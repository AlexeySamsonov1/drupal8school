<?php

namespace Drupal\nyt;

/**
 * Interface ArticleInterface.
 */
interface ArticleInterface {
  public function fetchArticles($start_date, $end_date);

}
