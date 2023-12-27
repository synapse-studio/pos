<?php

namespace Drupal\dashboard\PluginManager;

/**
 * Defines the common interface for all DeviceType classes.
 *
 * @see \Drupal\dashboard\PluginManager\StaticDashboardPluginManager
 * @see \Drupal\dashboard\PluginManager\StaticDashboardAnnotation
 * @see plugin_api
 */
interface DinamicDashboardPluginInterface {

  /**
   * Construct.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition);

  /**
   * Init funtion.
   */
  public function report(object $options) : array;

}
