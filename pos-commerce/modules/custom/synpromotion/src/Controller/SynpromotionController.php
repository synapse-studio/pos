<?php

namespace Drupal\synpromotion\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\synpromotion\Form\SettingsForm;

/**
 * Returns responses for synpromotion routes.
 */
class SynpromotionController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {
    $form = [];
    $storage = \Drupal::entityTypeManager()->getStorage('commerce_promotion');
    $query = $storage->getQuery()
      ->sort('status', 'DESC')
      ->range(0, 20);
    $ids = $query->execute();
    if (!empty($ids)) {
      foreach ($ids as $id) {
        $formName = new SettingsForm($id);
        $form[] = \Drupal::formBuilder()->getForm($formName);
      }
    }
    if (empty($form)) {
      return [
        'text' => ['#markup' => "<p>Акций пока нет</p>"],
      ];
    }
    return $form;
  }

  /**
   * Builds the response.
   */
  public function test() {
    // $promotion = \Drupal::entityTypeManager()
    //   ->getStorage('commerce_promotion')
    //   ->create([
    //     'uid' => 1,
    //     'name' => 'X_Скидка на сопутствующий товар',
    //     'description' => 'Купи 1 товара, получи скидку на другой',
    //     'created' => time(),
    //     'offer' => [
    //       'target_plugin_id' => 'order_buy_x_get_y',
    //       'target_plugin_configuration' => [
    //         'buy_quantity' => '1',
    //         'buy_conditions' => [],
    //         'get_quantity' => '1',
    //         'get_conditions' => [],
    //         'get_auto_add' => 0,
    //         'offer_type' => 'fixed_amount',
    //         'offer_percentage' => NULL,
    //         'offer_amount' => [
    //           'number' => 50,
    //           'currency_code' => 'RUB',
    //         ],
    //         'offer_limit' => 0,
    //       ],
    //     ],
    //     'compatibility' => 'any',
    //     'status' => 0,
    //     'require_coupon' => 0,
    //     'order_types' => ['default'],
    //     'stores' => [1],
    //   ]);
    // $promotion->save();
    // dsm($promotion);

    // $promotion = \Drupal::entityTypeManager()->getStorage('commerce_promotion')->load(17);
    // $offer = $promotion->getOffer();
    // $configuration = $offer->getConfiguration();
    // dsm($configuration);
    // foreach ($configuration['buy_conditions'] as $key_conf => $conf) {
    //   if ($conf['plugin'] == 'order_item_product_category') {
    //     unset($configuration['buy_conditions'][$key_conf]);
    //   }
    // }
    // dsm($configuration);
    // $offer->setConfiguration($configuration);
    // dsm($offer);
    // $promotion->setOffer($offer)->save();
    return [
      'text' => ['#markup' => "<p>X</p>"],
      // 'table' => HelperTheme::renderTable(),
    ];
  }

}
