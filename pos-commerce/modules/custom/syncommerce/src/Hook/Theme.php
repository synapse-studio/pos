<?php

namespace Drupal\syncommerce\Hook;

/**
 * Hooks.
 */
class Theme {

  /**
   * Implements hook_theme().
   */
  public static function hook() {
    return [
      'syncommerce_product' => [
        'template' => 'syncommerce--products-variations',
        'variables' => ['data' => []],
      ],
    ];
  }

}
