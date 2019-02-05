<?php

namespace Aacotroneo\Saml2\Tests;

use Aacotroneo\Saml2\Facades\Saml2;
use Aacotroneo\Saml2\Saml2ServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * Get package aliases.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageAliases($app): array
    {
        return ['Saml2' => Saml2::class];
    }

    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [Saml2ServiceProvider::class];
    }
}
