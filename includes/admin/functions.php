<?php
/**
 * Functions for admin-specific functionalities.
 * 
 * @package Gitenberg\Admin
 */

namespace Gitenberg\Admin;

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

/**
 * Get the SHA (Secure Hash Algorithm) of the linked GitHub markdown file for a post.
 *
 * This function retrieves the SHA (Secure Hash Algorithm) of the GitHub markdown file associated with a post.
 *
 * @param int $post_id The ID of the post.
 *
 * @return string|null The SHA of the linked markdown file, or null if not found or invalid post ID.
 */
function get_linked_markdown_file_sha( $post_id ) {
    if ( ! get_post_status( $post_id ) ) {
        // Post does not exist
        return null;
    }

    $sha = get_post_meta( $post_id, 'gitenberg_linked_markdown_file_sha', true );

    if ( empty( $sha ) ) {
        // Sha is empty or does not exist
        return null;
    }

    return $sha;
}
