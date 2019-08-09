<?php

namespace Drupal\nyt\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure NYT service settings for this site
 *
 * @internal
 */
class NytSettingsForm extends ConfigFormBase {
  /** @var string Config settings */
  const NYT_CONFIG = 'nyt.config';

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['nyt_service_api_key'];
  }

  /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'nyt_nyt_settings';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(self::NYT_CONFIG);
    $form['nyt_service_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('NYT API Key'),
      '#default_value' => $config->get('nyt_api_key'),
      '#description' => $this->t('Description.'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->configFactory->getEditable(static::NYT_CONFIG)
      ->set('nyt_api_key', $form_state->getValue('nyt_service_api_key'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
