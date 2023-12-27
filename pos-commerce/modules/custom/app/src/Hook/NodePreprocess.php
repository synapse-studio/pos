<?php

namespace Drupal\app\Hook;

/**
 * @file
 * Contains \Drupal\app\Hook\NodePreprocess.
 */

/**
 * Controller NodePreprocess.
 */
class NodePreprocess {

  /**
   * Hook.
   */
  public static function hook(&$variables) {
    $node = $variables['node'];
    if ($node->bundle() == 'syspage') {
      // DO something.
      // \Drupal::messenger()->addWarning("NodePreprocess");
    }
  }

}
