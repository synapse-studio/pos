<?php

namespace Drupal\adaptive\Hook;

/**
 * @file
 * Contains \Drupal\synlanding\Hook.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Class PageAttachmentsAlter.
 */
class FormAlter {

  const FORM_TYPES = ['contact_message_form', 'commerce_checkout_form'];

  /**
   * Hook.
   */
  public static function hook(&$form, FormStateInterface $form_state, $form_id) {
    $formTheme = is_array($form['#theme']) ? $form['#theme'] : [$form['#theme']];
    if (!empty(array_intersect($formTheme, self::FORM_TYPES))) {
      $config = \Drupal::config('synlanding.settings');
      $form['#attributes']['class'][] = 'form';
    }
  }

}
