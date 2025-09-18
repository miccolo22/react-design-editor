<?php

namespace ReactDesignEditor\Core\Services\Providers;

use ReactDesignEditor\Admin\SettingsPage;
use ReactDesignEditor\Core\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    private SettingsPage $settings_page;

    public function __construct()
    {
        $this->settings_page = new SettingsPage();
    }

    public function register(): void
    {
        add_action( 'admin_menu', [ $this->settings_page, 'register_menu' ] );
        add_action( 'admin_init', [ $this->settings_page, 'register_settings' ] );
    }
}
