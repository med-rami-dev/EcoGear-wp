<?php
/**
 * EcoGear Order Status Management Class
 * Handles all custom order status logic
 */

if (!defined('ABSPATH')) exit;

class EcoGear_Order_Status {

    /**
     * Get all custom order statuses
     * 
     * @return array Custom order statuses with labels, colors, and icons
     */
    public static function get_custom_statuses() {
        return [
            'wc-failed-01' => [
                'label' => 'Failed 01', 
                'color' => '#f56565', 
                'icon' => '❌',
                'wp_label' => 'Failed 01'
            ],
            'wc-failed-02' => [
                'label' => 'Failed 02', 
                'color' => '#f56565', 
                'icon' => '❌',
                'wp_label' => 'Failed 02'
            ],
            'wc-failed-03' => [
                'label' => 'Failed 03', 
                'color' => '#f56565', 
                'icon' => '❌',
                'wp_label' => 'Failed 03'
            ],
            'wc-failed-04' => [
                'label' => 'Failed 04', 
                'color' => '#f56565', 
                'icon' => '❌',
                'wp_label' => 'Failed 04'
            ],
            'wc-failed-05' => [
                'label' => 'Failed 05', 
                'color' => '#f56565', 
                'icon' => '❌',
                'wp_label' => 'Failed 05'
            ],
            'wc-confirmed' => [
                'label' => 'Confirmed', 
                'color' => '#48bb78', 
                'icon' => '✅',
                'wp_label' => 'Confirmed'
            ],
            'wc-cancelled-custom' => [
                'label' => 'Cancelled', 
                'color' => '#ed8936', 
                'icon' => '🚫',
                'wp_label' => 'Cancelled'
            ],
            'wc-scheduled' => [
                'label' => 'Scheduled', 
                'color' => '#4299e1', 
                'icon' => '📅',
                'wp_label' => 'Scheduled'
            ],
            'wc-duplicate' => [
                'label' => 'Duplicate', 
                'color' => '#a0aec0', 
                'icon' => '📄',
                'wp_label' => 'Duplicate'
            ],
            'wc-wrong-number' => [
                'label' => 'Wrong Number', 
                'color' => '#e53e3e', 
                'icon' => '📞',
                'wp_label' => 'Wrong Number'
            ],
            'wc-wrong-order' => [
                'label' => 'Wrong Order', 
                'color' => '#e53e3e', 
                'icon' => '📦',
                'wp_label' => 'Wrong Order'
            ],
            'wc-sms-sent' => [
                'label' => 'SMS Sent', 
                'color' => '#38b2ac', 
                'icon' => '💬',
                'wp_label' => 'SMS Sent'
            ],
            'wc-to-be-confirmed' => [
                'label' => 'To Be Confirmed', 
                'color' => '#d69e2e', 
                'icon' => '⏳',
                'wp_label' => 'To Be Confirmed At'
            ],
            'wc-out-of-stock' => [
                'label' => 'Out of Stock', 
                'color' => '#e53e3e', 
                'icon' => '📋',
                'wp_label' => 'Out of Stock'
            ]
        ];
    }

    /**
     * Register custom order statuses with WordPress
     * 
     * @param bool $run_now Whether to run immediately or hook to filter
     */
    public static function register_statuses($run_now = false) {
        $statuses = self::get_custom_statuses();
        $wp_statuses = [];

        // Extract WordPress labels for registration
        foreach ($statuses as $key => $status) {
            $wp_statuses[$key] = $status['wp_label'];
        }

        // Register each custom status
        foreach ($wp_statuses as $key => $label) {
            register_post_status($key, [
                'label'                     => $label,
                'public'                    => true,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop($label . ' <span class="count">(%s)</span>', $label . ' <span class="count">(%s)</span>')
            ]);
        }

        // Merge custom statuses with WooCommerce defaults
        if (!$run_now) {
            add_filter('wc_order_statuses', function ($order_statuses) use ($wp_statuses) {
                return array_merge($order_statuses, $wp_statuses);
            }, 20);
        }
    }

    /**
     * Get count of registered custom statuses
     * 
     * @return int Number of custom statuses
     */
    public static function get_status_count() {
        return count(self::get_custom_statuses());
    }
}
