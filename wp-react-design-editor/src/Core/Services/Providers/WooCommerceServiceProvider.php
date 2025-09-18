<?php

namespace ReactDesignEditor\Core\Services\Providers;

use ReactDesignEditor\Core\Support\ServiceProvider;

class WooCommerceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        add_action( 'woocommerce_init', [ $this, 'bootstrap_woocommerce_integration' ] );
    }

    public function bootstrap_woocommerce_integration(): void
    {
        if ( ! function_exists( 'WC' ) ) {
            return;
        }

        add_filter( 'woocommerce_add_cart_item_data', [ $this, 'attach_design_data' ], 10, 2 );
    }

    public function attach_design_data( array $cart_item_data, int $product_id ): array
    {
        if ( ! isset( $cart_item_data['react_design_editor'] ) ) {
            $cart_item_data['react_design_editor'] = [
                'design_id' => null,
                'price'     => null,
            ];
        }

        return $cart_item_data;
    }
}
