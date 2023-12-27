<?php

namespace Drupal\synpromotion\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

/**
 * Configure synpromotion settings for this site.
 */
class SettingsForm extends FormBase {

  const OFFER = [
    'order_item_percentage_off',
    'order_buy_x_get_y',
    'shipment_percentage_off',
  ];

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'synpromotion_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['synpromotion.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $storage = \Drupal::entityTypeManager()->getStorage('commerce_promotion');
    $query = $storage->getQuery()
      // ->condition('status', 1)
      // ->condition('type', 'product')
      ->sort('status', 'DESC')
      ->range(0, 20);
    $ids = $query->execute();
    if (!empty($ids)) {
      foreach ($storage->loadMultiple($ids) as $id => $promotion) {
        dsm([[$promotion->getOffer()]]);
        $title = $promotion->status->getString() ? '(Включено)' : '(Выключено)';
        $form["detail_promotion_{$promotion->id()}"] = [
          '#type' => 'details',
          '#title' => "{$promotion->getName()} {$title}. ID {$promotion->id()}",
          // '#open' => FALSE,
          // 'status' => [
          //
          // ],
          "status_{$promotion->id()}" => [
            '#type' => 'checkbox',
            '#title' => $this->t('Status'),
            '#default_value' => $promotion->status->getString(),
          ],
          "startDate_{$promotion->id()}" => [
            '#type' => 'textfield',
            '#title' => $this->t('Start date'),
            '#default_value' => $promotion->start_date->getString(),
          ],
          "endDate_{$promotion->id()}" => [
            '#type' => 'textfield',
            '#title' => $this->t('End date'),
            '#default_value' => $promotion->end_date->getString(),
          ],
        ];
        if ($promotion->status->getString()) {
          $form["detail_promotion_{$promotion->id()}"]['#open'] = TRUE;
        }
        switch ($promotion->getOffer()->getPluginId()) {
          case 'order_item_percentage_off':
            dsm($promotion->getOffer()->getConfiguration());
            $form["detail_promotion_{$promotion->id()}"]["percentage"] = [
              '#type' => 'textfield',
              '#title' => $this->t('Percentage'),
              '#default_value' => $promotion->getOffer()->getConfiguration()['percentage'] * 100,
            ];
            break;
        }
      }
      $form['actions'] = [
        '#type' => 'actions',
        'ajax_submit' => [
          '#type' => 'button',
          '#value' => $this->t('Save'),
          '#attributes' => ['class' => ['inline', 'btn-success']],
          '#ajax'   => [
            'callback' => '::saveSettings',
            'progress' => ['type' => 'throbber', 'message' => NULL],
          ],
        ],
        '#suffix' => '<div id="saveResult"></div>',
      ];
    }
    // dsm($entities);
    return $form;
  }

  /**
   * Implements saveSettings.
   */
  public static function saveSettings(array &$form, FormStateInterface $form_state) {
    dsm($form_state);
    dsm($form_state->getValues());
    $response = new AjaxResponse();
    // $office = $form_state->getValues()['shipping_information']['shipping_profile']['select_office'];
    // $response->addCommand(
    //   new InvokeCommand("[data-drupal-selector='edit-shipping-information-shipping-profile-field-customer-address-0-value']", 'val', [$office]));
    // $form_state->setRebuild(TRUE);
    $response->addCommand(new HtmlCommand("#saveResult", t('Configuration saved')));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
