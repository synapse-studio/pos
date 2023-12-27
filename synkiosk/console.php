#!/usr/bin/env php
<?php

if (!is_dir(__DIR__ . "/vendor")) {
  shell_exec("composer install --no-dev  -o -d " . __DIR__);
}

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use App\Command\Clear;
use App\Command\DockerInstall;
use App\Command\BackupRestore;
use Symfony\Component\Dotenv\Dotenv;

// Setup .env vars.
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');
$_ENV['ASSETS'] = __DIR__ . "/assets";

// Symfony app.
$app = new Application('Console Kiosk', 'v1.0');
$app->add(new Clear());
$app->add(new DockerInstall());
$app->add(new BackupRestore());

// Run.
$app->run();
