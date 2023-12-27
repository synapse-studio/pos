<?php

namespace Drupal\dashboard\PluginManager;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an dashboard template object.
 *
 * Plugin Namespace: Plugin\DashboardDinamic.
 *
 * @Annotation
 */
class DinamicDashboardAnnotation extends Plugin {

  /**
   * The archiver plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the archiver plugin.
   *
   * @var string
   */
  public $title;

  /**
   * Redmine project identifier.
   *
   * @var string
   */
  public $redmine;

  /**
   * Otdel.
   *
   * @var string
   */
  public $otdel;

  /**
   * Entyty type and bundle.
   *
   * @var string
   */
  public $entity;

  /**
   * Report Group.
   *
   * @var string
   */
  public $group;

  /**
   * Consumers.
   *
   * @var string
   */
  public $consumers;

}
