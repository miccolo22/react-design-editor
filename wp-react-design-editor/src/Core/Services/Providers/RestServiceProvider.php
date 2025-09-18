<?php

namespace ReactDesignEditor\Core\Services\Providers;

use ReactDesignEditor\Core\Support\ServiceProvider;
use WP_REST_Request;
use WP_REST_Response;

class RestServiceProvider extends ServiceProvider
{
    private const ROUTE_NAMESPACE = 'react-design-editor/v1';

    public function register(): void
    {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes(): void
    {
        register_rest_route(
            self::ROUTE_NAMESPACE,
            '/designs',
            [
                'methods'             => [ 'GET' ],
                'callback'            => [ $this, 'list_designs' ],
                'permission_callback' => [ $this, 'can_manage_designs' ],
            ]
        );
    }

    public function list_designs( WP_REST_Request $request ): WP_REST_Response
    {
        return new WP_REST_Response(
            [
                'data'    => [],
                'message' => __( 'Design listing placeholder.', 'react-design-editor' ),
            ]
        );
    }

    public function can_manage_designs(): bool
    {
        return current_user_can( 'manage_woocommerce' );
    }
}
