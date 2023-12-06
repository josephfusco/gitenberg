<?php
/**
 * Functions for plugin configuration settings.
 * 
 * @package Gitenberg\Config
 */

namespace Gitenberg\Config;

/**
 * Retrieves GitHub repository and access token, either from constants or WordPress options.
 *
 * @return array|WP_Error Contains repository URL and access token.
 */
function get_plugin_config() {
    $repo       = defined( 'GITENBERG_GITHUB_REPO' ) ? GITENBERG_GITHUB_REPO : get_option( 'gitenberg_github_repo', '' );
    $token      = defined( 'GITENBERG_GITHUB_PERSONAL_ACCESS_TOKEN' ) ? GITENBERG_GITHUB_PERSONAL_ACCESS_TOKEN : get_option( 'gitenberg_github_personal_access_token', '' );
    $remote_dir = defined( 'GITENBERG_GITHUB_REMOTE_DIR' ) ? GITENBERG_GITHUB_REMOTE_DIR : get_option( 'gitenberg_github_remote_dir', 'docs' );

    if ( empty( $repo ) || empty( $token ) ) {
        return new WP_Error(
            __( 'The GITENBERG_GITHUB_REPO and GITENBERG_GITHUB_PERSONAL_ACCESS_TOKEN are required for the Gitenberg plugin to function properly. Please define them in your wp-config.php file or set them in the WordPress options.', 'gitenberg' )
        );
    }

    return array(
        'repo'       => $repo,
        'token'      => $token,
        'remote_dir' => $remote_dir
    );
}
