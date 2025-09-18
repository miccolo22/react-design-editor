<?php

namespace ReactDesignEditor\Core\Services\Providers;

use ReactDesignEditor\Core\Support\ServiceProvider;

class AssetsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        add_action( 'init', [ $this, 'register_assets' ] );
    }

    public function register_assets(): void
    {
        if ( ! function_exists( 'wp_register_script' ) ) {
            return;
        }

        wp_register_script(
            'react-design-editor-frontend',
            RDE_PLUGIN_URL . 'assets/js/frontend.js',
            [ 'wp-element', 'wp-i18n' ],
            '0.1.0',
            true
        );

        wp_register_style(
            'react-design-editor-frontend',
            RDE_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            '0.1.0'
        );
    }
}
