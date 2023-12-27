<?php

namespace Drupal\app\Hook;

/**
 * @file
 * Contains \Drupal\app\Hook\NodeView.
 */

use Drupal\app\Utility\HelperTheme;

/**
 * Hook Node View.
 */
class NodeView {

  /**
   * Hook.
   */
  public static function hook(&$build, $node, $view_mode) {
    if ($node->bundle() == 'example') {
      if ($view_mode == 'full') {
        $build['app'] = [
          'from' => \Drupal::formBuilder()->getForm('Drupal\app\Form\Set', $node),
          'products' => HelperTheme::renderTable($node),
        ];
      }
      if ($view_mode == 'teaser') {
        $build['app'] = [
          'products' => HelperTheme::renderTable($node, 'small'),
        ];
      }
    }
  }

}
