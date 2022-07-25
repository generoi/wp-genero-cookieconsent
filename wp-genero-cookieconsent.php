<?php
/*
Plugin Name:        Genero CookieConsent
Plugin URI:         http://genero.fi
Description:        A Cookie Consent WordPress plugin
Version:            1.1.0
Author:             Genero
Author URI:         http://genero.fi/
License:            MIT License
License URI:        http://opensource.org/licenses/MIT
*/
use GeneroWP\CookieConsent\Plugin;

defined('ABSPATH') or die();

if (file_exists($composer = __DIR__ . '/vendor/autoload.php')) {
    require_once $composer;
}

Plugin::getInstance();
