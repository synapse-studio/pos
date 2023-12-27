<?php

namespace Drupal\dashboard\PluginManager;

/**
 * Defines the common interface for all DeviceType classes.
 *
 * @see \Drupal\dashboard\PluginManager\StaticDashboardPluginManager
 * @see \Drupal\dashboard\PluginManager\StaticDashboardAnnotation
 * @see plugin_api
 */
interface StaticDashboardPluginInterface {

  /**
   * Init funtion.
   */
  public function report($options = []) : array;

}
