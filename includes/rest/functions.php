<?php
/**
 * Functions for the WP REST API.
 * 
 * @package Gitenberg\API
 */

namespace Gitenberg\REST;

/**
 * REST API callback to list markdown files.
 *
 * @param WP_REST_Request $request The REST request object.
 * @return array|WP_Error List of markdown files with name and path, or WP_Error on failure.
 */
function list_markdown_files_rest_handler( $request ) {
    return list_remote_markdown_files();
}

function get_gitenberg_settings( WP_REST_Request $request ) {
    $config = get_plugin_config();
    if ( is_wp_error( $config ) ) {
        return $config;
    }
    return new WP_REST_Response( $config, 200 );
}

function update_gitenberg_settings( WP_REST_Request $request ) {
    $repo       = sanitize_text_field( $request->get_param( 'repo' ) );
    $token      = sanitize_text_field( $request->get_param( 'token' ) );
    $remote_dir = sanitize_text_field( $request->get_param( 'remote_dir' ) );

    if ( empty( $repo ) || empty( $token ) ) {
        return new WP_Error( 'missing_data', 'Repository and token are required.' );
    }

    update_option( 'gitenberg_github_repo', $repo );
    update_option( 'gitenberg_github_personal_access_token', $token );
    update_option( 'gitenberg_github_remote_dir', $remote_dir );

    return new WP_REST_Response( ['message' => 'Settings updated successfully'], 200 );
}

