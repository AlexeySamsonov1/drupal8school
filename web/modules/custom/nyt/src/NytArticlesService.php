<?php

namespace Drupal\nyt;

use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\Client;
use function GuzzleHttp\Promise\all;
use Psr\Http\Message\ResponseInterface;

/**
 * Class NytArticlesService.
 */
class NytArticlesService implements ArticleInterface {

  const NYT_API_URL = 'http://api.nytimes.com/svc/search/v2/articlesearch.json';

  /**
   * The system config object.
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var
   */
  private $httpClient;

  /**
   * Constructs a new NytArticlesService object.
   * @param \GuzzleHttp\Client $http_client
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  public function __construct(Client $http_client, ConfigFactoryInterface $config_factory) {
    $this->httpClient = $http_client;
    $this->configFactory = $config_factory;
  }

  /**
   * @return string
   */
  public function fetchArticles($start_date = '', $end_date = '', $multiple = FALSE) {
    $articles = [];
    $options = [
      'begin_date' => $start_date,
    ];
    $response = $this->makeHttpRequest($options);
    if (!empty($response)) {
      list($articles, $hits) = $this->parseResponse($response['response']);
    }

    if ($multiple && $hits > 10) {
      $data = $this->makeHttpRequestMultiple($options);
      if (!empty($data)) {
        foreach ($data as $response) {
          $articles = array_merge($articles, $this->parseResponse($response['response'])[0]);
        }
      }
    }

    return $articles;
  }

  /**
   * @param array $data
   */
  private function parseResponse(array $data) {
    foreach ($data['docs'] as $item) {
      $articles[] = [
        'headline' => $item['headline']['main'],
        'pub_date' => $item['pub_date'],
      ];
    }

    return [$articles, $data['meta']['hits']];
  }

  /**
   * @param array $query_args
   * @return mixed|null
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  protected function makeHttpRequest(array $query_args = []) {
    $nytApiKey = $this->configFactory->getEditable('nyt.config')
      ->get('nyt_api_key');
    $query_args += ['api-key' => $nytApiKey];
    $query = http_build_query($query_args);
    $url = self::NYT_API_URL . '?' . $query;

    $options_default = [
      'http_errors' => FALSE,
      'connect_timeout' => 10,
      'read_timeout' => 10,
      'timeout' => 10,
      'verify' => FALSE,
    ];
    $options = !empty($options) ? $options : $options_default;

    try {
      $response = $this->httpClient->request('GET', $url, $options);
      $code = $response->getStatusCode();
      if ($code == 200) {
        $body = $response->getBody()->getContents();

        return json_decode($body, TRUE);
      }
    } catch (\Exception $e) {
      watchdog_exception(__CLASS__, $e);
    }

    return NULL;
  }

  /**
   * @param array $query_args
   * @return mixed|null
   */
  protected function makeHttpRequestMultiple(array $query_args = []) {
    $responses = [];
    $nytApiKey = $this->configFactory->getEditable('nyt.config')
      ->get('nyt_api_key');
    $query_args += ['api-key' => $nytApiKey];
    $query = http_build_query($query_args);
    $url = self::NYT_API_URL . '?' . $query;

    $options_default = [
      'http_errors' => FALSE,
      'connect_timeout' => 10,
      'read_timeout' => 10,
      'timeout' => 10,
      'verify' => FALSE,
    ];
    $options = !empty($options) ? $options : $options_default;

    try {
      foreach ([2, 3, 4, 5] as $page) {
        $promises[] = $this->httpClient->requestAsync('GET', $url . '&page=' . $page, $options);
      }

      return all($promises)->then(function (array $responses) {
        $results = [];
        foreach ($responses as $response) {
          if ($response->getStatusCode() == 200) {
            $results[] = json_decode($response->getBody()->getContents(), TRUE);
          }
        }

        return $results;
      })->wait();
    } catch (\Exception $e) {
      watchdog_exception(__CLASS__, $e);
    }

    return $responses;
  }

}
