<?php

namespace Drupal\app\Hook;
/**
 * @file
 * Contains \Drupal\app\Hook\ProductPreprocess.
 */

/**
 * Controller ProductPreprocess.
 */
class ProductPreprocess {

  /**
   * Hook.
   */
  public static function hook(&$variables) {
    $entity = $variables['product_entity'];
    if ($entity->bundle() == 'default') {
      // DO something.
      \Drupal::messenger()->addWarning(__CLASS__ . "Preprocess");
      $variables['hello'] = 'world';
    }
  }

}
