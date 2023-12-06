<?php
/**
 * Functions for admin-specific functionalities.
 * 
 * @package Gitenberg\Admin
 */

namespace Gitenberg\Admin;

/**
 * HTML template for admin notice about missing GitHub credentials.
 */
function admin_notice_html() {
    return <<<HTML
        <div class="notice notice-error">
            <p>
                The <code>GITENBERG_GITHUB_REPO</code> and <code>GITENBERG_GITHUB_PERSONAL_ACCESS_TOKEN</code> are required for the <strong>Gitenberg</strong> plugin to function properly. Please define them in your wp-config.php file or set them in the WordPress options.
            </p>
        </div>
        HTML;
}

/**
 * Callback function to display the HTML content of the settings page.
 */
function settings_page_html() {
    return <<<HTML
        <div id="gitenberg-settings"></div>
        HTML;
}

/**
 * Displays an admin notice about missing GitHub credentials.
 */
function display_admin_notice() {
    echo admin_notice_html();
}


/**
 * Displays the settings page.
 */
function display_settings_page() {
    echo settings_page_html();
}

/**
 * Retrieves the 'gitenberg_linked_markdown_file' post meta for a given post ID.
 *
 * @param int $post_id The ID of the post.
 * @return mixed The value of the post meta, or null if not found or invalid post ID.
 */
function get_linked_markdown_file( $post_id ) {
    if ( ! get_post_status( $post_id ) ) {
        return null; // Post does not exist
    }

    return get_post_meta( $post_id, 'gitenberg_linked_markdown_file', true ) ?: null;
}

/**
 * Get the SHA (Secure Hash Algorithm) of the linked GitHub markdown file for a post.
 *
 * @param int $post_id The ID of the post.
 * @return string|null The SHA of the linked markdown file, or null if not found or invalid post ID.
 */
function get_linked_markdown_file_sha( $post_id ) {
    if ( ! get_post_status( $post_id ) ) {
        return null; // Post does not exist
    }

    return get_post_meta( $post_id, 'gitenberg_linked_markdown_file_sha', true ) ?: null;
}
