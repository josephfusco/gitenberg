<?php
/**
 * Callback functions for assets loading.
 * 
 * @package Gitenberg\Assets
 */

namespace Gitenberg\Assets;

use function Gitenberg\Config\get_plugin_config;
use function Gitenberg\Admin\get_linked_markdown_file;
use function Gitenberg\GitHub\list_remote_markdown_files;

/**
 * Registers and localizes plugin scripts.
 */
function register_scripts() {
    wp_register_script(
        'gitenberg-sidebar-script',
        GITENBERG_URL . 'js/gitenberg-sidebar.js',
        [
            'wp-plugins',
            'wp-edit-post',
            'wp-element',
            'wp-components',
            'wp-data',
            'wp-i18n'
        ],
        filemtime( GITENBERG_DIR . '/js/gitenberg-sidebar.js' )
    );

    $config = get_plugin_config();

    if ( is_wp_error( $config ) ) {
        return null;
    }

    $markdown_files = list_remote_markdown_files();

    if ( is_wp_error( $markdown_files ) ) {
        $markdown_files = [];
    }

    global $post;

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
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\register_scripts' );

/**
 * Enqueue JavaScript for Gutenberg sidebar customization.
 */
function enqueue_gitenberg_script() {
    $current_screen = get_current_screen();

    if ( ! ( function_exists( 'use_block_editor_for_post_type' ) &&
             use_block_editor_for_post_type( $current_screen->post_type ) ) ) {
        return;
    }

    wp_enqueue_script( 'gitenberg-sidebar-script' );
}
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\enqueue_gitenberg_script' );
