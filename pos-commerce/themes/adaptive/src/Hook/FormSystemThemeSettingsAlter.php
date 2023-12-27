<?php

namespace Drupal\adaptive\Hook;

/**
 * @file
 * Contains \Drupal\adaptive\Hook.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Controller routines for page example routes.
 */
class FormSystemThemeSettingsAlter {

  /**
   * Hook.
   */
  public static function hook(&$form, FormStateInterface $form_state) {
    $form['header_logo_inverse'] = [
      '#type' => 'checkbox',
      '#title' => t('Header logo inverce'),
      '#default_value' => theme_get_setting('header_logo_inverse'),
    ];
    $form['footer_logo_inverse'] = [
      '#type' => 'checkbox',
      '#title' => t('Footer logo inverce'),
      '#default_value' => theme_get_setting('footer_logo_inverse'),
    ];
  }

}
