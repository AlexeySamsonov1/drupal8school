<?php

namespace Drupal\currency_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Block\BlockPluginInterface;

/**
 * Provides a 'CurrencyBlock' block.
 *
 * @Block(
 *  id = "currency_block",
 *  admin_label = @Translation("Currencies Block"),
 *  category = "Custom blocks"
 * )
 */
class CurrencyBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'currency_form_id';
  }
  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $default_config = \Drupal::config('currency_block.settings');
    return array(
      'currency_list' => $default_config->get('currency_list')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
  $a = print_r($this->getConfiguration());
    $build = [];
    $build['currency_block']['#markup'] = \Drupal::service('renderer')->render($this->description());
    $build['#cache'] = [
      'max_age' => 0,
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();kint($config);
    $default_configuration = $this->defaultConfiguration();

    $currency_xml = $this->get_xml_currency();
    $currency_arr = $this->convert_xml_to_array($currency_xml);
    $currency_list = $this->build_currency_list($currency_arr);

    $form['currency_list'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('Choose currencies to display'),
      '#options' => $currency_list['Name'],
      '#default_value' => isset($config['currency_list']) ? $config['currency_list'] : $default_configuration['currency_list']
    );

    //dsm($form);
    //debug($form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('currency_list', $form_state->getValue('currency_list'));
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    kint($form);
    // At least one element should be chosen.
    if(!array_filter($form_state->getValue('currency_list'))) {
      $form_state->setErrorByName('currency_list', t('At least one element should be chosen.'));
    }
  }

  public function get_xml_currency() {
    $url = 'http://www.nbrb.by/Services/XmlExRates.aspx';

    $client = \Drupal::httpClient();
    try {
      $request = $client->request('GET', $url, [
        'headers' => [
          'Accept',
          'application/xml, text/xml'
        ]
      ]);
      $response = $request->getBody();
    }
    catch (\Exception $e) {
      watchdog_exception('currency block', $e, $e->getMessage());
    }

    if ($request->getStatusCode() == 200) {
      return $response->getContents();
    }

  }

  public function convert_xml_to_array($xml) {
    $xml = simplexml_load_string($xml);
    $json = json_encode($xml);

    return json_decode($json, TRUE);
  }

  public function build_currency_list($currency_arr) {
    $currency_list = [];
    foreach ($currency_arr['Currency'] as $row) {
      $currency_list['Name'][$row['CharCode']] = $row['Name'];
      $currency_list['CharCode'][] = $row['CharCode'];
      $currency_list['Rate'][] = $row['Rate'];
    }

    return $currency_list;
  }

  public function description() {
    // We are going to output the results in a table with a nice header.

    $header = [
      [$this->t('Name')],
      [$this->t('CharCode')],
      [$this->t('Rate')],
    ];


    $config = $this->getConfiguration();

    $content['message'] = array(
      '#markup' => $this->t('Generate a list of all entries in the database. There is no filter in the query.'),
    );

    foreach ($config as $row) {
      // Normally we would add some nice formatting to our rows
      // but for our purpose we are simply going to add our row
      // to the array.
      $rows[] = array('data' => (array) $row);
    }
    $rows = array('Id', 'uid', 'Name');
    $content['table'] = array(
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => t('No entries available.'),
    );

    return $content;
  }
}