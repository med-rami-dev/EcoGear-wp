<?php
/**
 * EcoGear API Hooks Class
 * Handles API endpoint refresh and customer notes synchronization
 */

if (!defined('ABSPATH')) exit;

class EcoGear_API_Hooks {

    /**
     * Cache for preventing duplicate refresh operations
     */
    private static $refresh_cache = [];
    
    /**
     * Cache for statistics to reduce database queries
     */
    private static $stats_cache = null;

    /**
     * Initialize API hooks
     */
    public static function init() {
        // Hook into customer note actions
        add_action('woocommerce_new_customer_note', [__CLASS__, 'refresh_order_on_note_add'], 10, 1);
        add_action('woocommerce_customer_note_added', [__CLASS__, 'refresh_order_on_note_add'], 10, 1);
        
        // Hook into order note actions (covers all note types)
        add_action('woocommerce_order_note_added', [__CLASS__, 'refresh_order_data'], 10, 2);
        
        // Hook into order updates via API
        add_action('woocommerce_rest_insert_shop_order_note', [__CLASS__, 'refresh_order_via_api'], 10, 3);
        add_action('woocommerce_rest_insert_shop_order_object', [__CLASS__, 'clear_order_cache'], 10, 3);
        
        // Clear object cache when orders are updated
        add_action('woocommerce_update_order', [__CLASS__, 'clear_order_cache_by_id'], 10, 1);
        add_action('woocommerce_order_status_changed', [__CLASS__, 'clear_order_cache_by_id'], 10, 1);
        
        // Add custom REST API endpoint for manual refresh
        add_action('rest_api_init', [__CLASS__, 'register_refresh_endpoint']);
    }

    /**
     * Refresh order data when customer note is added
     * 
     * @param array $comment_data Note data
     */
    public static function refresh_order_on_note_add($comment_data) {
        if (isset($comment_data['comment_post_ID'])) {
            $order_id = $comment_data['comment_post_ID'];
            self::force_refresh_order($order_id);
        }
    }

    /**
     * Refresh order data when any note is added
     * 
     * @param int $note_id Note ID
     * @param WC_Order $order Order object
     */
    public static function refresh_order_data($note_id, $order) {
        if ($order instanceof WC_Order) {
            self::force_refresh_order($order->get_id());
        }
    }

    /**
     * Refresh order via REST API
     * 
     * @param WP_Comment $note Note object
     * @param WP_REST_Request $request Request object
     * @param bool $creating Whether creating or updating
     */
    public static function refresh_order_via_api($note, $request, $creating) {
        if ($note && isset($note->comment_post_ID)) {
            self::force_refresh_order($note->comment_post_ID);
        }
    }

    /**
     * Clear order cache
     * 
     * @param WC_Order $order Order object
     * @param WP_REST_Request $request Request object
     * @param bool $creating Whether creating or updating
     */
    public static function clear_order_cache($order, $request, $creating) {
        if ($order instanceof WC_Order) {
            self::force_refresh_order($order->get_id());
        }
    }

    /**
     * Clear order cache by ID
     * 
     * @param int $order_id Order ID
     */
    public static function clear_order_cache_by_id($order_id) {
        self::force_refresh_order($order_id);
    }

    /**
     * Force refresh order data and clear all caches (optimized)
     * 
     * @param int $order_id Order ID
     */
    public static function force_refresh_order($order_id) {
        if (!$order_id) return;

        // Prevent duplicate refreshes within a short time window
        $cache_key = 'refresh_' . $order_id;
        if (isset(self::$refresh_cache[$cache_key]) && 
            (time() - self::$refresh_cache[$cache_key]) < 5) {
            return; // Skip if refreshed within last 5 seconds
        }
        
        self::$refresh_cache[$cache_key] = time();

        // Clear WordPress object cache efficiently
        $cache_keys = [
            ['key' => $order_id, 'group' => 'posts'],
            ['key' => $order_id, 'group' => 'post_meta'],
            ['key' => 'order-' . $order_id, 'group' => 'orders'],
            ['key' => $order_id, 'group' => 'wc_order_statuses']
        ];
        
        foreach ($cache_keys as $cache) {
            wp_cache_delete($cache['key'], $cache['group']);
        }
        
        // Clear transients efficiently
        $transients = [
            'wc_order_' . $order_id,
            'woocommerce_order_' . $order_id
        ];
        
        foreach ($transients as $transient) {
            delete_transient($transient);
        }
        
        // Only update order if it exists to avoid unnecessary database queries
        $order = wc_get_order($order_id);
        if ($order && $order instanceof WC_Order) {
            // Update the post modified date efficiently
            global $wpdb;
            $wpdb->update(
                $wpdb->posts,
                [
                    'post_modified' => current_time('mysql'),
                    'post_modified_gmt' => current_time('mysql', 1)
                ],
                ['ID' => $order_id],
                ['%s', '%s'],
                ['%d']
            );
            
            // Clear WooCommerce cache more efficiently
            if (class_exists('WC_Cache_Helper')) {
                WC_Cache_Helper::get_transient_version('orders', true);
            }
            
            // Update statistics asynchronously to avoid blocking
            wp_schedule_single_event(time(), 'ecogear_update_stats', [$order_id]);
            
            // Trigger action for other plugins/code to hook into
            do_action('ecogear_order_refreshed', $order_id, $order);
        }
        
        // Clean up old cache entries to prevent memory leaks
        self::cleanup_refresh_cache();
    }

    /**
     * Register REST API endpoint for manual order refresh
     */
    public static function register_refresh_endpoint() {
        register_rest_route('ecogear/v1', '/refresh-order/(?P<id>\d+)', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'rest_refresh_order'],
            'permission_callback' => [__CLASS__, 'check_refresh_permissions'],
            'args' => [
                'id' => [
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric($param);
                    }
                ]
            ]
        ]);
    }

    /**
     * REST API callback to refresh order
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public static function rest_refresh_order($request) {
        $order_id = $request->get_param('id');
        
        if (!$order_id) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Order ID is required'
            ], 400);
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        self::force_refresh_order($order_id);

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Order refreshed successfully',
            'order_id' => $order_id,
            'modified' => $order->get_date_modified()->date('Y-m-d H:i:s')
        ], 200);
    }

    /**
     * Check permissions for refresh endpoint
     * 
     * @param WP_REST_Request $request Request object
     * @return bool True if user has permission
     */
    public static function check_refresh_permissions($request) {
        return current_user_can('manage_woocommerce') || current_user_can('edit_shop_orders');
    }

    /**
     * Add order refresh meta box to admin
     */
    public static function add_refresh_meta_box() {
        add_meta_box(
            'ecogear-order-refresh',
            'EcoGear Order Refresh',
            [__CLASS__, 'render_refresh_meta_box'],
            'shop_order',
            'side',
            'high'
        );
    }

    /**
     * Render refresh meta box
     * 
     * @param WP_Post $post Post object
     */
    public static function render_refresh_meta_box($post) {
        echo '<div class="ecogear-refresh-box">';
        echo '<p>Force refresh this order in the API endpoints:</p>';
        echo '<button type="button" class="button button-secondary" onclick="ecoGearRefreshOrder(' . $post->ID . ')">Refresh Order Data</button>';
        echo '<div id="ecogear-refresh-result"></div>';
        echo '</div>';

        // Add JavaScript for the refresh button
        ?>
        <script>
        function ecoGearRefreshOrder(orderId) {
            const button = event.target;
            const result = document.getElementById('ecogear-refresh-result');
            
            button.disabled = true;
            button.textContent = 'Refreshing...';
            
            fetch(`/wp-json/ecogear/v1/refresh-order/${orderId}`, {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    result.innerHTML = '<div style="color: green; margin-top: 10px;">✅ Order refreshed successfully!</div>';
                } else {
                    result.innerHTML = '<div style="color: red; margin-top: 10px;">❌ ' + data.message + '</div>';
                }
            })
            .catch(error => {
                result.innerHTML = '<div style="color: red; margin-top: 10px;">❌ Error: ' + error.message + '</div>';
            })
            .finally(() => {
                button.disabled = false;
                button.textContent = 'Refresh Order Data';
                
                setTimeout(() => {
                    result.innerHTML = '';
                }, 3000);
            });
        }
        </script>
        <style>
        .ecogear-refresh-box button {
            width: 100%;
            margin-bottom: 10px;
        }
        </style>
        <?php
    }

    /**
     * Get refresh statistics (with caching)
     * 
     * @return array Statistics about order refreshes
     */
    public static function get_refresh_stats() {
        // Return cached stats if available
        if (self::$stats_cache !== null) {
            return self::$stats_cache;
        }
        
        self::$stats_cache = get_option('ecogear_refresh_stats', [
            'total_refreshes' => 0,
            'last_refresh' => null,
            'orders_refreshed_today' => 0,
            'last_refresh_date' => date('Y-m-d')
        ]);

        return self::$stats_cache;
    }

    /**
     * Update refresh statistics (optimized)
     * 
     * @param int $order_id Order ID that was refreshed
     */
    public static function update_refresh_stats($order_id) {
        $stats = self::get_refresh_stats();
        $stats['total_refreshes']++;
        $stats['last_refresh'] = current_time('mysql');
        
        // Reset daily counter if it's a new day
        $today = date('Y-m-d');
        $last_refresh_date = isset($stats['last_refresh_date']) ? $stats['last_refresh_date'] : '';
        
        if ($last_refresh_date !== $today) {
            $stats['orders_refreshed_today'] = 1;
            $stats['last_refresh_date'] = $today;
        } else {
            $stats['orders_refreshed_today']++;
        }

        // Update cache
        self::$stats_cache = $stats;
        
        // Update database
        update_option('ecogear_refresh_stats', $stats);
    }
    
    /**
     * Clean up old refresh cache entries to prevent memory leaks
     */
    private static function cleanup_refresh_cache() {
        $current_time = time();
        foreach (self::$refresh_cache as $key => $timestamp) {
            if (($current_time - $timestamp) > 60) { // Remove entries older than 1 minute
                unset(self::$refresh_cache[$key]);
            }
        }
    }
    
    /**
     * Hook for scheduled statistics update
     */
    public static function scheduled_stats_update($order_id) {
        self::update_refresh_stats($order_id);
    }
}
