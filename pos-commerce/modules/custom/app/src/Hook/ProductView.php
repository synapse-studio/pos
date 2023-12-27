<?php

namespace Drupal\app\Hook;

/**
 * @file
 * Contains \Drupal\app\Hook\ProductView.
 */

/**
 * Controller ProductView.
 */
class ProductView {

  /**
   * Hook.
   */
  public static function hook(&$build, $entity, $view_mode) {
    if ($entity->bundle() == 'default') {
      if ($view_mode == 'full') {
        \Drupal::messenger()->addWarning(__CLASS__ . "view-full");
      }
    }
  }

}
