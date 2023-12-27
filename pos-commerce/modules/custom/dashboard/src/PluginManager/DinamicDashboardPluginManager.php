<?php

namespace Drupal\dashboard\PluginManager;

use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides an Archiver plugin manager.
 *
 * @see \Drupal\dashboard\PluginManager\DinamicDashboardPluginInterface
 * @see \Drupal\dashboard\PluginManager\DinamicDashboardAnnotation
 * @see plugin_api
 */
class DinamicDashboardPluginManager extends DefaultPluginManager {

  /**
   * Constructs a ArchiverManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(
    \Traversable $namespaces,
    CacheBackendInterface $cache_backend,
    ModuleHandlerInterface $module_handler
  ) {
    parent::__construct(
      'Plugin/DashboardDinamic',
      $namespaces,
      $module_handler,
      'Drupal\dashboard\PluginManager\DinamicDashboardPluginInterface',
      'Drupal\dashboard\PluginManager\DinamicDashboardAnnotation'
    );
    $this->alterInfo('dashboard_dinamic');
    $this->setCacheBackend($cache_backend, 'dashboard_dinamic_plugin');
    $this->factory = new DefaultFactory($this->getDiscovery());
  }

}
