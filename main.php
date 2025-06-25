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

/**
 * Main EcoGear Plugin Class
 */
class EcoGear_Plugin {
    
    /**
     * Initialize the plugin
     */
    public static function init() {
        // Add admin menu
        add_action('admin_menu', [__CLASS__, 'add_admin_menu']);
        
        // Add dashboard widget
        add_action('wp_dashboard_setup', [__CLASS__, 'add_dashboard_widget']);
        
        // Register custom order statuses on init
        add_action('init', [__CLASS__, 'register_order_statuses']);
        
        // Add admin styles for logo
        add_action('admin_head', [__CLASS__, 'add_admin_logo_styles']);
    }

    /**
     * Add admin styles for logo in menu
     */
    public static function add_admin_logo_styles() {
        echo '<style>
            /* EcoGear Admin Menu Logo Styles */
            #adminmenu .toplevel_page_ecogear .wp-menu-image {
                background-size: 20px 20px !important;
                background-repeat: no-repeat !important;
                background-position: center center !important;
                opacity: 0.7;
                transition: all 0.3s ease;
            }
            
            #adminmenu .toplevel_page_ecogear:hover .wp-menu-image,
            #adminmenu .toplevel_page_ecogear.wp-has-current-submenu .wp-menu-image,
            #adminmenu .toplevel_page_ecogear.current .wp-menu-image {
                opacity: 1;
                transform: scale(1.05);
            }
            
            /* Hide default dashicon if custom icon is used */
            #adminmenu .toplevel_page_ecogear .wp-menu-image:before {
                content: none !important;
                display: none !important;
            }
        </style>';
    }

    /**
     * Add admin menu
     */
    public static function add_admin_menu() {
        // Use the best available menu icon
        $menu_icon = EcoGear_Config::get_best_menu_icon();
        
        add_menu_page(
            EcoGear_Config::MENU_PAGE_TITLE,
            EcoGear_Config::MENU_TITLE,
            EcoGear_Config::MENU_CAPABILITY,
            EcoGear_Config::MENU_SLUG,
            [__CLASS__, 'render_admin_page'],
            $menu_icon,
            EcoGear_Config::MENU_POSITION
        );
        
        // Add JavaScript to enhance menu icon display if needed
        add_action('admin_footer', [__CLASS__, 'add_menu_icon_script']);
    }
    
    /**
     * Add JavaScript to set menu icon dynamically
     */
    public static function add_menu_icon_script() {
        if (EcoGear_Config::logo_exists()) {
            $logo_url = EcoGear_Config::get_logo_url();
            echo '<script>
                jQuery(document).ready(function($) {
                    // Enhance the menu icon with the actual logo
                    var menuItem = $("#adminmenu .toplevel_page_ecogear .wp-menu-image");
                    if (menuItem.length && "' . $logo_url . '") {
                        menuItem.css({
                            "background-image": "url(\'' . $logo_url . '\')",
                            "background-size": "18px 18px",
                            "background-repeat": "no-repeat",
                            "background-position": "center center"
                        });
                        
                        // Hide any existing content
                        menuItem.find("*").hide();
                        menuItem.html("");
                    }
                });
            </script>';
        }
    }

    /**
     * Add dashboard widget
     */
    public static function add_dashboard_widget() {
        wp_add_dashboard_widget(
            EcoGear_Config::WIDGET_ID,
            EcoGear_Config::WIDGET_TITLE,
            [__CLASS__, 'render_dashboard_widget']
        );
    }

    /**
     * Register custom order statuses
     */
    public static function register_order_statuses() {
        EcoGear_Order_Status::register_statuses();
    }

    /**
     * Render admin page
     */
    public static function render_admin_page() {
        EcoGear_UI::render_api_keys_page();
    }

    /**
     * Render dashboard widget
     */
    public static function render_dashboard_widget() {
        EcoGear_UI::render_dashboard_widget();
    }
}

// Initialize the plugin
EcoGear_Plugin::init();
