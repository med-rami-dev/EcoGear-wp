<?php
/**
 * Plugin Name: EcoGear 
 * Description: Sustainable Orders management for your store
 * Version: 1.0
 * Author: EcoGear Team
 * Author URI: https://ecogear.xyz
 * Text Domain: ecogear
 * Domain Path: /languages
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) exit;

// Include required files
require_once plugin_dir_path(__FILE__) . 'includes/class-ecogear-config.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-ecogear-api.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-ecogear-order-status.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-ecogear-ui.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-ecogear-api-hooks.php';

/**
 * Main EcoGear Plugin Class
 */
class EcoGear_Plugin {
    
    /**
     * Plugin initialization flag to prevent duplicate initialization
     */
    private static $initialized = false;
    
    /**
     * Initialize the plugin
     */
    public static function init() {
        // Prevent duplicate initialization
        if (self::$initialized) {
            return;
        }
        
        self::$initialized = true;
        // Add admin menu
        add_action('admin_menu', [__CLASS__, 'add_admin_menu']);
        
        // Register custom order statuses on init
        add_action('init', [__CLASS__, 'register_order_statuses']);
        
        // Initialize API hooks for order refresh
        add_action('init', [__CLASS__, 'init_api_hooks']);
        
        // Register scheduled events
        add_action('ecogear_update_stats', ['EcoGear_API_Hooks', 'scheduled_stats_update']);
        
        // Add admin styles for logo (use wp_enqueue_style instead of inline styles)
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_styles']);
        
        // Add order refresh meta box to admin
        add_action('add_meta_boxes', ['EcoGear_API_Hooks', 'add_refresh_meta_box']);
    }

    /**
     * Add admin styles for logo in menu
     */
    public static function add_admin_logo_styles() {
        // Use cached logo data to avoid repeated file operations
        static $styles_added = false;
        if ($styles_added) return;
        
        $logo_config = EcoGear_Config::get_cached_logo_config();
        if ($logo_config['exists']) {
            wp_add_inline_style('admin-menu', self::get_admin_logo_css($logo_config['url']));
            $styles_added = true;
        }
    }
    
    /**
     * Get admin logo CSS
     */
    private static function get_admin_logo_css($logo_url) {
        return "
            #adminmenu .toplevel_page_ecogear .wp-menu-image {
                background-image: url('" . esc_url($logo_url) . "') !important;
                background-size: 25px 25px !important;
                background-repeat: no-repeat !important;
                background-position: center center !important;
                opacity: 0.8;
                transition: opacity 0.2s ease;
            }
            #adminmenu .toplevel_page_ecogear:hover .wp-menu-image {
                opacity: 1;
            }
            #adminmenu .toplevel_page_ecogear .wp-menu-image:before {
                display: none !important;
            }
        ";
    }

    /**
     * Add admin menu
     */
    public static function add_admin_menu() {
        // Use cached menu icon to avoid repeated file operations
        $menu_icon = EcoGear_Config::get_cached_menu_icon();
        
        add_menu_page(
            EcoGear_Config::MENU_PAGE_TITLE,
            EcoGear_Config::MENU_TITLE,
            EcoGear_Config::MENU_CAPABILITY,
            EcoGear_Config::MENU_SLUG,
            [__CLASS__, 'render_admin_page'],
            $menu_icon,
            EcoGear_Config::MENU_POSITION
        );
    }
    
    /**
     * Enqueue admin styles
     */
    public static function enqueue_admin_styles() {
        // Only load on EcoGear admin pages to improve performance
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'ecogear') === false) {
            return;
        }
        
        // Enqueue main admin styles
        wp_enqueue_style(
            'ecogear-admin',
            plugin_dir_url(__FILE__) . 'assets/css/ecogear-styles.css',
            [],
            EcoGear_Config::VERSION
        );
        
        // Add logo styles if needed
        $logo_config = EcoGear_Config::get_cached_logo_config();
        if ($logo_config['exists']) {
            wp_add_inline_style('ecogear-admin', self::get_admin_logo_css($logo_config['url']));
        }
    }

    /**
     * Remove the JavaScript menu icon script (no longer needed)
     */

    /**
     * Register custom order statuses
     */
    public static function register_order_statuses() {
        EcoGear_Order_Status::register_statuses();
    }

    /**
     * Initialize API hooks for order refresh
     */
    public static function init_api_hooks() {
        EcoGear_API_Hooks::init();
    }

    /**
     * Render admin page
     */
    public static function render_admin_page() {
        EcoGear_UI::render_api_keys_page();
    }
}

// Initialize the plugin
EcoGear_Plugin::init();
