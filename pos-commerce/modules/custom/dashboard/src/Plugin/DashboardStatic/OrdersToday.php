<?php

namespace Drupal\dashboard\Plugin\DashboardStatic;

use Drupal\dashboard\PluginManager\StaticDashboardPluginBase;
use Drupal\dashboard\PluginManager\StaticDashboardPluginInterface;

/**
 * Static Report.
 *
 * @StaticDashboardAnnotation(
 *   id = "OrdersToday",
 *   title = "Заказы за сегодня",
 *   group = "Заказы",
 *   consumers = {""},
 * )
 */
class OrdersToday extends StaticDashboardPluginBase implements StaticDashboardPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function report($options = []): array {
    $result = [];
    $rows = [];
    $orders = $this->getOrders();
    dsm($orders);
    $result = [
      '#type' => 'table',
      '#prefix' => '<h2>Сайты с установленным Syncloud</h2>',
      '#header' => [
        '#',
        'Название',
        'UUID',
        'Ссылка',
        'Drupal',
        'PHP',
        'Syncloud',
      ],
      '#rows' => $rows,
    ];
    return $result;
  }

  private function getOrders() {
    $entities = [];
    $storage = \Drupal::entityTypeManager()->getStorage('commerce_order');
    $ids_calls = \Drupal::entityQuery('commerce_order')
      ->condition('completed', strtotime('today'), '>=')
      ->condition('completed', strtotime('today +1 day') - 1, '<=')
      ->sort('completed', 'ASC')
      ->execute();
    if (!empty($ids_calls)) {
      foreach ($storage->loadMultiple($ids_calls) as $id => $entity) {
        $entities[$id] = $entity;
      }
    }
    return $entities;
  }

}
