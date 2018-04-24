<?php
/*
Plugin Name:        Genero CookieConsent
Plugin URI:         http://genero.fi
Description:        A Cookie Consent WordPress plugin
Version:            1.0.0-alpha
Author:             Genero
Author URI:         http://genero.fi/
License:            MIT License
License URI:        http://opensource.org/licenses/MIT
*/
namespace GeneroWP\CookieConsent;

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

    public $version = '1.0.0-alpha';
    public $cookieconsent_version = '3.0.3';
    public $plugin_name = 'wp-genero-cookieconsent';
    public $plugin_path;
    public $plugin_url;
    public $github_url = 'https://github.com/generoi/wp-genero-cookieconsent';

    public $debug = true;
    protected $options = null;

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
        // Google Analytics for Wordpress.
        add_filter('monsterinsights_track_user', [$this, 'ga_track_user']);
        // Admin pages
        add_action('acf/init', [$this, 'admin_page']);
    }

    /**
     * Create an Admin page.
     */
    public function admin_page()
    {
        $option_page = acf_add_options_page([
            'page_title' => __('CookieConsent', 'wp-genero-cookieconsent'),
            'parent_slug' => 'options-general.php',
            'post_id' => 'cookieconsent_options',
        ]);

        include_once __DIR__ . '/acf/acf-export.php';
    }

    /**
     * Return one or all options belonging to the plugin.
     *
     * @param  string  $key (optional)
     * @return mixed
     */
    public function options($key = null)
    {
        if (!$this->options) {
            $options = [
                'cookieconsent__enabled' => true,
                'cookieconsent__location' => true,
                'cookieconsent__regional_law' => true,
                'cookieconsent__type' => 'info',
                'cookieconsent__position' => 'bottom',
                'cookieconsent__theme' => 'no-edge',
                'cookieconsent__layout' => 'foundation-reveal',
                'cookieconsent__content_message' => __('This website uses cookies to ensure you get the best experience on our website.', 'wp-genero-cookieconsent'),
                'cookieconsent__content_dismiss' => __('Got it!', 'wp-genero-cookieconsent'),
                'cookieconsent__content_allow' => __('Allow cookies', 'wp-genero-cookieconsent'),
                'cookieconsent__content_deny' => __('Decline', 'wp-genero-cookieconsent'),
                'cookieconsent__content_link' => __('Learn more', 'wp-genero-cookieconsent'),
                'cookieconsent__content_href' => $this->default_privacy_policy_url(),
            ];
            if (function_exists('get_fields') && ($acf = get_fields('cookieconsent_options'))) {
                foreach ($acf as $key => $value) {
                    // Empty values use defaults.
                    if (is_string($value) && !empty($value)) {
                        $options[$key] = $value;
                    } elseif (!is_string($value)) {
                        $options[$key] = $value;
                    }
                }
            }

            foreach ($options as $key => $value) {
                $key = str_replace('cookieconsent__', '', $key);
                $this->options[$key] = $value;
            }
        }

        return isset($key) ? $this->options[$key] : $this->options;
    }

    /**
     * Register all assets.
     */
    public function register_assets()
    {
        wp_register_script('wp-genero-cookieconsent/js/cdn', "https://cdnjs.cloudflare.com/ajax/libs/cookieconsent2/{$this->cookieconsent_version}/cookieconsent.min.js", [], null, true);
        wp_register_style('wp-genero-cookieconsent/css/cdn', "https://cdnjs.cloudflare.com/ajax/libs/cookieconsent2/{$this->cookieconsent_version}/cookieconsent.min.css", [], null);

        wp_register_script('wp-genero-cookieconsent/js', $this->plugin_url . 'dist/main.js', ['jquery'], $this->version, true);
        wp_register_style('wp-genero-cookieconsent/css', $this->plugin_url . 'dist/main.css', [], $this->version);
    }

    /**
     * Enqueue all assets.
     */
    public function enqueue_assets()
    {
        wp_localize_script('wp-genero-cookieconsent/js/cdn', '_genero_cookieconsent', apply_filters('wp-genero-cookieconsent/options', [
            'ga' => apply_filters('wp-genero-cookieconsent/ga', function_exists('monsterinsights_get_ua') ? monsterinsights_get_ua() : ''),
            'gtm' => apply_filters('wp-genero-cookieconsent/gtm', get_option('google_tag_manager_id')),
            'options' => [
                'enabled' => $this->options('enabled'),
                'type' => $this->options('type'),
                'content' => [
                    'message' => $this->options('content_message'),
                    'dismiss' => $this->options('content_dismiss'),
                    'allow' => $this->options('content_allow'),
                    'deny' => $this->options('content_deny'),
                    'link' => $this->options('content_link'),
                    'href' => $this->options('content_href'),
                    'close' => '&#x274c;',
                ],
                'compliance' => [
                    'info' => '<div class="cc-compliance">{{dismiss}}</div>',
                    'opt-in' => '<div class="cc-compliance">{{dismiss}}{{allow}}</div>',
                    'opt-out' =>'<div class="cc-compliance">{{deny}}{{dismiss}}</div>',
                ],
                'elements' => [
                    'dismiss' => '<a aria-label="dismiss cookie message" tabindex="0" class="cc-btn cc-dismiss button">{{dismiss}}</a>',
                    'allow' => '<a aria-label="allow cookies" tabindex="0" class="cc-btn cc-allow button">{{allow}}</a>',
                    'deny' => '<a aria-label="deny cookies" tabindex="0" class="cc-btn cc-deny button">{{deny}}</a>',
                ],
                'dismissOnScroll' => 1000,
                'position' => $this->options('position'),
                'theme' => $this->options('theme'),
                'location' => $this->debug ? false : $this->options('location'),
                'regionalLaw' => $this->debug ? false : $this->options('regional_law'),
                'layout' => $this->options('layout'),
                'layouts' => [
                    'basic' => '<div class="cc-container basic">{{messagelink}}{{compliance}}</div>',
                    'foundation-reveal' => '<div class="cc-container reveal">{{messagelink}}{{compliance}}</div>',
                    'foundation-callout' => '<div class="cc-container callout">{{messagelink}}{{compliance}}</div>',
                ],
            ],
        ]));

        wp_enqueue_script('wp-genero-cookieconsent/js/cdn');
        wp_enqueue_script('wp-genero-cookieconsent/js');
        wp_enqueue_style('wp-genero-cookieconsent/css/cdn');
        wp_enqueue_style('wp-genero-cookieconsent/css');
    }

    /**
     * Return a default privacy policy url.
     *
     * @return string
     */
    public function default_privacy_policy_url()
    {
        if (function_exists('get_privacy_policy_url')) {
            return get_privacy_policy_url();
        }
        return 'https://cookiesandyou.com';
    }

    /**
     * Google Analytics for Wordpress filter;
     *
     * Override not to track users when cookie consent has been denied or
     * when using opt-in, not yet allowed.
     *
     * @note this requires a wp-super-cache plugin as it breaks caching.
     *
     * @param  string  $value
     * @return string
     */
    public function ga_track_user($value)
    {
        $isOptin = Plugin::get_instance()->options('type') === 'opt-in';
        $cookie = $_COOKIE['cookieconsent_status'] ?? null;
        // Optin but not yet dismissed.
        if ($isOptin && $cookie !== 'allow') {
            $value = false;
        }
        // Explicity denied
        if ($cookie === 'deny') {
            $value = false;
        }
        return $value;
    }

    /**
     * Ensure all required plugins are available before activating.
     *
     * @todo add support without ACF.
     */
    public static function activate()
    {
        foreach ([
            'advanced-custom-fields-pro/acf.php' => 'Advanced Custom Fields PRO',
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
}

Plugin::get_instance();
