<?php

namespace Drupal\currency_block\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class CurrencyBlockController.
 *
 * @package Drupal\currency_block\Controller
 */
class CurrencyBlockController extends ControllerBase {

  /**
   * Index.
   *
   * @return string
   *   Return Hello string.
   */
  public function index() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: index with parameter(s): $name'),
    ];
  }

}
