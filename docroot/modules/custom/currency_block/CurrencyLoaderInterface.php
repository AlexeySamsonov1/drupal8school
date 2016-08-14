<?php

namespace Drupal\currency_block;

/**
 * Defines a common interface getting currencies list.
 */
interface CurrencyLoaderInterface {
  //
  public function getCurrencies();
    
  //  
  public function buildCurrencyList();
}
