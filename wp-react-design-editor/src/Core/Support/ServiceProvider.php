<?php

namespace ReactDesignEditor\Core\Support;

/**
 * Base class for plugin service providers.
 */
abstract class ServiceProvider
{
    abstract public function register(): void;
}
