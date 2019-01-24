<?php

namespace Aacotroneo\Saml2\Facades;

use Illuminate\Support\Facades\Facade;

class Saml2 extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return 'saml2';
    }
}
