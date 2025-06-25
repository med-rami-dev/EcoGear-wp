<?php
/**
 * Simple API Key Test
 * Quick test to see if keys are being generated and displayed
 */

// This should be added to your main plugin file temporarily for testing
function ecogear_test_keys() {
    if (!current_user_can('administrator')) return;
    
    echo '<div style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccc; width: 100%; box-sizing: border-box;">';
    echo '<h3>EcoGear Key Generation Test</h3>';
    
    $user_id = get_current_user_id();
    echo '<p><strong>User ID:</strong> ' . $user_id . '</p>';
    
    // Test direct key generation
    $keys = EcoGear_API::generate_keys_for_user($user_id);
    
    if (is_wp_error($keys)) {
        echo '<p style="color: red;">❌ Error: ' . $keys->get_error_message() . '</p>';
    } else {
        echo '<p style="color: green;">✅ Keys generated!</p>';
        echo '<p><strong>APP ID (consumer_key):</strong> ' . esc_html($keys['consumer_key']) . '</p>';
        echo '<p><strong>APP KEY (consumer_secret):</strong> ' . esc_html($keys['consumer_secret']) . '</p>';
        echo '<p><strong>Key ID:</strong> ' . esc_html($keys['key_id']) . '</p>';
        
        // Test storage
        update_user_meta($user_id, EcoGear_Config::API_KEY_META_KEY, $keys);
        $stored = get_user_meta($user_id, EcoGear_Config::API_KEY_META_KEY, true);
        
        if ($stored && isset($stored['consumer_key'])) {
            echo '<p style="color: green;">✅ Keys stored successfully in user meta</p>';
        } else {
            echo '<p style="color: red;">❌ Failed to store keys in user meta</p>';
        }
    }
    
    echo '</div>';
}

// Add this to wp-admin for testing
add_action('admin_notices', 'ecogear_test_keys');
?>
