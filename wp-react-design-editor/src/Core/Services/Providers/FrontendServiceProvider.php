<?php

namespace ReactDesignEditor\Core\Services\Providers;

use ReactDesignEditor\Core\Support\ServiceProvider;

class FrontendServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        add_shortcode( 'react_design_editor', [ $this, 'render_editor_shortcode' ] );
    }

    public function render_editor_shortcode( array $attributes = [] ): string
    {
        wp_enqueue_script( 'react-design-editor-frontend' );
        wp_enqueue_style( 'react-design-editor-frontend' );

        return '<div id="react-design-editor-app"></div>';
    }
}
