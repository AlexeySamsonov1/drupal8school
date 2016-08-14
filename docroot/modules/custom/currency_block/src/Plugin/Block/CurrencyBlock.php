<?php

namespace Drupal\currency_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Renderer;
use Drupal\currency_block\Services\CurrencyLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'CurrencyBlock' block.
 *
 * @Block(
 *  id = "currency_block",
 *  admin_label = @Translation("Currencies Block"),
 *  category = "Custom blocks"
 * )
 */
class CurrencyBlock extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface  {

  protected $renderer;

  protected $currencyLoader;

  /**
   * Constructs a new BookNavigationBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack object.
   * @param \Drupal\book\BookManagerInterface $book_manager
   *   The book manager.
   * @param \Drupal\Core\Entity\EntityStorageInterface $node_storage
   *   The node storage.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Renderer $renderer, CurrencyLoader $currencyList) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->renderer = $renderer;
    $this->currencyLoader = $currencyList;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('renderer'),
      $container->get('currency_block.currency.loader')
    );
  }


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
    $build = [];
    $markup = $this->getCurrencyBlockMarkup();
    $build['currency_block']['#markup'] = $this->renderer->render($markup);
    $build['#cache'] = [
      'max_age' => \Drupal\Core\Cache\Cache::PERMANENT,
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $options = [];
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();
    $default_configuration = $this->defaultConfiguration();
    $currency_list = $this->currencyLoader->buildCurrencyList();
    foreach ($currency_list as $item) {
      $options[$item['CharCode']] = $item['Name'];
    }
    $form['currency_list'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('Choose currencies to display'),
      '#options' => $options,
      '#default_value' => isset($config['currency_list']) ? $config['currency_list'] : $default_configuration['currency_list']
    );

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
    // At least one element should be chosen.
    if(!array_filter($form_state->getValue('currency_list'))) {
      $form_state->setErrorByName('currency_list', t('At least one element should be chosen.'));
    }
  }


  public function getCurrencyBlockMarkup() {
    // We are going to output the results in a table.
    $header = [
      $this->t('Scale'),
      $this->t('Name'),
      $this->t('CharCode'),
      $this->t('Rate'),
    ];

    $config = $this->getConfiguration();
    $checked_values = (array_filter($config['currency_list']));
    $content['message'] = array(
      '#markup' => $this->t('Generate a list of choosen currencies.'),
    );

    $currency_list = $this->currencyLoader->buildCurrencyList();
    foreach ($checked_values as $key) {
      if ($currency_list[$key]) {
        $rows[] = [
          $currency_list[$key]['Scale'],
          $currency_list[$key]['Name'],
          $currency_list[$key]['CharCode'],
          $currency_list[$key]['Rate'],
        ];
      }
    }

    $content['table'] = array(
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => t('No entries available.'),
    );

    return $content;
  }
}
