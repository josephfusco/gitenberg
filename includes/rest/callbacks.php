<?php
/**
 * Callback functions for REST API.
 * 
 * @package Gitenberg\API
 */

namespace Gitenberg\REST;

use function Gitenberg\Config\get_plugin_config;
use function Gitenberg\Admin\get_linked_markdown_file;
use function Gitenberg\GitHub\should_load_from_github;
use function Gitenberg\GitHub\fetch_single_markdown_file;
use function Gitenberg\GitHub\list_remote_markdown_files;

/**
 * Registers the plugin's REST routes.
 */
function register_rest_routes() {
    register_rest_route(
        'gitenberg/v1',
        '/list-markdown-files',
        array(
            'methods'             => 'GET',
            'callback'            => __NAMESPACE__ . '\\list_markdown_files_rest_handler',
            'permission_callback' => '__return_true', // TODO: Better permissions.
        )
    );
}
add_action( 'rest_api_init', __NAMESPACE__ . '\\register_rest_routes' );

/**
 * REST API callback to list markdown files.
 *
 * @param WP_REST_Request $request The REST request object.
 * @return array|WP_Error List of markdown files with name and path, or WP_Error on failure.
 */
function list_markdown_files_rest_handler( $request ) {
    return list_remote_markdown_files();
}

/**
 * Modifies the REST API response for posts to load the GitHub markdown.
 *
 * Checks if the post content should be loaded from GitHub. If so,
 * it fetches the content from a specified GitHub markdown file and
 * updates the post content in the REST API response.
 *
 * @param WP_REST_Response $response The response object.
 * @param WP_Post          $post     The post object being returned.
 * @param WP_REST_Request  $request  The request object.
 *
 * @return WP_REST_Response The modified response object.
 */
function modify_rest_api_response( $response, $post, $request ) {
    $config = get_plugin_config();

    if ( is_wp_error( $config ) ) {
        return $response; // Return the unmodified response.
    }

    $linked_file = get_linked_markdown_file( $post->ID );

    if ( ! $linked_file ) {
        return $response; // Return the unmodified response.
    }

    if ( should_load_from_github( $response->data['content'], $post ) ) {
        $remote_file = fetch_single_markdown_file( $config['repo'], $linked_file );

        if ( is_wp_error( $remote_file ) ) {
            return $response;  // Return the unmodified response.
        }
        
        $remote_content = base64_decode( $remote_file->content );
        $response->data['content']['raw'] = $remote_content;

        update_post_meta( $post->ID, 'gitenberg_linked_markdown_file_sha', $remote_file->sha );
    }

    return $response;
}
add_filter( 'rest_prepare_post', __NAMESPACE__ . '\\modify_rest_api_response', 10, 3 );
