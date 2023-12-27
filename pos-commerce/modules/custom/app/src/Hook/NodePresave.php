<?php

namespace Drupal\app\Hook;

/**
 * @file
 * Contains \Drupal\app\Hook\NodePresave.
 */

/**
 * Controller NodePresave.
 */
class NodePresave {

  /**
   * Hook.
   */
  public static function hook($node) {
    if ($node->bundle() == 'syspage') {
      // DO something.
      \Drupal::messenger()->addWarning(__FILE__);
    }
  }

}
