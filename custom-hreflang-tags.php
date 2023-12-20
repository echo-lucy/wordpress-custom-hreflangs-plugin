<?php
/*
Plugin Name: Custom Page Hreflang
Description: Allows setting hreflang tags for individual pages and posts.
Version: 1.0
Author: Lucy King
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


// Add meta box
add_action('add_meta_boxes', 'hreflang_add_meta_box');
function hreflang_add_meta_box() {
    add_meta_box('hreflang_meta', 'Hreflang Settings', 'hreflang_meta_box_callback', 'page', 'normal', 'high');
    add_meta_box('hreflang_meta', 'Hreflang Settings', 'hreflang_meta_box_callback', 'post', 'normal', 'high');
}


function hreflang_meta_box_callback($post) {
    wp_nonce_field('hreflang_meta_box', 'hreflang_meta_box_nonce');
    $hreflangs = get_post_meta($post->ID, '_hreflang_meta', true);

    // Inline CSS
    echo '<style>
            .hreflang-table td {
                width: 50%;
            }
            .hreflang-table input[type="text"] {
                width: 100%;
                box-sizing: border-box;
            }
          </style>';

    // Table with input fields
    echo '<table class="hreflang-table" style="width: 100%;">';
    if (!empty($hreflangs)) {
        foreach ($hreflangs as $hreflang) {
            echo '<tr><td><input type="text" name="hreflang_country[]" value="' . esc_attr($hreflang['country']) . '" placeholder="Country Code"></td>';
            echo '<td><input type="text" name="hreflang_url[]" value="' . esc_attr($hreflang['url']) . '" placeholder="URL"></td></tr>';
        }
    }
    echo '</table>';

    // Button to add more fields
    echo '<button type="button" id="addHreflangField" style="margin-top: 10px;background: #2271b1;border: 1px solid #2271b1;color: #fff;padding-left:12px;padding-right:12px;padding-top:7px;padding-bottom:7px;border-radius:5px;">Add hreflang tag</button>';
}


// Enqueue JavaScript for the admin
add_action('admin_enqueue_scripts', 'hreflang_enqueue_admin_js');
function hreflang_enqueue_admin_js($hook) {
    if ('post.php' != $hook && 'post-new.php' != $hook) {
        return;
    }
    wp_enqueue_script('hreflang-admin-js', plugin_dir_url(__FILE__) . 'hreflang-admin.js', array('jquery'), null, true);
}

// Save meta box data
add_action('save_post', 'hreflang_save_meta_box_data');
function hreflang_save_meta_box_data($post_id) {
    if (!isset($_POST['hreflang_meta_box_nonce']) || !wp_verify_nonce($_POST['hreflang_meta_box_nonce'], 'hreflang_meta_box')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['hreflang_country']) && isset($_POST['hreflang_url'])) {
        $hreflangs = [];
        foreach ($_POST['hreflang_country'] as $key => $country) {
            if (!empty($country) && !empty($_POST['hreflang_url'][$key])) {
                $hreflangs[] = [
                    'country' => sanitize_text_field($country),
                    'url' => esc_url_raw($_POST['hreflang_url'][$key])
                ];
            }
        }
        update_post_meta($post_id, '_hreflang_meta', $hreflangs);
    }
}

// Output hreflang tags in head 
add_action('wp_head', 'add_custom_hreflang_tags', 1);
function add_custom_hreflang_tags() {
    if (is_singular()) {
        $hreflangs = get_post_meta(get_the_ID(), '_hreflang_meta', true);
        if (!empty($hreflangs)) {
            foreach ($hreflangs as $hreflang) {
                echo '<link rel="alternate" href="' . esc_url($hreflang['url']) . '" hreflang="' . esc_attr($hreflang['country']) . '" />' . "\n";
            }
        }
    }
}





