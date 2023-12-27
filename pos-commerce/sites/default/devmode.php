<?php

/**
 * @file
 * Settings devmode.php.
 */

use Drupal\Component\Assertion\Handle;

if (TRUE) {
  Handle::register();
  assert_options(ASSERT_ACTIVE, TRUE);
  function_exists('apcu_clear_cache') ? apcu_clear_cache() : FALSE;
  // $settings["extension_discovery_scan_tests"] = TRUE; // show test modules.
  $settings["container_yamls"][] = __DIR__ . "/services-dev.yml";
  $config["system.logging"]["error_level"] = "verbose";
  $config["system.performance"]["css"]["preprocess"] = FALSE;
  $config["system.performance"]["js"]["preprocess"] = FALSE;
  $settings["cache"]["bins"]["render"] = "cache.backend.null";
  $settings["cache"]["bins"]["dynamic_page_cache"] = "cache.backend.null";
  $settings["cache"]["bins"]["page"] = "cache.backend.null";
  $settings["rebuild_access"] = TRUE;
  $settings["skip_permissions_hardening"] = TRUE;
}
else {
  $config["system.performance"]["css"]["preprocess"] = TRUE;
  $config["system.performance"]["js"]["preprocess"] = TRUE;
}

