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
    $asset_file = include( GITENBERG_DIR . '/build/gitenberg-sidebar.asset.php' );

    wp_register_script(
        'gitenberg-sidebar-script',
        GITENBERG_URL . 'build/gitenberg-sidebar.js',
        $asset_file['dependencies'],
        $asset_file['version'],
    );

    $config = get_plugin_config();

    if ( is_wp_error( $config ) ) {
        return null;
    }

    $markdown_files = list_remote_markdown_files();

    if ( is_wp_error( $markdown_files ) ) {
        $markdown_files = [];
    }

    wp_localize_script(
        'gitenberg-sidebar-script',
        'gitenbergData',
        array(
            'repo'          => $config['repo'],
            'markdownFiles' => $markdown_files,
        )
    );
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\register_scripts' );

/**
 * Enqueue JavaScript for Gutenberg sidebar customization.
 */
function enqueue_sidebar_scripts() {
    $current_screen = get_current_screen();

    if ( ! ( function_exists( 'use_block_editor_for_post_type' ) &&
             use_block_editor_for_post_type( $current_screen->post_type ) ) ) {
        return;
    }

    wp_enqueue_script( 'gitenberg-sidebar-script' );
}
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\enqueue_sidebar_scripts' );

/**
 * Enqueue scripts for the Gitenberg settings page.
 *
 * @param string $hook The current admin page hook.
 */
function enqueue_settings_scripts( $hook ) {
    // Exit if not this settings page.
    if ( 'settings_page_gitenberg-settings' !== $hook ) {
        return;
    }

    $asset_file = include( GITENBERG_DIR . '/build/settings.asset.php' );

    wp_enqueue_script(
        'gitenberg-settings-script',
        GITENBERG_URL . 'build/settings.js',
        $asset_file['dependencies'],
        $asset_file['version'],
        true
    );
    
    $localized_data = [
        'nonce' => wp_create_nonce( 'wp_rest' )
    ];

    // Localize the script with the nonce
    wp_localize_script( 'gitenberg-settings-script', 'gitenbergSettings', $localized_data );

    wp_enqueue_style( 'wp-components' );
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_settings_scripts', 10, 1 );