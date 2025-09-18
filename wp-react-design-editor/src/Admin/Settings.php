<?php

namespace ReactDesignEditor\Admin;

use function __;
use function absint;
use function get_option;
use function in_array;
use function is_array;
use function preg_split;
use function sanitize_key;
use function sanitize_text_field;
use function str_replace;
use function wp_parse_args;

class Settings
{
    public const OPTION_KEY = 'react_design_editor_settings';

    public static function get_default_settings(): array
    {
        return [
            'enable_editor'                     => true,
            'enable_live_preview'               => true,
            'enable_guides'                     => true,
            'default_unit'                      => 'px',
            'default_canvas_width'              => 1000,
            'default_canvas_height'             => 1000,
            'enable_google_fonts'               => true,
            'enabled_fonts'                     => "Roboto\nOpen Sans\nLato\nMontserrat",
            'default_font'                      => 'Roboto',
            'fallback_font_stack'               => 'Helvetica, Arial, sans-serif',
            'enable_font_preloading'            => false,
            'allow_vector_uploads'              => true,
            'allow_bitmap_uploads'              => true,
            'allowed_upload_types'              => "image/svg+xml\nimage/png\nimage/jpeg\napplication/pdf",
            'max_upload_size_mb'                => 25,
            'safe_zone_percentage'              => 5,
            'canvas_bleed_margin_inch'          => 0.125,
            'enable_live_pricing'               => true,
            'pricing_base_price'                => 0.0,
            'pricing_price_per_square_unit'     => 0.05,
            'pricing_additional_color_cost'     => 1.5,
            'pricing_finish_options'            => "Matte Lamination\nGloss Lamination\nUV Coating",
            'pricing_rounding_precision'        => 2,
            'pdf_renderer'                      => 'tcpdf',
            'pdf_include_crop_marks'            => true,
            'pdf_bleed_margin_inch'             => 0.125,
            'pdf_embed_fonts'                   => true,
            'pdf_color_profile'                 => 'Fogra39',
            'woocommerce_override_cart_price'   => true,
            'woocommerce_metadata_key'          => 'react_design_editor_meta',
            'woocommerce_enable_order_downloads'=> true,
            'woocommerce_auto_status'           => 'processing',
            'woocommerce_enable_price_breakdown'=> false,
            'security_allow_guest_designs'      => false,
            'security_restrict_rest_to_logged_in'=> true,
            'rest_enable_public_previews'       => false,
            'rest_enable_logging'               => false,
            'rest_rate_limit'                   => 60,
        ];
    }

    public static function get_settings(): array
    {
        $stored = get_option( self::OPTION_KEY, [] );

        if ( ! is_array( $stored ) ) {
            $stored = [];
        }

        return wp_parse_args( $stored, self::get_default_settings() );
    }

    /**
     * Sanitize settings payload before saving.
     *
     * @param mixed $input Incoming settings data.
     */
    public static function sanitize( $input ): array
    {
        $defaults  = self::get_default_settings();
        $sanitized = $defaults;

        if ( ! is_array( $input ) ) {
            $input = [];
        }

        $sanitized['enable_editor']           = ! empty( $input['enable_editor'] );
        $sanitized['enable_live_preview']     = ! empty( $input['enable_live_preview'] );
        $sanitized['enable_guides']           = ! empty( $input['enable_guides'] );

        $sanitized['default_unit'] = in_array( $input['default_unit'] ?? $defaults['default_unit'], array_keys( self::get_supported_units() ), true )
            ? $input['default_unit']
            : $defaults['default_unit'];

        $sanitized['default_canvas_width']  = max( 1, absint( $input['default_canvas_width'] ?? $defaults['default_canvas_width'] ) );
        $sanitized['default_canvas_height'] = max( 1, absint( $input['default_canvas_height'] ?? $defaults['default_canvas_height'] ) );

        $sanitized['enable_google_fonts']    = ! empty( $input['enable_google_fonts'] );
        $sanitized['enable_font_preloading'] = ! empty( $input['enable_font_preloading'] );

        $sanitized['enabled_fonts'] = self::sanitize_multiline_string( $input['enabled_fonts'] ?? $defaults['enabled_fonts'] );
        $sanitized['default_font']  = sanitize_text_field( $input['default_font'] ?? $defaults['default_font'] );

        $sanitized['fallback_font_stack'] = sanitize_text_field( $input['fallback_font_stack'] ?? $defaults['fallback_font_stack'] );

        $sanitized['allow_vector_uploads'] = ! empty( $input['allow_vector_uploads'] );
        $sanitized['allow_bitmap_uploads'] = ! empty( $input['allow_bitmap_uploads'] );

        $sanitized['allowed_upload_types'] = self::sanitize_multiline_string( $input['allowed_upload_types'] ?? $defaults['allowed_upload_types'] );

        $sanitized['max_upload_size_mb'] = max( 1, min( 512, absint( $input['max_upload_size_mb'] ?? $defaults['max_upload_size_mb'] ) ) );

        $sanitized['safe_zone_percentage']     = max( 0, min( 50, absint( $input['safe_zone_percentage'] ?? $defaults['safe_zone_percentage'] ) ) );
        $sanitized['canvas_bleed_margin_inch'] = max( 0.0, min( 2.0, self::to_float( $input['canvas_bleed_margin_inch'] ?? $defaults['canvas_bleed_margin_inch'] ) ) );

        $sanitized['enable_live_pricing']           = ! empty( $input['enable_live_pricing'] );
        $sanitized['pricing_base_price']            = max( 0.0, self::to_float( $input['pricing_base_price'] ?? $defaults['pricing_base_price'] ) );
        $sanitized['pricing_price_per_square_unit'] = max( 0.0, self::to_float( $input['pricing_price_per_square_unit'] ?? $defaults['pricing_price_per_square_unit'] ) );
        $sanitized['pricing_additional_color_cost'] = max( 0.0, self::to_float( $input['pricing_additional_color_cost'] ?? $defaults['pricing_additional_color_cost'] ) );

        $sanitized['pricing_finish_options']     = self::sanitize_multiline_string( $input['pricing_finish_options'] ?? $defaults['pricing_finish_options'] );
        $sanitized['pricing_rounding_precision'] = max( 0, min( 4, absint( $input['pricing_rounding_precision'] ?? $defaults['pricing_rounding_precision'] ) ) );

        $sanitized['pdf_renderer'] = in_array( $input['pdf_renderer'] ?? $defaults['pdf_renderer'], array_keys( self::get_supported_pdf_renderers() ), true )
            ? $input['pdf_renderer']
            : $defaults['pdf_renderer'];

        $sanitized['pdf_include_crop_marks'] = ! empty( $input['pdf_include_crop_marks'] );
        $sanitized['pdf_bleed_margin_inch']  = max( 0.0, min( 2.0, self::to_float( $input['pdf_bleed_margin_inch'] ?? $defaults['pdf_bleed_margin_inch'] ) ) );
        $sanitized['pdf_embed_fonts']        = ! empty( $input['pdf_embed_fonts'] );
        $sanitized['pdf_color_profile']      = sanitize_text_field( $input['pdf_color_profile'] ?? $defaults['pdf_color_profile'] );

        $sanitized['woocommerce_override_cart_price']   = ! empty( $input['woocommerce_override_cart_price'] );
        $sanitized['woocommerce_enable_order_downloads'] = ! empty( $input['woocommerce_enable_order_downloads'] );
        $sanitized['woocommerce_enable_price_breakdown'] = ! empty( $input['woocommerce_enable_price_breakdown'] );

        $metadata_key = sanitize_key( $input['woocommerce_metadata_key'] ?? $defaults['woocommerce_metadata_key'] );
        $sanitized['woocommerce_metadata_key'] = $metadata_key ?: $defaults['woocommerce_metadata_key'];

        $sanitized['woocommerce_auto_status'] = in_array( $input['woocommerce_auto_status'] ?? $defaults['woocommerce_auto_status'], array_keys( self::get_supported_order_statuses() ), true )
            ? $input['woocommerce_auto_status']
            : $defaults['woocommerce_auto_status'];

        $sanitized['security_allow_guest_designs']       = ! empty( $input['security_allow_guest_designs'] );
        $sanitized['security_restrict_rest_to_logged_in'] = ! empty( $input['security_restrict_rest_to_logged_in'] );
        $sanitized['rest_enable_public_previews']        = ! empty( $input['rest_enable_public_previews'] );
        $sanitized['rest_enable_logging']                = ! empty( $input['rest_enable_logging'] );
        $sanitized['rest_rate_limit']                    = max( 0, min( 1000, absint( $input['rest_rate_limit'] ?? $defaults['rest_rate_limit'] ) ) );

        return $sanitized;
    }

    public static function get_supported_units(): array
    {
        return [
            'px' => __( 'Pixels', 'react-design-editor' ),
            'in' => __( 'Inches', 'react-design-editor' ),
            'mm' => __( 'Millimetres', 'react-design-editor' ),
        ];
    }

    public static function get_supported_pdf_renderers(): array
    {
        return [
            'tcpdf'  => __( 'TCPDF (recommended)', 'react-design-editor' ),
            'dompdf' => __( 'DOMPDF', 'react-design-editor' ),
        ];
    }

    public static function get_supported_order_statuses(): array
    {
        return [
            'processing' => __( 'Processing', 'react-design-editor' ),
            'on-hold'    => __( 'On Hold', 'react-design-editor' ),
            'completed'  => __( 'Completed', 'react-design-editor' ),
        ];
    }

    private static function sanitize_multiline_string( string $value ): string
    {
        $lines = preg_split( '/[\r\n]+/', $value ) ?: [];

        $sanitized = [];

        foreach ( $lines as $line ) {
            $line = trim( $line );

            if ( '' === $line ) {
                continue;
            }

            $sanitized[] = sanitize_text_field( $line );
        }

        return implode( "\n", array_unique( $sanitized ) );
    }

    private static function to_float( $value ): float
    {
        if ( is_string( $value ) ) {
            $value = str_replace( ',', '.', $value );
        }

        return (float) $value;
    }
}
