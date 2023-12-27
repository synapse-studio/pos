<?php

namespace Drupal\dashboard\Plugin\DashboardDinamic;

use Drupal\dashboard\PluginManager\DinamicDashboardPluginBase;
use Drupal\dashboard\PluginManager\DinamicDashboardPluginInterface;

/**
 * Report: OrdersDynamic this.
 *
 * @DinamicDashboardAnnotation(
 *   id = "OrdersDynamic",
 *   title = "Заказы за период",
 *   group = "Заказы",
 *   consumers = {""},
 * )
 */
class OrdersDynamic extends DinamicDashboardPluginBase implements DinamicDashboardPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function report($options = []): array {
    $result = [];
    $rows = [];
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

}
