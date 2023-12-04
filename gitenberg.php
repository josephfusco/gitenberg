<?php
/**
 * Plugin Name: Gitenberg
 * Description: Edit Github Markdown files using WordPress and the Gutenberg block-based editor.
 * Version: 1.0-beta.0.0.1
 */

namespace Gitenberg;

/**
 * Retrieves GitHub repository and access token, either from constants or WordPress options.
 *
 * @return array Contains repository URL and access token.
 */
function get_github_credentials() {
    $repo = defined( 'GITHUB_REPO' ) ? GITHUB_REPO : get_option( 'gitenberg_github_repo', '' );
    $token = defined( 'GITHUB_ACCESS_TOKEN' ) ? GITHUB_ACCESS_TOKEN : get_option( 'gitenberg_github_access_token', '' );

    return array(
        'repo'  => $repo,
        'token' => $token
    );
}

/**
 * Checks if necessary GitHub constants or options are set and displays an admin notice if not.
 */
function check_github_credentials() {
    $credentials = get_github_credentials();

    if ( empty( $credentials['repo'] ) || empty( $credentials['token'] ) ) {
        add_action( 'admin_notices', 'github_credentials_admin_notice' );
    }
}

/**
 * Displays an admin notice about missing GitHub credentials.
 */
function github_credentials_admin_notice() {
    ?>
    <div class="notice notice-error">
        <p>
            The <code>GITHUB_REPO</code> and <code>GITHUB_ACCESS_TOKEN</code> are required for the <strong>Gitenberg</strong> plugin to function properly. Please define them in your wp-config.php file or set them in the WordPress options.
        </p>
    </div>
    <?php
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\\check_github_credentials' );

/**
 * Fetch a single markdown file from a GitHub repository.
 *
 * @param string $repo The GitHub repository in the format 'owner/repo'.
 * @param string $path The path of the markdown file in the repository.
 * @param string $branch The branch from which to fetch the file (default 'main').
 * @return array|WP_Error Markdown file data or WP_Error on failure.
 */
function fetch_single_github_markdown_file( $repo, $path, $branch = 'main' ) {
    $credentials = get_github_credentials();

    $api_url = 'https://api.github.com/repos/' . $repo . '/contents/' . $path . '?ref=' . $branch;

    $args = array(
        'headers' => array(
            'Authorization' => 'token ' . $credentials['token'],
            'User-Agent'    => 'WordPress/' . $GLOBALS['wp_version'] . '; ' . home_url(),
            'Accept'        => 'application/vnd.github.v3+json',
        ),
    );

    $response = wp_remote_get( $api_url, $args );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $file    = wp_remote_retrieve_body( $response );
    $headers = wp_remote_retrieve_headers( $response );
    
    $file = json_decode( $file );

    if ( ! isset( $file->name ) || pathinfo( $file->name, PATHINFO_EXTENSION ) !== 'md' ) {
        return new WP_Error( 'github_api_error', 'Invalid or non-markdown file received from GitHub API' );
    }

    if ( ! isset( $file->content ) ) {
        return new WP_Error( 'github_api_error', 'No content found for markdown file received from GitHub API' );
    }

    $remote_content = base64_decode( $file->content );

    return parse_blocks( $remote_content );
}

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

function register_scripts() {
    wp_register_script(
        'gitenberg-editor',
        plugin_dir_url( __FILE__ ) . 'js/gitenberg-editor.js',
        array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-data', 'wp-dom-ready' ),
        uniqid(),
        true
    );

}
add_action( 'init', __NAMESPACE__ . '\\register_scripts', 10 );

/**
 * Enqueues a script for the WordPress block editor.
 */
function enqueue_block_editor_assets() {
    $remote_content = fetch_single_github_markdown_file( 'josephfusco/gitenberg', 'docs/some-tech-docs.md' );

    wp_enqueue_script( 'gitenberg-editor' );
    wp_localize_script( 'gitenberg-editor', 'gitenbergData', array(
        'remoteContent' => $remote_content,
    ));
}
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\enqueue_block_editor_assets', 10 );
