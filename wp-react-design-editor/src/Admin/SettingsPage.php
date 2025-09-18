<?php

namespace ReactDesignEditor\Admin;

use function __;
use function add_menu_page;
use function add_settings_field;
use function add_settings_section;
use function checked;
use function current_user_can;
use function do_settings_sections;
use function esc_attr;
use function esc_html;
use function esc_html__;
use function esc_textarea;
use function printf;
use function register_setting;
use function selected;
use function settings_errors;
use function settings_fields;
use function submit_button;
use function wp_kses_post;

class SettingsPage
{
    private string $menu_slug = 'react-design-editor';

    public function register_menu(): void
    {
        add_menu_page(
            __( 'Design Editor', 'react-design-editor' ),
            __( 'Design Editor', 'react-design-editor' ),
            'manage_options',
            $this->menu_slug,
            [ $this, 'render_page' ],
            'dashicons-art'
        );
    }

    public function register_settings(): void
    {
        register_setting(
            'react_design_editor_options',
            Settings::OPTION_KEY,
            [
                'type'              => 'array',
                'sanitize_callback' => [ Settings::class, 'sanitize' ],
                'default'           => Settings::get_default_settings(),
            ]
        );

        $this->register_general_section();
        $this->register_typography_section();
        $this->register_asset_section();
        $this->register_pricing_section();
        $this->register_pdf_section();
        $this->register_woocommerce_section();
        $this->register_security_section();
    }

    public function render_page(): void
    {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'React Design Editor Settings', 'react-design-editor' ) . '</h1>';
        echo '<p>' . esc_html__( 'Configure the design editor experience, pricing, and WooCommerce integration options.', 'react-design-editor' ) . '</p>';

        settings_errors();

        echo '<form method="post" action="options.php">';
        settings_fields( 'react_design_editor_options' );
        do_settings_sections( $this->menu_slug );
        submit_button();
        echo '</form>';
        echo '</div>';
    }

    public function render_section_description( string $message ): void
    {
        echo '<p>' . wp_kses_post( $message ) . '</p>';
    }

    public function render_field( array $args ): void
    {
        $settings = Settings::get_settings();
        $key      = $args['key'];
        $value    = $settings[ $key ] ?? ( $args['default'] ?? '' );
        $name     = Settings::OPTION_KEY . '[' . $key . ']';
        $id       = $args['label_for'] ?? $key;
        $type     = $args['type'] ?? 'text';

        switch ( $type ) {
            case 'checkbox':
                printf(
                    '<label for="%1$s"><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s /> %4$s</label>',
                    esc_attr( $id ),
                    esc_attr( $name ),
                    checked( (bool) $value, true, false ),
                    esc_html( $args['toggle_label'] ?? '' )
                );
                break;
            case 'number':
                $step = $args['step'] ?? '1';
                $min  = $args['min'] ?? '';
                $max  = $args['max'] ?? '';
                printf(
                    '<input type="number" id="%1$s" name="%2$s" value="%3$s" step="%4$s"%5$s%6$s class="regular-text" />',
                    esc_attr( $id ),
                    esc_attr( $name ),
                    esc_attr( $value ),
                    esc_attr( $step ),
                    '' !== $min ? ' min="' . esc_attr( $min ) . '"' : '',
                    '' !== $max ? ' max="' . esc_attr( $max ) . '"' : ''
                );
                break;
            case 'textarea':
                $rows = $args['rows'] ?? 5;
                if ( isset( $args['format'] ) && 'lines' === $args['format'] ) {
                    $value = implode( "\n", array_filter( array_map( 'trim', explode( "\n", (string) $value ) ) ) );
                }
                printf(
                    '<textarea id="%1$s" name="%2$s" rows="%3$d" class="large-text code">%4$s</textarea>',
                    esc_attr( $id ),
                    esc_attr( $name ),
                    (int) $rows,
                    esc_textarea( (string) $value )
                );
                break;
            case 'select':
                $options = $args['options'] ?? [];
                echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';
                foreach ( $options as $option_key => $label ) {
                    printf(
                        '<option value="%1$s" %2$s>%3$s</option>',
                        esc_attr( $option_key ),
                        selected( $value, $option_key, false ),
                        esc_html( $label )
                    );
                }
                echo '</select>';
                break;
            default:
                printf(
                    '<input type="text" id="%1$s" name="%2$s" value="%3$s" class="regular-text" />',
                    esc_attr( $id ),
                    esc_attr( $name ),
                    esc_attr( $value )
                );
                break;
        }

        if ( ! empty( $args['description'] ) ) {
            echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
        }
    }

    private function register_general_section(): void
    {
        add_settings_section(
            'rde_general',
            __( 'General Settings', 'react-design-editor' ),
            fn () => $this->render_section_description( __( 'Control the default editor canvas and UX behaviour.', 'react-design-editor' ) ),
            $this->menu_slug
        );

        add_settings_field(
            'enable_editor',
            __( 'Enable editor', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_general',
            [
                'key'          => 'enable_editor',
                'type'         => 'checkbox',
                'label_for'    => 'rde-enable-editor',
                'toggle_label' => __( 'Enable the frontend design experience for supported products.', 'react-design-editor' ),
                'description'  => __( 'Disable this to temporarily turn off the designer for maintenance.', 'react-design-editor' ),
            ]
        );

        add_settings_field(
            'enable_live_preview',
            __( 'Live preview', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_general',
            [
                'key'          => 'enable_live_preview',
                'type'         => 'checkbox',
                'label_for'    => 'rde-enable-live-preview',
                'toggle_label' => __( 'Enable real-time preview updates while customers edit designs.', 'react-design-editor' ),
            ]
        );

        add_settings_field(
            'enable_guides',
            __( 'Guides & rulers', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_general',
            [
                'key'          => 'enable_guides',
                'type'         => 'checkbox',
                'label_for'    => 'rde-enable-guides',
                'toggle_label' => __( 'Display rulers, guides, safe zones and bleed outlines in the editor.', 'react-design-editor' ),
            ]
        );

        add_settings_field(
            'default_unit',
            __( 'Measurement unit', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_general',
            [
                'key'       => 'default_unit',
                'type'      => 'select',
                'label_for' => 'rde-default-unit',
                'options'   => Settings::get_supported_units(),
            ]
        );

        add_settings_field(
            'default_canvas_width',
            __( 'Canvas width', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_general',
            [
                'key'         => 'default_canvas_width',
                'type'        => 'number',
                'label_for'   => 'rde-canvas-width',
                'description' => __( 'Width of the design canvas in the selected unit.', 'react-design-editor' ),
                'min'         => 1,
                'max'         => 10000,
            ]
        );

        add_settings_field(
            'default_canvas_height',
            __( 'Canvas height', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_general',
            [
                'key'         => 'default_canvas_height',
                'type'        => 'number',
                'label_for'   => 'rde-canvas-height',
                'description' => __( 'Height of the design canvas in the selected unit.', 'react-design-editor' ),
                'min'         => 1,
                'max'         => 10000,
            ]
        );
    }

    private function register_typography_section(): void
    {
        add_settings_section(
            'rde_typography',
            __( 'Typography', 'react-design-editor' ),
            fn () => $this->render_section_description( __( 'Manage the fonts that are available to customers in the editor.', 'react-design-editor' ) ),
            $this->menu_slug
        );

        add_settings_field(
            'enable_google_fonts',
            __( 'Google Fonts', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_typography',
            [
                'key'          => 'enable_google_fonts',
                'type'         => 'checkbox',
                'label_for'    => 'rde-enable-google-fonts',
                'toggle_label' => __( 'Load Google Fonts selected below on the storefront editor.', 'react-design-editor' ),
            ]
        );

        add_settings_field(
            'enabled_fonts',
            __( 'Available fonts', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_typography',
            [
                'key'         => 'enabled_fonts',
                'type'        => 'textarea',
                'format'      => 'lines',
                'rows'        => 6,
                'label_for'   => 'rde-enabled-fonts',
                'description' => __( 'Enter one font family per line. Supports Google Fonts and self-hosted fonts.', 'react-design-editor' ),
            ]
        );

        add_settings_field(
            'default_font',
            __( 'Default font', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_typography',
            [
                'key'         => 'default_font',
                'type'        => 'text',
                'label_for'   => 'rde-default-font',
                'description' => __( 'Font applied to new text layers when a customer starts designing.', 'react-design-editor' ),
            ]
        );

        add_settings_field(
            'fallback_font_stack',
            __( 'Fallback stack', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_typography',
            [
                'key'         => 'fallback_font_stack',
                'type'        => 'text',
                'label_for'   => 'rde-fallback-fonts',
                'description' => __( 'Comma-separated system font stack used if a custom font fails to load.', 'react-design-editor' ),
            ]
        );

        add_settings_field(
            'enable_font_preloading',
            __( 'Preload fonts', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_typography',
            [
                'key'          => 'enable_font_preloading',
                'type'         => 'checkbox',
                'label_for'    => 'rde-font-preloading',
                'toggle_label' => __( 'Preload fonts in the editor head to avoid layout shifts.', 'react-design-editor' ),
            ]
        );
    }

    private function register_asset_section(): void
    {
        add_settings_section(
            'rde_assets',
            __( 'Uploads & Artwork', 'react-design-editor' ),
            fn () => $this->render_section_description( __( 'Control the artwork formats and safety guides available to end users.', 'react-design-editor' ) ),
            $this->menu_slug
        );

        add_settings_field(
            'allow_vector_uploads',
            __( 'Vector uploads', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_assets',
            [
                'key'          => 'allow_vector_uploads',
                'type'         => 'checkbox',
                'label_for'    => 'rde-allow-vector',
                'toggle_label' => __( 'Allow SVG and PDF vector uploads in the designer.', 'react-design-editor' ),
            ]
        );

        add_settings_field(
            'allow_bitmap_uploads',
            __( 'Bitmap uploads', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_assets',
            [
                'key'          => 'allow_bitmap_uploads',
                'type'         => 'checkbox',
                'label_for'    => 'rde-allow-bitmap',
                'toggle_label' => __( 'Allow PNG and JPEG uploads.', 'react-design-editor' ),
            ]
        );

        add_settings_field(
            'allowed_upload_types',
            __( 'Allowed MIME types', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_assets',
            [
                'key'         => 'allowed_upload_types',
                'type'        => 'textarea',
                'format'      => 'lines',
                'rows'        => 4,
                'label_for'   => 'rde-allowed-mimes',
                'description' => __( 'One MIME type per line; applied when validating customer uploads.', 'react-design-editor' ),
            ]
        );

        add_settings_field(
            'max_upload_size_mb',
            __( 'Upload limit (MB)', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_assets',
            [
                'key'         => 'max_upload_size_mb',
                'type'        => 'number',
                'label_for'   => 'rde-max-upload',
                'description' => __( 'Maximum upload size enforced by the designer (in megabytes).', 'react-design-editor' ),
                'min'         => 1,
                'max'         => 512,
            ]
        );

        add_settings_field(
            'safe_zone_percentage',
            __( 'Safe zone (%)', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_assets',
            [
                'key'         => 'safe_zone_percentage',
                'type'        => 'number',
                'label_for'   => 'rde-safe-zone',
                'description' => __( 'Percentage of the canvas reserved as a safe zone inside the trim area.', 'react-design-editor' ),
                'min'         => 0,
                'max'         => 50,
            ]
        );

        add_settings_field(
            'canvas_bleed_margin_inch',
            __( 'Bleed margin (in)', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_assets',
            [
                'key'         => 'canvas_bleed_margin_inch',
                'type'        => 'number',
                'label_for'   => 'rde-canvas-bleed',
                'description' => __( 'Canvas bleed margin shown in the editor, measured in inches.', 'react-design-editor' ),
                'step'        => '0.001',
                'min'         => 0,
                'max'         => 2,
            ]
        );
    }

    private function register_pricing_section(): void
    {
        add_settings_section(
            'rde_pricing',
            __( 'Pricing Engine', 'react-design-editor' ),
            fn () => $this->render_section_description( __( 'Tune the live pricing calculator that syncs with WooCommerce cart totals.', 'react-design-editor' ) ),
            $this->menu_slug
        );

        add_settings_field(
            'enable_live_pricing',
            __( 'Live pricing', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_pricing',
            [
                'key'          => 'enable_live_pricing',
                'type'         => 'checkbox',
                'label_for'    => 'rde-live-pricing',
                'toggle_label' => __( 'Update pricing dynamically as the design changes.', 'react-design-editor' ),
            ]
        );

        add_settings_field(
            'pricing_base_price',
            __( 'Base price', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_pricing',
            [
                'key'         => 'pricing_base_price',
                'type'        => 'number',
                'step'        => '0.01',
                'label_for'   => 'rde-base-price',
                'description' => __( 'Starting price applied before coverage and finishing calculations.', 'react-design-editor' ),
                'min'         => 0,
            ]
        );

        add_settings_field(
            'pricing_price_per_square_unit',
            __( 'Price per unit', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_pricing',
            [
                'key'         => 'pricing_price_per_square_unit',
                'type'        => 'number',
                'step'        => '0.01',
                'label_for'   => 'rde-price-per-unit',
                'description' => __( 'Cost multiplier for printable surface area (based on selected unit).', 'react-design-editor' ),
                'min'         => 0,
            ]
        );

        add_settings_field(
            'pricing_additional_color_cost',
            __( 'Color surcharge', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_pricing',
            [
                'key'         => 'pricing_additional_color_cost',
                'type'        => 'number',
                'step'        => '0.01',
                'label_for'   => 'rde-color-cost',
                'description' => __( 'Additional cost applied per spot color over your included allowance.', 'react-design-editor' ),
                'min'         => 0,
            ]
        );

        add_settings_field(
            'pricing_finish_options',
            __( 'Finishing options', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_pricing',
            [
                'key'         => 'pricing_finish_options',
                'type'        => 'textarea',
                'format'      => 'lines',
                'rows'        => 4,
                'label_for'   => 'rde-finishing-options',
                'description' => __( 'Define optional finishes (one per line) used for rules in the pricing engine.', 'react-design-editor' ),
            ]
        );

        add_settings_field(
            'pricing_rounding_precision',
            __( 'Rounding precision', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_pricing',
            [
                'key'         => 'pricing_rounding_precision',
                'type'        => 'number',
                'label_for'   => 'rde-rounding-precision',
                'description' => __( 'Number of decimal places used when syncing totals to WooCommerce.', 'react-design-editor' ),
                'min'         => 0,
                'max'         => 4,
            ]
        );
    }

    private function register_pdf_section(): void
    {
        add_settings_section(
            'rde_pdf',
            __( 'PDF Production', 'react-design-editor' ),
            fn () => $this->render_section_description( __( 'Control the render pipeline for production-ready PDF exports.', 'react-design-editor' ) ),
            $this->menu_slug
        );

        add_settings_field(
            'pdf_renderer',
            __( 'Renderer', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_pdf',
            [
                'key'       => 'pdf_renderer',
                'type'      => 'select',
                'label_for' => 'rde-pdf-renderer',
                'options'   => Settings::get_supported_pdf_renderers(),
            ]
        );

        add_settings_field(
            'pdf_include_crop_marks',
            __( 'Crop marks', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_pdf',
            [
                'key'          => 'pdf_include_crop_marks',
                'type'         => 'checkbox',
                'label_for'    => 'rde-pdf-crop-marks',
                'toggle_label' => __( 'Include crop marks and printers marks on exported PDFs.', 'react-design-editor' ),
            ]
        );

        add_settings_field(
            'pdf_bleed_margin_inch',
            __( 'Bleed margin (in)', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_pdf',
            [
                'key'         => 'pdf_bleed_margin_inch',
                'type'        => 'number',
                'label_for'   => 'rde-pdf-bleed',
                'description' => __( 'Bleed margin applied when generating production PDFs.', 'react-design-editor' ),
                'step'        => '0.001',
                'min'         => 0,
                'max'         => 2,
            ]
        );

        add_settings_field(
            'pdf_embed_fonts',
            __( 'Embed fonts', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_pdf',
            [
                'key'          => 'pdf_embed_fonts',
                'type'         => 'checkbox',
                'label_for'    => 'rde-pdf-embed-fonts',
                'toggle_label' => __( 'Embed font subsets directly into generated PDFs.', 'react-design-editor' ),
            ]
        );

        add_settings_field(
            'pdf_color_profile',
            __( 'Color profile', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_pdf',
            [
                'key'         => 'pdf_color_profile',
                'type'        => 'text',
                'label_for'   => 'rde-pdf-color-profile',
                'description' => __( 'ICC profile identifier used when normalising colour during export.', 'react-design-editor' ),
            ]
        );
    }

    private function register_woocommerce_section(): void
    {
        add_settings_section(
            'rde_woocommerce',
            __( 'WooCommerce Integration', 'react-design-editor' ),
            fn () => $this->render_section_description( __( 'Map design data and prices into carts and orders.', 'react-design-editor' ) ),
            $this->menu_slug
        );

        add_settings_field(
            'woocommerce_override_cart_price',
            __( 'Override cart pricing', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_woocommerce',
            [
                'key'          => 'woocommerce_override_cart_price',
                'type'         => 'checkbox',
                'label_for'    => 'rde-woo-override-price',
                'toggle_label' => __( 'Apply custom pricing returned by the design editor to cart items.', 'react-design-editor' ),
            ]
        );

        add_settings_field(
            'woocommerce_enable_price_breakdown',
            __( 'Show price breakdown', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_woocommerce',
            [
                'key'          => 'woocommerce_enable_price_breakdown',
                'type'         => 'checkbox',
                'label_for'    => 'rde-woo-price-breakdown',
                'toggle_label' => __( 'Expose a line-item breakdown of design charges on carts and orders.', 'react-design-editor' ),
            ]
        );

        add_settings_field(
            'woocommerce_metadata_key',
            __( 'Metadata key', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_woocommerce',
            [
                'key'         => 'woocommerce_metadata_key',
                'type'        => 'text',
                'label_for'   => 'rde-woo-meta-key',
                'description' => __( 'Slug used when storing design payloads against carts and orders.', 'react-design-editor' ),
            ]
        );

        add_settings_field(
            'woocommerce_enable_order_downloads',
            __( 'Order downloads', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_woocommerce',
            [
                'key'          => 'woocommerce_enable_order_downloads',
                'type'         => 'checkbox',
                'label_for'    => 'rde-woo-order-downloads',
                'toggle_label' => __( 'Allow store managers to download customer artwork from the order screen.', 'react-design-editor' ),
            ]
        );

        add_settings_field(
            'woocommerce_auto_status',
            __( 'Auto status', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_woocommerce',
            [
                'key'       => 'woocommerce_auto_status',
                'type'      => 'select',
                'label_for' => 'rde-woo-status',
                'options'   => Settings::get_supported_order_statuses(),
                'description' => __( 'Order status to set when all design assets are approved.', 'react-design-editor' ),
            ]
        );
    }

    private function register_security_section(): void
    {
        add_settings_section(
            'rde_security',
            __( 'Security & REST API', 'react-design-editor' ),
            fn () => $this->render_section_description( __( 'Restrict who can create and access designs through the API.', 'react-design-editor' ) ),
            $this->menu_slug
        );

        add_settings_field(
            'security_allow_guest_designs',
            __( 'Guest designs', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_security',
            [
                'key'          => 'security_allow_guest_designs',
                'type'         => 'checkbox',
                'label_for'    => 'rde-guest-designs',
                'toggle_label' => __( 'Allow non-logged-in customers to create temporary designs.', 'react-design-editor' ),
            ]
        );

        add_settings_field(
            'security_restrict_rest_to_logged_in',
            __( 'Restrict REST access', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_security',
            [
                'key'          => 'security_restrict_rest_to_logged_in',
                'type'         => 'checkbox',
                'label_for'    => 'rde-rest-restrict',
                'toggle_label' => __( 'Require authentication for API endpoints (recommended).', 'react-design-editor' ),
            ]
        );

        add_settings_field(
            'rest_enable_public_previews',
            __( 'Public previews', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_security',
            [
                'key'          => 'rest_enable_public_previews',
                'type'         => 'checkbox',
                'label_for'    => 'rde-rest-previews',
                'toggle_label' => __( 'Generate share links that allow previewing designs without logging in.', 'react-design-editor' ),
            ]
        );

        add_settings_field(
            'rest_enable_logging',
            __( 'Request logging', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_security',
            [
                'key'          => 'rest_enable_logging',
                'type'         => 'checkbox',
                'label_for'    => 'rde-rest-logging',
                'toggle_label' => __( 'Log REST API interactions for auditing and support.', 'react-design-editor' ),
            ]
        );

        add_settings_field(
            'rest_rate_limit',
            __( 'Rate limit', 'react-design-editor' ),
            [ $this, 'render_field' ],
            $this->menu_slug,
            'rde_security',
            [
                'key'         => 'rest_rate_limit',
                'type'        => 'number',
                'label_for'   => 'rde-rest-rate-limit',
                'description' => __( 'Maximum number of REST requests per minute allowed per user (0 disables limiting).', 'react-design-editor' ),
                'min'         => 0,
                'max'         => 1000,
            ]
        );
    }
}
