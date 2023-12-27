<?php

namespace Drupal\adaptive\Hook;

use Drupal\block\Entity\Block;

/**
 * @file
 * Contains \Drupal\service\Hook\TemplatePreprocessParagraphBlock.
 */

/**
 * Theme.
 */
class TemplatePreprocessParagraphBlock {

  /**
   * Hook.
   */
  public static function hook(&$variables) {
    $bid = $variables['elements']['#paragraph']->field_block_id->value;
    if ($bid) {
      $block = Block::load($bid);
      if ($block) {
        $variables['block'] = \Drupal::entityTypeManager()->getViewBuilder('block')->view($block);
      }
    }
  }

}
