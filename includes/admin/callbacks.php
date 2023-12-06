<?php
/**
 * Admin area callback functions.
 * 
 * @package Gitenberg\Admin
 */

namespace Gitenberg\Admin;

use function Gitenberg\Config\get_plugin_config;
use function Gitenberg\Admin\get_linked_markdown_file;
use function Gitenberg\Admin\get_linked_markdown_file_sha;

/**
 * Checks if necessary GitHub constants or options are set and displays an admin notice if not.
 */
function maybe_display_admin_notice() {
    $config = get_plugin_config();

    if ( is_wp_error( $config ) ) {
        add_action( 'admin_notices', __NAMESPACE__ . '\\display_admin_notice' );
    }
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\\maybe_display_admin_notice' );

/**
 * Save post content to a GitHub markdown file.
 *
 * This function handles the process of updating a GitHub markdown file when a post is published or updated.
 *
 * @param string $new_status The new post status.
 * @param string $old_status The old post status.
 * @param WP_Post $post The post object being saved.
 */
function save_post_content( $new_status, $old_status, $post ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
        return;
    if ( !current_user_can( 'edit_post', $post->ID ) )
        return;
    if ( wp_is_post_revision( $post->ID ) )
        return;

    $initial_post_statuses = [ 'auto-draft', 'inherit', 'new' ];

    // If the post is a fresh post that hasn't been made public, don't track the action
    if ( in_array( $new_status, $initial_post_statuses, true ) ) {
        return;
    }

    // Updating a draft should not log actions
    if ( 'draft' === $new_status && 'draft' === $old_status ) {
        return;
    }

    // If the post isn't coming from a "publish" state or going to a "publish" state
    // we can ignore the action.
    if ( 'publish' !== $old_status && 'publish' !== $new_status ) {
        return;
    }
    
    $config = get_plugin_config();
    if ( is_wp_error( $config ) ) {
        return;
    }

    $content = get_post_field( 'post_content', $post->ID );
    $filename = get_linked_markdown_file( $post->ID );

    if ( '' === $filename ) {
        return; // "None"
    }

    // TODO: if filename === create-new, we should do a POST request instead of a PUT, and generate a slug for the markdown filename.

    $github_api_url = 'https://api.github.com/repos/' . $config['repo'] . '/contents/' . $filename;

    $data = array(
        'message' => 'Update from WordPress',
        'content' => base64_encode( $content ),
        'branch'  => 'main',
        'sha'     => get_linked_markdown_file_sha( $post->ID ),
    );

    $headers = array(
        'Authorization' => 'token ' . $config['token'],
        'Content-Type'  => 'application/json',
    );

    $response = wp_remote_post( $github_api_url, array(
        'method'    => 'PUT',
        'headers'   => $headers,
        'body'      => json_encode( $data ),
        'timeout'   => 15
    ) );

    if ( is_wp_error( $response ) ) {
        // Handle error.
    } else {
        // Handle success.
    }
}
add_action( 'transition_post_status', __NAMESPACE__ . '\\save_post_content', 10, 3 );
