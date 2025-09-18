<?php

namespace ReactDesignEditor\Core\Services\Providers;

use ReactDesignEditor\Core\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
    }

    public function register_menu(): void
    {
        add_menu_page(
            __( 'Design Editor', 'react-design-editor' ),
            __( 'Design Editor', 'react-design-editor' ),
            'manage_options',
            'react-design-editor',
            [ $this, 'render_admin_page' ],
            'dashicons-art'
        );
    }

    public function render_admin_page(): void
    {
        echo '<div class="wrap"><h1>' . esc_html__( 'React Design Editor Settings', 'react-design-editor' ) . '</h1></div>';
    }
}
