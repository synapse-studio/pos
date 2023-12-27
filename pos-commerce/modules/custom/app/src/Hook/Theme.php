<?php

namespace Drupal\app\Hook;

/**
 * @file
 * Contains \Drupal\app\Hook\Theme.
 */

/**
 * Controller Theme.
 */
class Theme {

  /**
   * Hook.
   */
  public static function hook() {
    return [
      'app' => [
        'variables' => [
          'data' => [],
        ],
      ],
    ];
  }

}
