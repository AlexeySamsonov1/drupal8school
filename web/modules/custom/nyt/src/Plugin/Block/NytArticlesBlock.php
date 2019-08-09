<?php

namespace Drupal\nyt\Plugin\Block;

use DateTime;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Hello' Block.
 *
 * @Block(
 *   id = "nyt_articles_block",
 *   admin_label = @Translation("NYT Articles block"),
 *   category = @Translation("NYT Articles"),
 * )
 */
class NytArticlesBlock extends BlockBase {

  const NYT_ARTICLES_DEFAULT_COUNT = 10;

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => $this->t('Hello, World!'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $today = new DateTime();
    $yesterday = new DateTime('-1day');

    $form['start_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Start date'),
      '#default_value' => $config['start_date'] ?? $yesterday->format('Y-m-d'),
      '#date_format' => 'd/m/Y',
      '#required' => TRUE,
    ];

    $form['end_date'] = [
      '#type' => 'date',
      '#title' => $this->t('End date'),
      '#default_value' => $config['end_date'] ?? $today->format('Y-m-d'),
      '#date_date_format' => 'm/d/Y',
      '#required' => TRUE,
    ];

    $form['articles_count'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Articles count'),
      '#default_value' => self::NYT_ARTICLES_DEFAULT_COUNT,
      '#size' => 10,
      '#maxlength' => 10,
      '#pattern' => '[0-9]+',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['start_date'] = $values['start_date'];
    $this->configuration['end_date'] = $values['end_date'];
  }

}