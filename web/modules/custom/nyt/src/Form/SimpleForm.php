<?php

namespace Drupal\nyt\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class SimpleForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'nyt_simple_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['operations'] = [
      '#type' => 'number',
      '#title' => $this
        ->t('Quantity'),
    ];

    $form['example_select'] = [
      '#type' => 'select',
      '#title' => $this
        ->t('Select element'),
      '#options' => [
        '1' => $this
          ->t('One'),
        '2' => [
          '2.1' => $this
            ->t('Two point one'),
          '2.2' => $this
            ->t('Two point two'),
        ],
        '3' => $this
          ->t('Three'),
      ],
    ];

    $form['quantity'] = array(
      '#type' => 'range',
      '#title' => $this
        ->t('Quantity'),
    );

    $form['email'] = array(
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#pattern' => '*@example.com',
    );

    $form['fid'] = [
      '#title' => $this->t('Image'),
      '#type' => 'managed_file',
      '#upload_location' => 'public://test',
      '#upload_validators' => [
        'file_validate_extensions' => ['gif png jpg jpeg'],
        //'file_validate_size' => [$max_filesize],
        //'file_validate_image_resolution' => [$max_dimensions],
      ],
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * Form submission handler.
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $messenger = \Drupal::messenger();
    $messenger->addMessage('Quantity: ' . $form_state->getValue('quantity'));
    $messenger->addMessage('Fid: ' . $form_state->getValue('fid'));
  }

}