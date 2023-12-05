<?php
/**
 * Plugin Name: Gitenberg
 * Description: Edit Github Markdown files using WordPress and the Gutenberg block-based editor.
 * Version:     1.0-beta.0.0.1
 */

namespace Gitenberg;

use WP_Error;

/**
 * Retrieves GitHub repository and access token, either from constants or WordPress options.
 *
 * @return array|WP_Error Contains repository URL and access token.
 */
function get_gitenberg_config() {
    $repo       = defined( 'GITENBERG_GITHUB_REPO' ) ? GITENBERG_GITHUB_REPO : get_option( 'gitenberg_github_repo', '' );
    $token      = defined( 'GITENBERG_GITHUB_PERSONAL_ACCESS_TOKEN' ) ? GITENBERG_GITHUB_PERSONAL_ACCESS_TOKEN : get_option( 'gitenberg_github_personal_access_token', '' );
    $remote_dir = defined( 'GITENBERG_GITHUB_REMOTE_DIR' ) ? GITENBERG_GITHUB_REMOTE_DIR : get_option( 'gitenberg_github_remote_dir', 'docs' );

    if ( empty( $repo ) || empty( $token ) ) {
        return new WP_Error( 'The GITENBERG_GITHUB_REPO and GITENBERG_GITHUB_PERSONAL_ACCESS_TOKEN are required for the Gitenberg plugin to function properly. Please define them in your wp-config.php file or set them in the WordPress options.' );
    }

    return array(
        'repo'       => $repo,
        'token'      => $token,
        'remote_dir' => $remote_dir
    );
}

/**
 * Checks if necessary GitHub constants or options are set and displays an admin notice if not.
 */
function maybe_display_admin_notice() {
    $config = get_gitenberg_config();

    if ( is_wp_error( $config ) ) {
        add_action( 'admin_notices', __NAMESPACE__ . '\\display_admin_notice' );
    }
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\\maybe_display_admin_notice' );

/**
 * Displays an admin notice about missing GitHub credentials.
 */
function display_admin_notice() {
    ?>
    <div class="notice notice-error">
        <p>
            The <code>GITENBERG_GITHUB_REPO</code> and <code>GITENBERG_GITHUB_PERSONAL_ACCESS_TOKEN</code> are required for the <strong>Gitenberg</strong> plugin to function properly. Please define them in your wp-config.php file or set them in the WordPress options.
        </p>
    </div>
    <?php
}

/**
 * Fetch a single markdown file from a GitHub repository.
 *
 * @param string $repo The GitHub repository in the format 'owner/repo'.
 * @param string $path The path of the markdown file in the repository.
 * @param string $branch The branch from which to fetch the file (default 'main').
 * @return array|WP_Error Markdown file data or WP_Error on failure.
 */
function fetch_single_github_markdown_file( $repo, $path, $branch = 'main' ) {
    $config = get_gitenberg_config();

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

    $file = wp_remote_retrieve_body( $response );    
    $file = json_decode( $file );

    if ( ! isset( $file->name ) || pathinfo( $file->name, PATHINFO_EXTENSION ) !== 'md' ) {
        return new WP_Error( 'github_api_error', 'Invalid or non-markdown file received from GitHub API' );
    }

    if ( ! isset( $file->content ) ) {
        return new WP_Error( 'github_api_error', 'No content found for markdown file received from GitHub API' );
    }

    $remote_content = base64_decode( $file->content );

    return $remote_content;
}

/**
 * Retrieves and lists markdown files from a specified GitHub repository, showing only name and path.
 *
 * @return array|WP_Error List of markdown files with name and path, or WP_Error on failure.
 */
function list_markdown_files() {
    $config = get_gitenberg_config();

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

function list_markdown_files_rest_handler( $request ) {
    return list_markdown_files();
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
    $config = get_gitenberg_config();

    if ( is_wp_error( $config ) ) {
        return $response; // Return the unmodified response.
    }

    $linked_file = get_linked_markdown_file( $post->ID );
    
    if ( ! $linked_file ) {
        return $response; // Return the unmodified response.
    }

    if ( should_load_from_github( $response->data['content'], $post ) ) {
        // TODO: load markdown file path from post meta (docs/some-tech-docs.md)
        $remote_content = fetch_single_github_markdown_file( $config['repo'], 'docs/some-tech-docs.md' );

        if ( is_wp_error( $remote_content ) ) {
            return $response;  // Return the unmodified response.
        }
        
        $response->data['content']['raw'] = $remote_content;
    }

    return $response;
}
add_filter( 'rest_prepare_post', __NAMESPACE__ . '\\modify_rest_api_response', 10, 3 );

/**
 * Registers the plugin's REST routes.
 */
function register_rest_routes() {
    register_rest_route(
        'gitenberg/v1',
        '/list-markdown-files',
        array(
            'methods'  => 'GET',
            'callback' => __NAMESPACE__ . '\\list_markdown_files_rest_handler',
        )
    );
}
add_action( 'rest_api_init', __NAMESPACE__ . '\\register_rest_routes' );

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
 * Handles custom operations when a post is saved or updated.
 *
 * This function is hooked into the 'save_post' action and is triggered
 * whenever a post or a page is created or updated. It can be used to
 * perform additional operations like saving custom meta data, performing
 * checks, or triggering other actions based on the post update.
 *
 * @param int      $post_id The ID of the post being saved.
 * @param WP_Post  $post    The post object.
 * @param bool     $update  Whether this is an existing post being updated or not.
 */
function save_post_content( $post_id, $post, $update ) {

}
add_filter( 'save_post', __NAMESPACE__ . '\\save_post_content', 10, 3 );

/**
 * Enqueue JavaScript for Gutenberg sidebar customization.
 */
function enqueue_gitenberg_scripts() {
    $config = get_gitenberg_config();

    if ( is_wp_error( $config ) ) {
        return null;
    }

    $current_screen = get_current_screen();
    global $post;

    $markdown_files = list_markdown_files();

    if ( is_wp_error( $markdown_files ) ) {
        $markdown_files = [];
    }

    if ( ! ( function_exists( 'use_block_editor_for_post_type' ) &&
             use_block_editor_for_post_type( $current_screen->post_type ) ) ) {
        return;
    }

    wp_enqueue_script(
        'gitenberg-sidebar-script',
        plugins_url( 'js/gitenberg-sidebar.js', __FILE__ ), // Adjust the path as needed
        array( 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n' ),
        filemtime( plugin_dir_path( __FILE__ ) . 'js/gitenberg-sidebar.js' )
    );


    wp_localize_script(
        'gitenberg-sidebar-script',
        'gitenbergData',
        array(
            'repo'               => $config['repo'],
            'markdownFiles'      => $markdown_files,
            'linkedMarkdownFile' => get_linked_markdown_file( $post->ID )
        )
    );
}
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\enqueue_gitenberg_scripts' );

/**
 * Retrieves the 'gitenberg_linked_markdown_file' post meta for a given post ID.
 *
 * @param int $post_id The ID of the post.
 * @return mixed The value of the post meta, or null if not found or invalid post ID.
 */
function get_linked_markdown_file( $post_id ) {
    if ( ! get_post_status( $post_id ) ) {
        // Post does not exist
        return null;
    }

    $post_meta = get_post_meta( $post_id, 'gitenberg_linked_markdown_file', true );

    if ( empty( $post_meta ) ) {
        // Post meta is empty or does not exist
        return null;
    }

    return $post_meta;
}

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