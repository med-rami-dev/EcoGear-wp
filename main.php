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
        
        // Register custom order statuses on init
        add_action('init', [__CLASS__, 'register_order_statuses']);
        
        // Add admin styles for logo
        add_action('admin_head', [__CLASS__, 'add_admin_logo_styles']);
    }

    /**
     * Add admin styles for logo in menu
     */
    public static function add_admin_logo_styles() {
        if (EcoGear_Config::logo_exists()) {
            $logo_url = EcoGear_Config::get_logo_url();
            echo '<style>
                /* EcoGear Admin Menu Logo Styles */
                #adminmenu .toplevel_page_ecogear .wp-menu-image {
                    background-image: url("' . esc_url($logo_url) . '") !important;
                    background-size: 25px 25px !important;
                    background-repeat: no-repeat !important;
                    background-position: center center !important;
                    opacity: 0.8;
                    transition: all 0.3s ease;
                    margin: 0 !important;
                    padding: 0 !important;
                    border: none !important;
                    box-shadow: none !important;
                }
                
                #adminmenu .toplevel_page_ecogear:hover .wp-menu-image,
                #adminmenu .toplevel_page_ecogear.wp-has-current-submenu .wp-menu-image,
                #adminmenu .toplevel_page_ecogear.current .wp-menu-image {
                    opacity: 1;
                    transform: scale(1.1);
                }
                
                /* Hide default dashicon if custom icon is used */
                #adminmenu .toplevel_page_ecogear .wp-menu-image:before {
                    content: none !important;
                    display: none !important;
                }
                
                /* Ensure proper sizing on different admin states */
                #adminmenu .toplevel_page_ecogear .wp-menu-image img {
                    display: none !important;
                }
                
                /* Remove spacing and lines around the menu item */
                #adminmenu .toplevel_page_ecogear {
                    margin: 0 !important;
                    padding: 0 !important;
                    border-bottom: none !important;
                    box-shadow: none !important;
                }
                
                #adminmenu .toplevel_page_ecogear a {
                    margin: 0 !important;
                    padding: 8px 0 !important;
                    border-bottom: none !important;
                    box-shadow: none !important;
                }
            </style>';
        }
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
                    if (menuItem.length && "' . esc_js($logo_url) . '") {
                        // Apply the background image
                        menuItem.css({
                            "background-image": "url(\'' . esc_js($logo_url) . '\')",
                            "background-size": "20px 20px",
                            "background-repeat": "no-repeat",
                            "background-position": "center center"
                        });
                        
                        // Hide any existing dashicon content
                        menuItem.find("*").hide();
                        menuItem.html("");
                        
                        // Ensure proper display
                        menuItem.show();
                    }
                });
            </script>';
        }
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
}

// Initialize the plugin
EcoGear_Plugin::init();
