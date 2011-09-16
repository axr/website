<?php

/**
 * @file
 * Initiates a browser-based installation of Drupal.
 */

/**
 * Root directory of Drupal installation.
 */
define('DRUPAL_ROOT', getcwd() ."../www/");

  /**
   * Global flag to indicate that site is in installation mode.
   */
  define('MAINTENANCE_MODE', 'install');

  require_once DRUPAL_ROOT . '/includes/install.core.inc';

  $settings = array(
    'parameters' => array(
      'profile' => 'standard',
      'locale' => 'en',
    ),
    'forms' => array(
      'install_settings_form' => array(
        'driver' => 'mysql',
       // 'username' => '',
       // 'host' => 'myhost',
       // 'port' => '',
       // 'password' => 'mypass',
       // 'database' => 'mydbname',
      ),
      'install_configure_form' => array(
      //  'site_name' => 'my site',
      //  'site_mail' => 'me@me.com',
        'account' => array(
      //    'name' => 'admin',
      //    'mail' => 'me@me.com',
          'pass' => array(
     //       'pass1' => 'adminpass',
       //     'pass2' => 'adminpass',
          ),
        ),
        'update_status_module' => array(
          1 => TRUE,
          2 => TRUE,
        ),
        'clean_url' => TRUE,
      ),
    ),
  );

  install_drupal($settings);