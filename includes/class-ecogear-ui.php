<?php
/**
 * EcoGear UI Renderer Class
 * Handles all UI rendering and display logic
 */

if (!defined('ABSPATH')) exit;

class EcoGear_UI {
      /**
     * Render the main API keys page
     */
    public static function render_api_keys_page() {
        $user_id = get_current_user_id();
        
        // Enqueue assets
        self::enqueue_assets();
        
        echo '<div class="eco-simple-wrap">';        
        // Simple Header with Large Centered Logo Only
        echo '<div class="eco-header">';
        echo '<div class="eco-header-logo-center">';
        if (EcoGear_Config::logo_exists()) {
            echo '<img src="' . EcoGear_Config::get_logo_url() . '" alt="EcoGear Logo" class="eco-logo-big" />';
        } else {
            echo '<span class="eco-icon-big">🛒</span>';
        }
        echo '</div>';
        echo '</div>';

        // Check access
        if (!EcoGear_API::user_has_access()) {
            echo '<div class="eco-error">You need to be logged in and have WooCommerce installed.</div>';
            echo '</div>';
            return;
        }        // Main Action Buttons - Vertical Layout
        echo '<div class="eco-buttons-vertical">';
          // Access Keys Button
        echo '<div class="eco-button-card">';
        echo '<h3>🔑 Access Keys</h3>';
        echo '<p>Generate your APP ID and APP KEY for secure access</p>';
        if (isset($_POST['generate_api'])) {
            self::handle_api_generation($user_id);        } else {
            echo '<form method="post">';
            wp_nonce_field('generate_api');
            echo '<button type="submit" name="generate_api" class="eco-btn eco-btn-primary">Get Started</button>';
            echo '</form>';
              // Add helpful links under Get Started
            echo '<div class="eco-helpful-links" style="text-align: center;">';
            echo '<h4>Quick Links</h4>';
            echo '<ul class="eco-links-list" style="list-style: none; padding: 0; display: inline-block;">';
            echo '<li style="display: inline-block; margin: 0 10px;"><a href="#" class="eco-link" onclick="alert(\'Support\'); return false;">📞 Support</a></li>';
            echo '<li style="display: inline-block; margin: 0 10px;"><a href="#" class="eco-link" onclick="alert(\'Documentation\'); return false;">📚 Documentation</a></li>';
            echo '</ul>';
            echo '</div>';
        }echo '</div>';
        
        echo '</div>'; // End buttons
        echo '</div>'; // End wrapper
    }

    /**
     * Handle API key generation
     */
    private static function handle_api_generation($user_id) {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'generate_api')) {
            echo '<div class="eco-error">Security check failed.</div>';
            return;
        }
        
        // Force regenerate keys
        $keys = EcoGear_API::force_regenerate_user_keys($user_id);        if ($keys && !is_wp_error($keys)) {
            echo '<div class="eco-success">✅ Access Keys Generated Successfully!</div>';
            echo '<div class="eco-keys">';
              // Combined QR Code Section
            echo '<div class="eco-combined-qr">';
            echo '<h3>📱 Complete Access Information</h3>';
            echo '<p>Your complete access credentials and domain info in one QR code</p>';
            echo '<div class="eco-qr-display" id="combined-qr-container">';
            echo '<div class="eco-qr-loading" style="text-align: center; padding: 2rem; color: #64748b;">';
            echo '� Generating QR code...';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            
            echo '<div class="eco-key-item">';
            echo '<label>APP ID:</label>';
            echo '<div class="eco-key-input-wrapper">';
            echo '<input type="text" id="app-id" value="' . esc_attr($keys['consumer_key']) . '" readonly onclick="copyToClipboard(this)">';
            echo '<div class="eco-key-actions">';
            echo '<button type="button" class="eco-copy-btn" onclick="copyToClipboard(document.getElementById(\'app-id\'))">📋 Copy</button>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '<div class="eco-key-item">';
            echo '<label>APP KEY:</label>';
            echo '<div class="eco-key-input-wrapper">';
            echo '<input type="text" id="app-key" value="' . esc_attr($keys['consumer_secret']) . '" readonly onclick="copyToClipboard(this)">';
            echo '<div class="eco-key-actions">';
            echo '<button type="button" class="eco-copy-btn" onclick="copyToClipboard(document.getElementById(\'app-key\'))">📋 Copy</button>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
              // Add domain information
            echo '<div class="eco-domain-info">';
            echo '<h3>🌐 Domain Information</h3>';
            echo '<div class="eco-domain-item">';
            echo '<label>Domain:</label>';
            echo '<input type="text" id="domain-name" value="' . esc_attr(get_site_url()) . '" readonly>';
            echo '<input type="hidden" id="full-url" value="' . esc_attr(home_url($_SERVER['REQUEST_URI'])) . '">';
            echo '<div class="eco-key-actions">';
            echo '<button type="button" class="eco-copy-btn" onclick="copyToClipboard(document.getElementById(\'domain-name\'))">📋 Copy</button>';
            echo '</div>';
            echo '</div>';            echo '</div>';        } else {
            echo '<div class="eco-error">❌ Failed to generate access keys.</div>';
        }    }

    /**
     * Render hero header section
     */
    private static function render_hero_header() {
        echo '<div class="eco-hero-header">';        
        echo '<div class="eco-hero-content">';
        echo '<div class="eco-hero-icon">';
        if (EcoGear_Config::logo_exists()) {
            echo '<img src="' . EcoGear_Config::get_logo_url() . '" alt="EcoGear Logo" class="eco-hero-logo" style="height: 64px; width: auto;" />';
        } else {
            echo '<span class="eco-fallback-icon" style="font-size: 64px;">🔐</span>';
        }
        echo '</div>';
        echo '<h1 class="eco-hero-title">EcoGear Access Management</h1>';
        echo '<p class="eco-hero-subtitle">Secure access key management for your WooCommerce store</p>';
        echo '</div>';
        echo '<div class="eco-hero-bg"></div>';
        echo '</div>';
    }    /**
     * Render requirement error
     */
    private static function render_requirement_error($message) {
        echo '<div class="eco-error-card">';
        echo '<div class="eco-error-icon">🔧</div>';
        echo '<h3>System Requirements Not Met</h3>';
        echo '<p>' . esc_html($message) . '</p>';
        echo '</div>';
    }

    /**
     * Render access error
     */
    private static function render_access_error() {
        echo '<div class="eco-error-card">';
        echo '<div class="eco-error-icon">⚠️</div>';
        echo '<h3>Access Required</h3>';
        echo '<p>You must be logged in and have WooCommerce installed to access this feature.</p>';
        echo '</div>';
    }    /**
     * Render generation error
     */
    private static function render_generation_error($message = 'Failed to generate access keys. Please try again.') {
        echo '<div class="eco-error-card">';
        echo '<div class="eco-error-icon">❌</div>';
        echo '<h3>Generation Error</h3>';
        echo '<p>' . esc_html($message) . '</p>';
        
        // Add debug information if in debug mode
        if (EcoGear_Config::is_debug_mode()) {
            echo '<details style="margin-top: 15px;">';
            echo '<summary style="cursor: pointer; color: #666;">Debug Information</summary>';
            echo '<div style="margin-top: 10px; font-family: monospace; font-size: 12px; background: #f5f5f5; padding: 10px; border-radius: 5px;">';
            echo '<strong>User ID:</strong> ' . get_current_user_id() . '<br>';
            echo '<strong>WooCommerce Active:</strong> ' . (class_exists('WooCommerce') ? 'Yes' : 'No') . '<br>';            echo '<strong>Required Functions:</strong><br>';
            echo '- wc_rand_hash: ' . (function_exists('wc_rand_hash') ? 'Available' : 'Missing') . '<br>';
            echo '- wc_api_hash: ' . (function_exists('wc_api_hash') ? 'Available' : 'Missing') . '<br>';
            
            global $wpdb;
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}woocommerce_api_keys'");
            echo '<strong>Access Keys Table:</strong> ' . ($table_exists ? 'Exists' : 'Missing') . '<br>';
            echo '</div>';
            echo '</details>';
        }
        echo '</div>';
    }    /**
     * Render access keys section
     * 
     * @param array $keys Access keys data
     */    private static function render_api_keys_section($keys) {        // Handle regenerate request
        if (isset($_POST['regenerate_api_keys']) && wp_verify_nonce($_POST['_wpnonce'], 'regenerate_api_keys')) {
            $user_id = get_current_user_id();
            $new_keys = EcoGear_API::regenerate_user_keys($user_id);
            if ($new_keys) {
                $keys = $new_keys;
                echo '<div class="eco-success-card eco-animate-bounce-in" style="margin: 20px;">';
                echo '<div class="eco-success-icon">✅</div>';
                echo '<h3>Keys Regenerated!</h3>';
                echo '<p>Your access keys have been successfully regenerated.</p>';
                echo '</div>';
            }
        }        echo '<div class="eco-section eco-api-keys-section">';
        echo '<div class="eco-section-header">';
        echo '<h2><span class="eco-icon">🔑</span> Your App Credentials</h2>';
        echo '<p class="eco-section-subtitle">Generated credentials for WooCommerce access</p>';
        echo '</div>';

        echo '<div class="eco-cards-grid">';
          // Consumer Key Card (displayed as APP ID)
        self::render_key_card(
            'APP ID',
            'consumer-key-icon',
            '🔑',
            $keys['consumer_key'],
            0.1
        );

        // Consumer Secret Card (displayed as APP KEY)
        self::render_key_card(
            'APP KEY',
            'consumer-secret-icon',
            '🔐',
            $keys['consumer_secret'],
            0.2
        );

        // Permissions Card
        self::render_permission_card();

        // Key ID Card
        self::render_key_id_card($keys['key_id']);

        echo '</div>'; // End cards grid

        // Add regenerate button
        echo '<div class="eco-key-actions" style="text-align: center; margin: 30px 0;">';        echo '<form method="post" style="display: inline-block;" onsubmit="return confirm(\'Are you sure you want to regenerate your access keys? This will invalidate the current keys.\')">';
        wp_nonce_field('regenerate_api_keys');
        echo '<button type="submit" name="regenerate_api_keys" class="eco-btn eco-btn-secondary">';
        echo '<span class="eco-btn-icon">🔄</span>';
        echo 'Regenerate Access Keys';
        echo '</button>';
        echo '</form>';
        echo '</div>';

        // Security Notice
        self::render_security_notice();

        echo '</div>'; // End API keys section
    }

    /**
     * Render individual key card
     * 
     * @param string $title Card title
     * @param string $icon_class CSS class for icon
     * @param string $icon_emoji Emoji icon
     * @param string $value Key value
     * @param float $delay Animation delay
     */
    private static function render_key_card($title, $icon_class, $icon_emoji, $value, $delay) {
        echo '<div class="eco-key-card eco-animate-slide-up" style="animation-delay: ' . $delay . 's">';
        echo '<div class="eco-key-header">';
        echo '<div class="eco-key-icon ' . $icon_class . '">' . $icon_emoji . '</div>';
        echo '<h3>' . esc_html($title) . '</h3>';
        echo '</div>';
        echo '<div class="eco-key-content">';
        echo '<code class="eco-key-value" onclick="copyToClipboard(this)">' . esc_html($value) . '</code>';
        echo '<button class="eco-copy-btn" onclick="copyToClipboard(this.previousElementSibling)">📋 Copy</button>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Render permissions card
     */
    private static function render_permission_card() {
        echo '<div class="eco-key-card eco-animate-slide-up" style="animation-delay: 0.3s">';
        echo '<div class="eco-key-header">';
        echo '<div class="eco-key-icon permissions-icon">⚡</div>';
        echo '<h3>Permissions</h3>';
        echo '</div>';
        echo '<div class="eco-key-content">';
        echo '<div class="eco-permission-badge">✓ Read & Write Access</div>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Render key ID card
     * 
     * @param int $key_id Key ID
     */
    private static function render_key_id_card($key_id) {
        echo '<div class="eco-key-card eco-animate-slide-up" style="animation-delay: 0.4s">';
        echo '<div class="eco-key-header">';
        echo '<div class="eco-key-icon key-id-icon">🆔</div>';
        echo '<h3>Key ID</h3>';
        echo '</div>';
        echo '<div class="eco-key-content">';
        echo '<div class="eco-key-id">' . esc_html($key_id) . '</div>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Render security notice
     */
    private static function render_security_notice() {
        echo '<div class="eco-security-notice eco-animate-fade-in" style="animation-delay: 0.5s">';
        echo '<div class="eco-security-icon">🛡️</div>';        echo '<div class="eco-security-content">';
        echo '<h4>Security Reminder</h4>';
        echo '<p>Keep these keys secure and never share them publicly. They provide full access to your WooCommerce store.</p>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Render admin section
     */
    private static function render_admin_section() {
        echo '<div class="eco-section eco-admin-section">';
        echo '<div class="eco-section-header">';
        echo '<h2><span class="eco-icon">⚙️</span> Admin Controls</h2>';
        echo '<p class="eco-section-subtitle">Manage custom order statuses for enhanced workflow</p>';
        echo '</div>';

        echo '<div class="eco-admin-card eco-animate-slide-up" style="animation-delay: 0.6s">';
        echo '<div class="eco-admin-content">';
        echo '<div class="eco-admin-icon">📊</div>';
        echo '<h3>Custom Order Statuses</h3>';
        echo '<p>Register additional order statuses to better track your order workflow and customer journey.</p>';
        
        echo '<form method="post" class="eco-admin-form">';
        echo '<button type="submit" name="register_custom_statuses" class="eco-btn eco-btn-primary">';
        echo '<span class="eco-btn-icon">🚀</span>';
        echo 'Register Custom Statuses';
        echo '</button>';
        echo '</form>';
        echo '</div>';
        echo '</div>';

        // Handle form submission
        if (isset($_POST['register_custom_statuses'])) {
            EcoGear_Order_Status::register_statuses(true);
            self::render_success_message();
        }

        // Display registered statuses
        self::render_status_display();

        echo '</div>'; // End admin section
    }

    /**
     * Render success message
     */
    private static function render_success_message() {
        echo '<div class="eco-success-card eco-animate-bounce-in">';
        echo '<div class="eco-success-icon">✅</div>';
        echo '<h3>Success!</h3>';
        echo '<p>Custom order statuses have been registered successfully.</p>';
        echo '</div>';
    }

    /**
     * Render status display section
     */
    private static function render_status_display() {
        echo '<div class="eco-status-display eco-animate-slide-up" style="animation-delay: 0.7s; margin-top: 30px;">';
        echo '<h3><span class="eco-icon">📋</span> Registered Custom Statuses</h3>';
        echo '<div class="eco-status-grid">';
        
        $custom_statuses = EcoGear_Order_Status::get_custom_statuses();
        
        foreach ($custom_statuses as $key => $status) {
            echo '<div class="eco-status-badge" style="border-left-color: ' . $status['color'] . '">';
            echo '<span class="eco-status-icon">' . $status['icon'] . '</span>';
            echo '<span class="eco-status-label">' . esc_html($status['label']) . '</span>';
            echo '<code class="eco-status-key">' . esc_html($key) . '</code>';
            echo '</div>';
        }
        
        echo '</div>';
        echo '</div>';
    }

    /**
     * Render footer section
     */
    private static function render_footer() {
        echo '<div class="eco-footer">';
        echo '<div class="eco-footer-content">';
        echo '<div class="eco-footer-info">';
        echo '<div class="eco-footer-logo">';
        if (EcoGear_Config::logo_exists()) {
            echo '<img src="' . EcoGear_Config::get_logo_url() . '" alt="EcoGear Logo" class="eco-footer-logo-img" style="height: 24px; width: auto; vertical-align: middle; margin-right: 8px;" />';
        } else {
            echo '<span class="eco-icon" style="font-size: 24px; margin-right: 8px; vertical-align: middle;">🌱</span>';
        }
        echo '<h3 style="display: inline-block; margin: 0; vertical-align: middle;">EcoGear</h3>';
        echo '</div>';
        echo '<p>Sustainable API management for your WooCommerce store</p>';
        echo '</div>';
        echo '<div class="eco-footer-stats">';        echo '<div class="eco-stat-item">';
        echo '<span class="eco-stat-number">2</span>';
        echo '<span class="eco-stat-label">Access Keys</span>';
        echo '</div>';
        echo '<div class="eco-stat-item">';
        echo '<span class="eco-stat-number">' . EcoGear_Order_Status::get_status_count() . '</span>';
        echo '<span class="eco-stat-label">Custom Statuses</span>';
        echo '</div>';
        echo '<div class="eco-stat-item">';
        echo '<span class="eco-stat-number">100%</span>';
        echo '<span class="eco-stat-label">Security</span>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Render dashboard widget content
     */
    public static function render_dashboard_widget() {
        $user_id = get_current_user_id();
        $keys = EcoGear_API::get_user_keys($user_id);
        
        echo '<div style="text-align: center; padding: 20px;">';
        
        // Add logo to dashboard widget
        echo '<div style="margin-bottom: 15px;">';
        if (EcoGear_Config::logo_exists()) {
            echo '<img src="' . EcoGear_Config::get_logo_url() . '" alt="EcoGear Logo" class="eco-dashboard-logo" style="height: 32px; width: auto;" />';
        } else {
            echo '<span class="eco-icon" style="font-size: 32px;">🌱</span>';
        }
        echo '</div>';
          if ($keys) {
            echo '<div style="color: #48bb78; font-size: 3rem; margin-bottom: 15px;">✅</div>';
            echo '<h3 style="color: #2d3748; margin: 0 0 10px 0;">Access Keys Active</h3>';
            echo '<p style="color: #718096; margin: 0;">Your WooCommerce access keys are ready to use</p>';
            echo '<div style="margin-top: 20px;">';
            echo '<a href="' . admin_url('admin.php?page=ecogear') . '" class="button button-primary">View Access Keys</a>';
            echo '</div>';
        } else {
            echo '<div style="color: #ed8936; font-size: 3rem; margin-bottom: 15px;">⚠️</div>';
            echo '<h3 style="color: #2d3748; margin: 0 0 10px 0;">No Access Keys</h3>';
            echo '<p style="color: #718096; margin: 0;">Generate your access keys to get started</p>';
            echo '<div style="margin-top: 20px;">';
            echo '<a href="' . admin_url('admin.php?page=ecogear') . '" class="button button-primary">Generate Keys</a>';
            echo '</div>';
        }
        
        echo '</div>';
    }    /**
     * Enqueue UI assets (CSS and JavaScript)
     */
    private static function enqueue_assets() {
        // Include CSS
        $css_file = plugin_dir_path(__FILE__) . '../assets/css/ecogear-styles.css';
        if (file_exists($css_file)) {
            echo '<link rel="stylesheet" href="' . plugin_dir_url(__FILE__) . '../assets/css/ecogear-styles.css">';
        }
        
        // Include JavaScript
        $js_file = plugin_dir_path(__FILE__) . '../assets/js/ecogear-scripts.js';
        if (file_exists($js_file)) {
            echo '<script src="' . plugin_dir_url(__FILE__) . '../assets/js/ecogear-scripts.js"></script>';
        }
        
        // Fallback: Include inline styles and scripts if files don't exist
        if (!file_exists($css_file)) {
            self::inline_styles();
        }
        
        if (!file_exists($js_file)) {
            self::inline_scripts();
        }
    }
    
    /**
     * Inline CSS styles as fallback
     */
    private static function inline_styles() {
        echo '<style>' . file_get_contents(plugin_dir_path(__FILE__) . '../assets/css/ecogear-styles.css') . '</style>';
    }
    
    /**
     * Inline JavaScript as fallback
     */
    private static function inline_scripts() {
        echo '<script>' . file_get_contents(plugin_dir_path(__FILE__) . '../assets/js/ecogear-scripts.js') . '</script>';
    }
    
    /**
     * Render logo element
     * 
     * @param string $size Size of the logo (small, medium, large)
     * @param string $class Additional CSS classes
     * @param bool $with_text Whether to include text next to logo
     */
    private static function render_logo($size = 'medium', $class = '', $with_text = false) {
        $sizes = [
            'small' => '20px',
            'medium' => '32px',
            'large' => '64px'
        ];
        
        $height = isset($sizes[$size]) ? $sizes[$size] : $sizes['medium'];
        $logo_class = 'eco-logo eco-logo-' . $size . ' ' . $class;
        
        echo '<div class="eco-logo-container">';
        echo '<img src="' . EcoGear_Config::get_logo_url() . '" alt="EcoGear Logo" class="' . $logo_class . '" style="height: ' . $height . '; width: auto;" />';
        
        if ($with_text) {
            echo '<span class="eco-logo-text">EcoGear</span>';
        }
        
        echo '</div>';
    }
}
