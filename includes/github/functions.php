<?php
/**
 * Functions for GitHub API interactions.
 * 
 * @package Gitenberg\GitHub
 */

namespace Gitenberg\GitHub;

use function Gitenberg\Config\get_plugin_config;
use WP_Error;

/**
 * Whether the post content should be loaded from Github or not
 * 
 * @param string $content
 * @param WP_Post|null $post 
 * @return bool
 */
function should_load_from_github( $content, $post ) {
    $should = apply_filters( 'gitenberg/should_load_content_from_github', false, $content, $post );
    return true;
}

/**
 * Fetch a single markdown file from a GitHub repository.
 *
 * @param string $repo The GitHub repository in the format 'owner/repo'.
 * @param string $path The path of the markdown file in the repository.
 * @param string $branch The branch from which to fetch the file (default 'main').
 * @return array|WP_Error Markdown file data or WP_Error on failure.
 */
function fetch_single_markdown_file( $repo, $path, $branch = 'main' ) {
    $config = get_plugin_config();

    if ( is_wp_error( $config ) ) {
        return $config; // Return WP_Error
    }

    $api_url = 'https://api.github.com/repos/' . $repo . '/contents/' . $path . '?ref=' . $branch;

    $args = array(
        'headers' => array(
            'Authorization' => 'token ' . $config['token'],
            'User-Agent'    => 'WordPress/' . $GLOBALS['wp_version'] . '; ' . home_url(),
            'Accept'        => 'application/vnd.github.v3+json',
        ),
    );

    $response = wp_remote_get( $api_url, $args );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $body = wp_remote_retrieve_body( $response );
    $file = json_decode( $body );

    if ( ! isset( $file->sha ) ) {
        return new WP_Error( 'github_api_error', 'Invalid or missing sha returned from GitHub API' );
    }

    if ( ! isset( $file->name ) || pathinfo( $file->name, PATHINFO_EXTENSION ) !== 'md' ) {
        return new WP_Error( 'github_api_error', 'Invalid or non-markdown file received from GitHub API' );
    }

    if ( ! isset( $file->content ) ) {
        return new WP_Error( 'github_api_error', 'No content found for markdown file received from GitHub API' );
    }

    return $file;
}

/**
 * Retrieves and lists markdown files from a specified GitHub repository, showing only name and path.
 *
 * @return array|WP_Error List of markdown files with name and path, or WP_Error on failure.
 */
function list_remote_markdown_files() {
    $config = get_plugin_config();

    if ( is_wp_error( $config ) ) {
        return $config; // Return WP_Error
    }

    $headers = array(
        'Authorization' => 'token ' . $config['token'],
        'Accept'        => 'application/vnd.github.v3+json',
    );

    $api_url  = 'https://api.github.com/repos/' . $config['repo'] . '/contents/' . $config['remote_dir'];
    $response = wp_remote_get( $api_url, array( 'headers' => $headers ) );

    if ( is_wp_error( $response ) ) {
        return new WP_Error( $response->get_error_message() );
    }

    // Check for a 404 response
    $response_code = wp_remote_retrieve_response_code( $response );
    if ( $response_code == 404 ) {
        return new WP_Error( 'github_api_error', 'GitHub API returned a 404 Not Found response.' );
    }

    $files = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( empty( $files ) ) {
        return [];
    }

    // Extract only the 'name' and 'path' from each file
    return array_map( function ( $file ) {
        return [
            'name' => $file['name'],
            'path' => $file['path']
        ];
    }, $files );
}
