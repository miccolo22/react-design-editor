<?php

namespace ReactDesignEditor\Core\Services;

use ReactDesignEditor\Core\Support\ServiceProvider;

/**
 * Simple service registry to orchestrate providers.
 */
class ServiceRegistry
{
    /**
     * @var ServiceProvider[]
     */
    private array $providers = [];

    public function __construct()
    {
        $this->providers = [
            new \ReactDesignEditor\Core\Services\Providers\AssetsServiceProvider(),
            new \ReactDesignEditor\Core\Services\Providers\AdminServiceProvider(),
            new \ReactDesignEditor\Core\Services\Providers\FrontendServiceProvider(),
            new \ReactDesignEditor\Core\Services\Providers\RestServiceProvider(),
            new \ReactDesignEditor\Core\Services\Providers\WooCommerceServiceProvider(),
        ];
    }

    public function register(): void
    {
        foreach ( $this->providers as $provider ) {
            $provider->register();
        }
    }
}
