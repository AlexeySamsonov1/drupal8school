<?php

namespace Drupal\nyt\Controller;

use DateTime;
use Drupal\Core\Controller\ControllerBase;
use Drupal\nyt\ArticleInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller routines for book routes.
 */
class NytController extends ControllerBase {

  private $nytArticlesService;

  /**
   * NytController constructor.
   * @param ArticleInterface $nytArticlesService
   */
  public function __construct(ArticleInterface $nytArticlesService) {
    $this->nytArticlesService = $nytArticlesService;
  }

  public static function create(ContainerInterface $container) {
    $nytArticlesService = $container->get('nyt.nyt_articles');

    return new static($nytArticlesService);
  }


  public function nytWeekArticles() {
    $start_date = (new DateTime('-3days'))->format('Ymd');
    $articles = $this->nytArticlesService->fetchArticles($start_date, '', TRUE);
    return [
      '#theme' => 'nyt_articles',
      '#articles' => $articles,
    ];
  }

  public function nytDayArticles() {
    $start_date = (new DateTime())->format('Ymd');
    $articles = $this->nytArticlesService->fetchArticles($start_date, '');
    return [
      '#theme' => 'nyt_articles',
      '#articles' => $articles,
    ];
  }

  public function nytForm() {

  }
}