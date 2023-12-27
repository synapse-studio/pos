<?php

namespace Drupal\adaptive\Hook;

use Drupal\paragraphs\Entity\Paragraph;

/**
 * @file
 * Contains \Drupal\service\Hook\TemplatePreprocessParagraphWorkScope.
 */

/**
 * Theme.
 */
class TemplatePreprocessParagraphWorkScope {

  /**
   * Hook.
   */
  public static function hook(&$variables) {
    $storage = \Drupal::entityTypeManager()->getStorage('paragraph');
    $work_scope_items = $variables['paragraph']->field_work_scope_items;
    $variables['work_scope_items'] = [];
    foreach ($work_scope_items as $item) {
      $work_scope_item = Paragraph::load($item->target_id);
      $variables['work_scope_items'][] = $work_scope_item;
    }
    template_preprocess_paragraph($variables);
  }

}
