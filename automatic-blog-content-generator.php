<?php

/**
 * Plugin Name: Automatic Blog Content Generator
 * Description: A plugin to generate blog content automatically based on post title.
 * Version: 1.0
 * Author: Dev.Tanisha
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: automatic-blog-content-generator
 */

// Prevent direct access to the file
if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include settings page file
require_once plugin_dir_path(__FILE__) . 'includes/settings-page.php';

// Create Custom Post Type (CPT)
function create_blog_post_type()
{
    $args = array(
        'label'               => 'AutoBlogCreation',
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'supports'            => array('title', 'editor', 'author', 'thumbnail', 'comments'),
        'has_archive'         => true,
        'rewrite'             => array('slug' => 'blog'),
    );
    register_post_type('create_blog', $args);
}
add_action('init', 'create_blog_post_type');

// Add Meta Box for Generate Content Button
function abcg_add_generate_button_metabox()
{
    add_meta_box(
        'abcg_generate_content_box',
        'Automatic Blog Content',
        'abcg_generate_content_metabox_callback',
        'create_blog', // Post type slug
        'side'
    );
}
add_action('add_meta_boxes', 'abcg_add_generate_button_metabox');

// Modify this to add the settings page under "Create Blog"
function abcg_add_settings_page()
{
    add_submenu_page(
        'edit.php?post_type=create_blog',  // This links to your CPT menu
        'Settings',       // Page title
        'Settings',                // Menu title
        'manage_options',                  // Capability
        'abcg-settings',                   // Menu slug
        'abcg_render_settings_page'        // Callback function
    );
}
add_action('admin_menu', 'abcg_add_settings_page');



// Meta Box HTML
function abcg_generate_content_metabox_callback($post)
{
?>
    <button type="button" class="button button-primary" id="abcg-generate-content">Generate Content</button>
    <p id="abcg-status" style="margin-top:10px;"></p>
<?php
}

// Enqueue admin JS for AJAX
function abcg_enqueue_admin_scripts($hook)
{
    global $post;

    // Only load on "create_blog" post edit screen
    if ($hook == 'post.php' || $hook == 'post-new.php') {
        if (get_post_type($post) == 'create_blog') {
            wp_enqueue_script(
                'abcg-admin-js',
                plugin_dir_url(__FILE__) . 'assets/js/admin.js',
                array('jquery'),
                '1.0',
                true
            );

            // Pass AJAX URL and nonce
            wp_localize_script('abcg-admin-js', 'abcg_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('abcg_nonce'),
            ));
        }
    }
}
add_action('admin_enqueue_scripts', 'abcg_enqueue_admin_scripts');

add_action('admin_enqueue_scripts', 'abcg_enqueue_admin_styles');

function abcg_enqueue_admin_styles($hook) {
    // Prevent loading during AJAX requests
    if (defined('DOING_AJAX') && DOING_AJAX) {
        return;
    }

    wp_enqueue_style(
        'abcg-admin-style',
        plugin_dir_url(__FILE__) . 'assets/css/admin.css',
        array(),
        '1.0.0'
    );
}

require_once plugin_dir_path(__FILE__) . 'includes/ajax-handler.php';
?>