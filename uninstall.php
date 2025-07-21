<?php
// If uninstall not called from WordPress, exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Clean up custom post type data (optional)
$post_type = 'create_blog';
$posts = get_posts(array('post_type' => $post_type, 'numberposts' => -1));

foreach ($posts as $post) {
    wp_delete_post($post->ID, true);
}
