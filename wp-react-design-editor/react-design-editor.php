<?php
/**
 * Plugin Name:       React Design Editor for WooCommerce
 * Plugin URI:        https://example.com/react-design-editor
 * Description:       Advanced product design editor and pricing engine for WooCommerce.
 * Version:           0.1.0
 * Author:            Your Company
 * Author URI:        https://example.com
 * Text Domain:       react-design-editor
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      8.0
 *
 * @package ReactDesignEditor
 */

defined( 'ABSPATH' ) || exit;

define( 'RDE_PLUGIN_FILE', __FILE__ );
define( 'RDE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'RDE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'RDE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    add_action( 'admin_notices', static function () {
        if ( current_user_can( 'activate_plugins' ) ) {
            printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                esc_html__( 'React Design Editor for WooCommerce requires Composer dependencies to be installed.', 'react-design-editor' )
            );
        }
    } );
    return;
}

add_action( 'plugins_loaded', static function () {
    \ReactDesignEditor\Core\Plugin::instance()->boot();
} );

register_activation_hook( __FILE__, [ '\\ReactDesignEditor\\Core\\Plugin', 'activate' ] );
register_deactivation_hook( __FILE__, [ '\\ReactDesignEditor\\Core\\Plugin', 'deactivate' ] );
