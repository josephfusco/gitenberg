<?php
/**
 * Plugin Name: Gitenberg
 * Description: Edit Github Markdown files using WordPress and the Gutenberg block-based editor.
 * Author:      Jason Bahl, Joseph Fusco
 * Version:     1.0.0-beta.1
 * Text Domain: gitenberg
 */

namespace Gitenberg;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'GITENBERG_FILE', __FILE__ );
define( 'GITENBERG_DIR', dirname( __FILE__ ) );
define( 'GITENBERG_URL', plugin_dir_url( __FILE__ ) );
define( 'GITENBERG_PATH', plugin_basename( GITENBERG_FILE ) );
define( 'GITENBERG_SLUG', dirname( plugin_basename( GITENBERG_FILE ) ) );

require GITENBERG_DIR . '/includes/config/functions.php';
require GITENBERG_DIR . '/includes/admin/functions.php';
require GITENBERG_DIR . '/includes/github/functions.php';

require GITENBERG_DIR . '/includes/config/callbacks.php';
require GITENBERG_DIR . '/includes/rest/callbacks.php';
require GITENBERG_DIR . '/includes/admin/callbacks.php';
require GITENBERG_DIR . '/includes/assets/callbacks.php';
