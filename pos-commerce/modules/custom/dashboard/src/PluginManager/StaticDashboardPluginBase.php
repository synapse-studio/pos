<?php

namespace Drupal\dashboard\PluginManager;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides an DeviceType plugin manager.
 *
 * @see Drupal\dashboard\PluginManager\StaticDashboardAnnotation
 * @see Drupal\dashboard\PluginManager\StaticDashboardPluginInterface
 * @see plugin_api
 */
class StaticDashboardPluginBase extends PluginBase {
  // Use LinkGeneratorTrait;
  // use LoggerChannelTrait;
  // use RedirectDestinationTrait;
  // use UrlGeneratorTrait;.
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->board = FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function yandexDirectLinkMarkup($campaign_id, $campaign_name, $project) {
    return "<a href='https://direct.yandex.ru/registered/main.pl?cid=$campaign_id&ulogin=$project&cmd=showCamp'>$campaign_name</a>";
  }

}
