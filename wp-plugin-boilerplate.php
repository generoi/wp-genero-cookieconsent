<?php
/*
Plugin Name:        Plugin Boilerplate
Plugin URI:         http://genero.fi
Description:        A boilerplate WordPress plugin
Version:            1.0.0
Author:             Genero
Author URI:         http://genero.fi/
License:            MIT License
License URI:        http://opensource.org/licenses/MIT
*/
namespace GeneroWP\PluginBoilerplate;

use Puc_v4_Factory;

if (!defined('ABSPATH')) {
    exit;
}

if (file_exists($composer = __DIR__ . '/vendor/autoload.php')) {
    require_once $composer;
}

class Plugin
{
    use Singleton;

    public $version = '1.0.0';
    public $plugin_name = 'wp-plugin-boilerplate';
    public $plugin_path;
    public $plugin_url;
    public $github_url = 'https://github.com/generoi/wp-plugin-boilerplate';

    public function __construct()
    {
        $this->plugin_path = plugin_dir_path(__FILE__);
        $this->plugin_url = plugin_dir_url(__FILE__);

        register_activation_hook(__FILE__, [__CLASS__, 'activate']);
        register_deactivation_hook(__FILE__, [__CLASS__, 'deactivate']);

        Puc_v4_Factory::buildUpdateChecker($this->github_url, __FILE__, $this->plugin_name);

        add_action('plugins_loaded', [$this, 'init']);
    }

    public function init()
    {
        add_action('wp_enqueue_scripts', [$this, 'register_assets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function register_assets()
    {
        wp_register_script('wp-plugin-boilerplate/js', $this->plugin_url . 'dist/main.js', ['jquery'], $this->version, true);
        wp_register_style('wp-plugin-boilerplate/css', $this->plugin_url . 'dist/main.css', [], $this->version);
    }

    public function enqueue_assets()
    {
        wp_enqueue_script('wp-plugin-boilerplate/js');
        wp_enqueue_style('wp-plugin-boilerplate/css');
    }

    public static function activate()
    {
        foreach ([
            'advanced-custom-fields-pro/acf.php' => 'Advanced Custom Fields PRO',
            'timber-library/timber.php' => 'Timber Library',
            // 'wp-timber-extended/wp-timber-extended.php' => 'WP Timber Extended',
        ] as $plugin => $name) {
            if (!is_plugin_active($plugin) && current_user_can('activate_plugins')) {
                wp_die(sprintf(
                    __('Sorry, but this plugin requires the %s plugin to be installed and active. <br><a href="%s">&laquo; Return to Plugins</a>', 'wp-hero'),
                    $name,
                    admin_url('plugins.php')
                ));
            }
        }
    }

    public static function deactivate()
    {
    }
}

Plugin::get_instance();
