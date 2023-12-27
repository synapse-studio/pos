<?php

namespace Drupal\dashboard\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Configure rabbitmq settings for this site.
 */
class CustomValue extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dashboard_custom_value';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['custom_value'] = [
      '#title' => $this->t('Custom value'),
      '#default_value' => '',
      '#type' => 'textfield',
      '#description' => '',
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $previousUrl = \Drupal::request()->server->get('HTTP_REFERER');
    if ($pos = strpos($previousUrl, '?')) {
      $previousUrl = substr($previousUrl, 0, $pos);
    }
    $response = new RedirectResponse($previousUrl . "?custom_value={$form_state->getValue('custom_value')}");
    $response->send();
  }

}
