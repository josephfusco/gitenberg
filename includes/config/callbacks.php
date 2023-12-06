<?php
/**
 * Functions for plugin configuration settings.
 * 
 * @package Gitenberg\Config
 */

namespace Gitenberg\Config;

/**
 * Register post meta for linking to GitHub markdown files.
 *
 * This function registers a custom post meta field to store the GitHub markdown file associated with a post.
 */
function register_meta() {
    $post_type = 'post';
    $meta_key  = 'gitenberg_linked_markdown_file';
    $args      = [
        'type'         => 'string',
        'description'  => __( 'The markdown file from the selected GitHub repository that this post should load its content from.', 'gitenberg' ),
        'show_in_rest' => true,
        'single'       => true,
    ];

    register_post_meta( $post_type, $meta_key, $args );
}
add_action( 'init', __NAMESPACE__ . '\\register_meta', 10 );
