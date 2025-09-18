<?php
/**
 * Uninstall procedures for React Design Editor for WooCommerce.
 *
 * @package ReactDesignEditor
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

if ( ! file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    return;
}

require_once __DIR__ . '/vendor/autoload.php';

\ReactDesignEditor\Core\Plugin::uninstall();
