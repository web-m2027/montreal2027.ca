<?php

use Symfony\Component\Dotenv\Dotenv;

// @codingStandardsIgnoreLine
$env_file = DRUPAL_ROOT . '/../.env';
if (file_exists($env_file)) {
  $dotenv = new Dotenv();
  $dotenv->load($env_file);
}

// phpcs:ignoreFile

/**
 * Drupal settings that are common to all environments of the site.  This file gets
 * committed to the git repo.  Environment specific settings are added to settings.local.php
 * and IS NOT committed to the git repo.
 */

$databases = [];

$settings['update_free_access'] = FALSE;

$settings['container_yamls'][] = $app_root . '/' . $site_path . '/services.yml';

$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];

$settings['entity_update_batch_size'] = 50;
$settings['entity_update_backup'] = TRUE;
$settings['migrate_node_migrate_type_classic'] = FALSE;


$config['image.settings']['allow_insecure_derivatives'] = TRUE;
$config['image.settings']['suppress_itok_output'] = TRUE;

$settings['config_sync_directory'] = '../config/sync';

// Override the logo path for site theme.
// $config['theme_name.settings']['logo']['use_default'] = FALSE;
// $config['theme_name.settings']['logo']['path'] = '/assets/theme/logo.png.webp';

/**
 * Set the public file path to /assets/
 *
 * Set the alias in the site nginx config file to map /assets/theme to the theme root
 */

$settings['file_public_path'] = 'assets';

# If you're using a different local domain, you can add it to your settings.local.php file to redefine it.
$settings['trusted_host_patterns'] = [
  '^.+\\.montreal2027\\.ca',
  '^montreal2027\\.ca',
  '^montreal2027\\.local',
  '^localhost$',
  '^127\\.0\\.0\\.1$',
];

$databases['default']['default'] = [
  'database' => $_ENV['DB_NAME'],
  'username' => $_ENV['DB_USER'],
  'password' => $_ENV['DB_PASSWORD'],
  'prefix' => '',
  'host' => $_ENV['DB_HOST'],
  'port' => $_ENV['DB_PORT'],
  'isolation_level' => 'READ COMMITTED',
  'driver' => 'mysql',
  'namespace' => 'Drupal\\mysql\\Driver\\Database\\mysql',
  'autoload' => 'core/modules/mysql/src/Driver/Database/mysql/',
];

$settings['container_yamls'][] = $app_root . '/' . $site_path . '/development.services.yml';
$settings['hash_salt'] = $_ENV['HASH_SALT'];
$settings['cache']['bins']['render'] = 'cache.backend.null';
$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';

if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings.local.php';
}