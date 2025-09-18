<?php

namespace ReactDesignEditor\Core;

use ReactDesignEditor\Core\Services\ServiceRegistry;

/**
 * Main plugin bootstrapper.
 */
class Plugin
{
    private static ?self $instance = null;

    private ServiceRegistry $services;

    private function __construct()
    {
        $this->services = new ServiceRegistry();
    }

    public static function instance(): self
    {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function boot(): void
    {
        add_action( 'init', [ $this, 'load_textdomain' ] );
        $this->services->register();
    }

    public function load_textdomain(): void
    {
        load_plugin_textdomain( 'react-design-editor', false, dirname( RDE_PLUGIN_BASENAME ) . '/languages' );
    }

    public static function activate(): void
    {
        // Future activation logic.
    }

    public static function deactivate(): void
    {
        // Future deactivation logic.
    }

    public static function uninstall(): void
    {
        // Future uninstall cleanup.
    }
}
