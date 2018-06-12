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
namespace GeneroWP\CookieConsent;

use Puc_v4_Factory;
use GeneroWP\Common\Singleton;
use GeneroWP\Common\Assets;

if (!defined('ABSPATH')) {
    exit;
}

if (file_exists($composer = __DIR__ . '/vendor/autoload.php')) {
    require_once $composer;
}

class Plugin
{
    use Singleton;
    use Assets;

    public $version = '1.1.0';
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

        Puc_v4_Factory::buildUpdateChecker($this->github_url, __FILE__, $this->plugin_name);

        add_action('plugins_loaded', [$this, 'init']);
    }

    public function init()
    {
        add_action('wp_enqueue_scripts', [$this, 'register_assets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        // Admin pages
        add_action('acf/init', [$this, 'admin_page']);
        add_action('init', [$this, 'load_textdomain']);
    }

    /**
     * Create an Admin page.
     */
    public function admin_page()
    {
        $option_page = acf_add_options_page([
            'page_title' => 'CookieConsent',
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
                'cookieconsent__type' => 'info',
                'cookieconsent__position' => 'bottom',
                'cookieconsent__theme' => 'no-edge',
                'cookieconsent__layout' => 'basic',
                'cookieconsent__content_message' => __('We respect and value your privacy. Cookies are used on our website to ensure you the best possible user experience. By continuing to browse the site you agree to our use of cookies.', 'wp-genero-cookieconsent'),
                'cookieconsent__content_dismiss' => __('Got it!', 'wp-genero-cookieconsent'),
                'cookieconsent__content_allow' => __('Allow cookies', 'wp-genero-cookieconsent'),
                'cookieconsent__content_deny' => __('Decline cookies', 'wp-genero-cookieconsent'),
                'cookieconsent__content_link' => __('See our privacy policy for more information.', 'wp-genero-cookieconsent'),
                'cookieconsent__content_href' => $this->default_privacy_policy_url(),
                'cookieconsent__revoke_button' => __('Cookie Policy', 'wp-genero-cookieconsent'),
                'cookieconsent__show_revoke' => false,
            ];
            if (function_exists('get_fields') && ($acf = get_fields('cookieconsent_options'))) {
                foreach ($acf as $_key => $value) {
                    // Empty values use defaults.
                    if (is_string($value) && !empty($value)) {
                        $options[$_key] = $value;
                    } elseif (!is_string($value)) {
                        $options[$_key] = $value;
                    }
                }
            }

            foreach ($options as $_key => $value) {
                $_key = str_replace('cookieconsent__', '', $_key);
                $this->options[$_key] = $value;
            }
        }

        return isset($key) ? $this->options[$key] : $this->options;
    }

    /**
     * Register all assets.
     */
    public function register_assets()
    {
        $this->registerStyle('wp-genero-cookieconsent/css/library', 'dist/cookieconsent.min.css');

        $this->registerScript('wp-genero-cookieconsent/js', 'dist/main.js', ['jquery']);
        $this->registerStyle('wp-genero-cookieconsent/css', 'dist/main.css');
    }

    /**
     * Enqueue all assets.
     */
    public function enqueue_assets()
    {
        wp_localize_script('wp-genero-cookieconsent/js', '_genero_cookieconsent', apply_filters('wp-genero-cookieconsent/options', [
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
                    'opt-in' => '<div class="cc-compliance">{{allow}}{{deny}}</div>',
                    'opt-out' =>'<div class="cc-compliance">{{deny}}{{dismiss}}</div>',
                ],
                'elements' => [
                    'dismiss' => '<a aria-label="dismiss cookie message" tabindex="0" class="cc-btn cc-dismiss button">{{dismiss}}</a>',
                    'allow' => '<a aria-label="allow cookies" tabindex="0" class="cc-btn cc-allow button">{{allow}}</a>',
                    'deny' => '<a aria-label="deny cookies" tabindex="0" class="cc-btn cc-deny">{{deny}}</a>',
                ],
                'revokeBtn' => '<div class="cc-revoke {{classes}}">' . $this->options('revoke_button') . '</div>',
                'dismissOnScroll' => false,
                'revokable' => true,
                'alwaysShowRevoke' => $this->options('show_revoke'),
                'position' => $this->options('position'),
                'theme' => $this->options('theme'),
                'layout' => $this->options('layout'),
                'layouts' => [
                    'basic' => '<div class="cc-container basic">{{messagelink}}{{compliance}}</div>',
                    'foundation-reveal' => '<div class="cc-container reveal">{{messagelink}}{{compliance}}</div>',
                    'foundation-callout' => '<div class="cc-container callout">{{messagelink}}{{compliance}}</div>',
                ],
            ],
        ]));

        $this->enqueueScript('wp-genero-cookieconsent/js');
        $this->enqueueStyle('wp-genero-cookieconsent/css/library');
        $this->enqueueStyle('wp-genero-cookieconsent/css');
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
        return '';
    }

    /**
     * Load plugin textdomain.
     */
    public function load_textdomain()
    {
        load_plugin_textdomain('wp-genero-cookieconsent', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
}

Plugin::getInstance();
