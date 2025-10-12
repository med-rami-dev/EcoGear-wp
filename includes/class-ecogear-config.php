<?php
/**
 * EcoGear Configuration
 * Central configuration for the plugin
 */

if (!defined('ABSPATH')) exit;

class EcoGear_Config {
    
    /**
     * Plugin version
     */
    const VERSION = '2.0';
    
    /**
     * Plugin name
     */
    const PLUGIN_NAME = 'EcoGear';
    
    /**
     * Plugin description
     */
    const PLUGIN_DESCRIPTION = 'Orders Manager For EcoGear APP - Sustainable Orders management for your store';
    
    /**
     * Cached logo configuration to avoid repeated file operations
     */
    private static $logo_cache = null;
    
    /**
     * Cached menu icon to avoid repeated calculations
     */
    private static $menu_icon_cache = null;
      /**
     * Menu settings
     */
    const MENU_PAGE_TITLE = 'EcoGear';
    const MENU_TITLE = 'EcoGear';
    const MENU_CAPABILITY = 'read';
    const MENU_SLUG = 'ecogear';
    const MENU_ICON = 'dashicons-rest-api'; // Will be replaced with SVG logo
    const MENU_POSITION = 56;
    
    /**
     * Logo settings
     */
    const LOGO_PATH = 'assets/images/logo.svg';
    
    /**
     * Get cached logo configuration to avoid repeated file operations
     */
    public static function get_cached_logo_config() {
        if (self::$logo_cache === null) {
            $logo_path = plugin_dir_path(dirname(__FILE__)) . self::LOGO_PATH;
            $exists = file_exists($logo_path);
            
            self::$logo_cache = [
                'exists' => $exists,
                'path' => $logo_path,
                'url' => $exists ? plugin_dir_url(dirname(__FILE__)) . self::LOGO_PATH : '',
                'data_url' => $exists ? self::generate_logo_data_url($logo_path) : ''
            ];
        }
        
        return self::$logo_cache;
    }
    
    /**
     * Get cached menu icon
     */
    public static function get_cached_menu_icon() {
        if (self::$menu_icon_cache === null) {
            $logo_config = self::get_cached_logo_config();
            
            if ($logo_config['exists'] && $logo_config['data_url']) {
                self::$menu_icon_cache = $logo_config['data_url'];
            } else {
                self::$menu_icon_cache = self::get_menu_svg_icon();
            }
        }
        
        return self::$menu_icon_cache;
    }
    
    /**
     * Generate logo data URL (optimized version)
     */
    private static function generate_logo_data_url($logo_path) {
        if (!file_exists($logo_path)) {
            return '';
        }
        
        // Use WordPress filesystem if available for better performance
        if (function_exists('WP_Filesystem')) {
            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once ABSPATH . '/wp-admin/includes/file.php';
                WP_Filesystem();
            }
            
            if ($wp_filesystem) {
                $svg_content = $wp_filesystem->get_contents($logo_path);
            } else {
                $svg_content = file_get_contents($logo_path);
            }
        } else {
            $svg_content = file_get_contents($logo_path);
        }
        
        if (!$svg_content) {
            return '';
        }
        
        // Optimize SVG content
        $svg_content = preg_replace('/\s+/', ' ', $svg_content);
        $svg_content = str_replace('"', "'", $svg_content);
        $svg_content = trim($svg_content);
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg_content);
    }
    
    /**
     * Check if logo file exists (cached version)
     */
    public static function logo_exists() {
        return self::get_cached_logo_config()['exists'];
    }
    
    /**
     * Get logo URL (cached version)
     */
    public static function get_logo_url() {
        $logo_config = self::get_cached_logo_config();
        return $logo_config['exists'] ? $logo_config['url'] : '';
    }
    
    /**
     * Dashboard widget settings
     */
    const WIDGET_ID = 'ecogear_dashboard_widget';
    const WIDGET_TITLE = 'EcoGear API Status';
    
    /**
     * API settings
     */
    const API_KEY_META_KEY = '_wc_api_keys';
    const API_PERMISSIONS = 'read_write';
    
    /**
     * UI settings
     */
    const PRIMARY_COLOR = '#667eea';
    const SECONDARY_COLOR = '#764ba2';
    const SUCCESS_COLOR = '#48bb78';
    const ERROR_COLOR = '#f56565';
    const WARNING_COLOR = '#ed8936';
    const INFO_COLOR = '#38b2ac';
    
    /**
     * Animation settings
     */
    const ANIMATION_DURATION = '0.3s';
    const ANIMATION_EASING = 'ease-out';
    
    /**
     * Get plugin directory path
     */
    public static function get_plugin_path() {
        return plugin_dir_path(dirname(__FILE__));
    }
    
    /**
     * Get plugin directory URL
     */
    public static function get_plugin_url() {
        return plugin_dir_url(dirname(__FILE__));
    }
    
    /**
     * Get assets directory path
     */
    public static function get_assets_path() {
        return self::get_plugin_path() . 'assets/';
    }
    
    /**
     * Get assets directory URL
     */
    public static function get_assets_url() {
        return self::get_plugin_url() . 'assets/';
    }
    
    /**
     * Get includes directory path
     */
    public static function get_includes_path() {
        return self::get_plugin_path() . 'includes/';
    }
    
    /**
     * Check if debug mode is enabled
     */
    public static function is_debug_mode() {
        return defined('WP_DEBUG') && WP_DEBUG;
    }
    
    /**
     * Get system requirements
     */
    public static function get_requirements() {
        return [
            'php_version' => '7.4',
            'wp_version' => '5.0',
            'woocommerce' => true,
            'required_plugins' => ['woocommerce/woocommerce.php']
        ];
    }
    
    /**
     * Get default settings
     */
    public static function get_default_settings() {
        return [
            'enable_dashboard_widget' => true,
            'enable_animations' => true,
            'auto_generate_keys' => true,
            'show_security_notices' => true,
            'enable_copy_notifications' => true
        ];
    }
    
    /**
     * Create a simple SVG icon for menu use
     */
    public static function get_menu_svg_icon() {
        // Create a simplified SVG version for menu use
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <circle cx="10" cy="10" r="8" fill="#22c55e"/>
            <path d="M6 10h8M10 6v8" stroke="white" stroke-width="2" stroke-linecap="round"/>
        </svg>';
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
    
    /**
     * Get the best available menu icon (optimized)
     */
    public static function get_best_menu_icon() {
        return self::get_cached_menu_icon();
    }
}
