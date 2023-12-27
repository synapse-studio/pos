<?php

namespace Drupal\app\Utility;

/**
 * @file
 * Contains \Drupal\app\Utility\HelperTheme.
 */

/**
 * Utility Class.
 */
class HelperTheme {

  /**
   * Theme render Example.
   */
  public static function renderTable($node = FALSE, $type = 'small') {
    $src = [1, 2, 3, 4, 5];
    $headers = [
      ['data' => '#', 'class' => ['text-center']],
      ['data' => ['#markup' => 'One'], 'class' => ['text-left']],
      ['data' => 'Two', 'class' => ['text-right']],
      ['data' => 'Three', 'class' => ['text-right']],
      ['data' => 'Four', 'class' => ['text-right']],
    ];
    $rows = [];
    foreach ($src as $k => $val) {
      $rows[] = [
        ['data' => ++$k, 'class' => ['text-center']],
        ['data' => "one" , 'class' => ['text-left']],
        ['data' => "two" , 'class' => ['text-right']],
        ['data' => "three" , 'class' => ['text-right']],
        ['data' => "four" , 'class' => ['text-right']],
      ];
    }
    return [
      '#type' => 'table',
      '#header' => $headers,
      '#rows'   => $rows,
      '#attributes' => ['class' => ['table', 'table-striped', 'table-hover']],
      '#allowed_tags' => ['p', 'h2', 'small', 'br'],
    ];
  }

}
