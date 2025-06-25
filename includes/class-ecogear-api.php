<?php
/**
 * EcoGear API Management Class
 * Handles all API key generation and management logic
 */

if (!defined('ABSPATH')) exit;

class EcoGear_API {
      /**
     * Generate API keys for a user using the proven working method
     * 
     * @param int $user_id User ID
     * @return array|WP_Error API keys or error
     */
    public static function generate_keys_for_user($user_id) {
        global $wpdb;

        $consumer_key    = 'ck_' . wc_rand_hash();
        $consumer_secret = 'cs_' . wc_rand_hash();

        $data = [
            'user_id'         => $user_id,
            'description'     => 'EcoGear',
            'permissions'     => 'read_write',
            'consumer_key'    => wc_api_hash($consumer_key),
            'consumer_secret' => $consumer_secret,
            'truncated_key'   => substr($consumer_key, -7),
        ];

        $inserted = $wpdb->insert("{$wpdb->prefix}woocommerce_api_keys", $data);

        if (!$inserted) {
            return new WP_Error('api_key_error', 'Failed to insert API key into database.');
        }        return [
            'consumer_key'    => $consumer_key,
            'consumer_secret' => $consumer_secret,
            'key_id'          => $wpdb->insert_id,
            'permissions'     => 'read_write',
            'description'     => $data['description']
        ];
    }    /**
     * Get API keys for a user
     * 
     * @param int $user_id User ID
     * @return array|false API keys or false if not found
     */
    public static function get_user_keys($user_id) {
        $keys = get_user_meta($user_id, EcoGear_Config::API_KEY_META_KEY, true);

        if (!$keys || empty($keys)) {
            $keys = self::generate_keys_for_user($user_id);
            if (!is_wp_error($keys)) {
                update_user_meta($user_id, EcoGear_Config::API_KEY_META_KEY, $keys);
                return $keys;
            }
            return false;
        }

        return $keys;
    }

    /**
     * Delete API keys for a user
     * 
     * @param int $user_id User ID
     * @return bool True if deleted successfully
     */
    public static function delete_user_keys($user_id) {
        $keys = get_user_meta($user_id, EcoGear_Config::API_KEY_META_KEY, true);
        
        if ($keys && isset($keys['key_id'])) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'woocommerce_api_keys';
            $wpdb->delete($table_name, ['key_id' => $keys['key_id']]);
        }
        
        return delete_user_meta($user_id, EcoGear_Config::API_KEY_META_KEY);
    }

    /**
     * Regenerate API keys for a user
     * 
     * @param int $user_id User ID
     * @return array|false New API keys or false on failure
     */
    public static function regenerate_user_keys($user_id) {
        // Delete existing keys first
        self::delete_user_keys($user_id);
        
        // Generate new keys
        return self::get_user_keys($user_id);
    }

    /**
     * Force regenerate API keys for a user (clears cache first)
     * 
     * @param int $user_id User ID
     * @return array|false New API keys or false on failure
     */
    public static function force_regenerate_user_keys($user_id) {
        // Delete from user meta first
        delete_user_meta($user_id, EcoGear_Config::API_KEY_META_KEY);
        
        // Delete from database
        self::delete_user_keys($user_id);
        
        // Generate fresh keys
        return self::get_user_keys($user_id);
    }

    /**
     * Check if user has access to API functionality
     * 
     * @return bool True if user has access, false otherwise
     */
    public static function user_has_access() {
        return is_user_logged_in() && class_exists('WooCommerce');
    }

    /**
     * Check if current user can manage WooCommerce
     * 
     * @return bool True if user can manage, false otherwise
     */
    public static function user_can_manage() {
        return current_user_can('manage_woocommerce');
    }    /**
     * Validate WooCommerce requirements
     * 
     * @return array Array with 'valid' boolean and 'message' string
     */
    public static function validate_requirements() {
        $requirements = EcoGear_Config::get_requirements();
        
        // Check PHP version
        if (version_compare(PHP_VERSION, $requirements['php_version'], '<')) {
            return [
                'valid' => false,
                'message' => 'PHP version ' . $requirements['php_version'] . ' or higher is required.'
            ];
        }
        
        // Check WordPress version
        global $wp_version;
        if (version_compare($wp_version, $requirements['wp_version'], '<')) {
            return [
                'valid' => false,
                'message' => 'WordPress version ' . $requirements['wp_version'] . ' or higher is required.'
            ];
        }
        
        // Check WooCommerce
        if (!class_exists('WooCommerce')) {
            return [
                'valid' => false,
                'message' => 'WooCommerce plugin is required but not active.'
            ];
        }
        
        // Check WooCommerce functions
        if (!function_exists('wc_rand_hash') || !function_exists('wc_api_hash')) {
            return [
                'valid' => false,
                'message' => 'Required WooCommerce functions are not available. Please update WooCommerce.'
            ];
        }
        
        // Check WooCommerce API keys table
        global $wpdb;
        $table_name = $wpdb->prefix . 'woocommerce_api_keys';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        
        if (!$table_exists) {
            return [
                'valid' => false,
                'message' => 'WooCommerce API keys table is missing. Please deactivate and reactivate WooCommerce to create the required database tables.'
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'All requirements met.'
        ];
    }

    /**
     * Create WooCommerce API keys table if it doesn't exist
     * This is a fallback method in case WooCommerce didn't create the table properly
     * 
     * @return bool True if table exists or was created successfully
     */
    public static function ensure_api_keys_table_exists() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'woocommerce_api_keys';
        
        // Check if table already exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        if ($table_exists) {
            return true;
        }
        
        // Create the table using WooCommerce's expected schema
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            key_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            description varchar(200) DEFAULT NULL,
            permissions varchar(10) NOT NULL,
            consumer_key char(64) NOT NULL,
            consumer_secret char(43) NOT NULL,
            truncated_key char(7) NOT NULL,
            last_access datetime DEFAULT NULL,
            PRIMARY KEY (key_id),
            KEY consumer_key (consumer_key),
            KEY consumer_secret (consumer_secret)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Verify table was created
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        return !empty($table_exists);    }

}
