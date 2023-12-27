<?php

/**
 * @file
 * Drupal site-specific configuration file.
 */

// Location of the site configuration files.
$config_directories = [];
// Salt for one-time login links, cancel links, form tokens, etc.
$settings['hash_salt'] = 'Ssi2cKNBILzQuLngoCkrS6WqMtoO3X_eTA2-mq16azMA1A884SmDKr-nBCixLEn6uthntzCbYw';
// Access control for update.php script.
$settings['update_free_access'] = FALSE;
// Private FS.
$settings['file_private_path'] = 'sites/default/private';

// Load services definition file.
$settings['container_yamls'][] = __DIR__ . '/services-prod.yml';

// The default list of directories that will be ignored by Drupal's file API.
$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];

// Trusted host configuration.
/*$settings['trusted_host_patterns'] = [
  '^example\.com$',
  '^.+\.example\.com$',
];*/

// Set files temporary after entity delete (drupal.org/node/2891902).
$config["file.settings"]["make_unused_managed_files_temporary"] = TRUE;

// Set database cache bins size more then default (5000)
$settings['database_cache_max_rows']['bins']['entity'] = 500000;
$settings['database_cache_max_rows']['bins']['render'] = 500000;
$settings['database_cache_max_rows']['bins']['page'] = 500000;
$settings['database_cache_max_rows']['bins']['dynamic_page_cache'] = 500000;

// Load local development override configuration, if available.
include __DIR__ . '/devmode.php';
// Database settings.
$databases = [];

$settings['config_sync_directory'] = 'sites/config/config_ctcZSlMNqh18xsj0/sync';
$databases['default']['default'] = array (
  'database' => 'drupal',
  'username' => 'drupal',
  'password' => 'zwh4LMd2bRVDjg',
  'prefix' => '',
  'host' => 'localhost',
  'port' => '',
  'namespace' => 'Drupal\\mysql\\Driver\\Database\\mysql',
  'driver' => 'mysql',
  'autoload' => 'core/modules/mysql/src/Driver/Database/mysql/',
);

$databases["default"]["default"]["password"] = "zwh4LMd2bRVDjg";


$databases["default"]["default"]["password"] = "zwh4LMd2bRVDjg";

