<?php

namespace Aacotroneo\Saml2\Tests;

use Aacotroneo\Saml2\Config;

class ConfigTest extends TestCase
{
    /**
     * Test Config::formatUrl() method.
     *
     * @return void
     */
    public function testFormatUrl(): void
    {
        $config = app(Config::class);

        $this->assertSame(url('/'), $config->formatUrl('/'));
        $this->assertSame(url('/foo', ['bar' => 'baz']), $config->formatUrl('/foo', ['bar' => 'baz']));
        $this->assertSame(route('saml2.metadata'), $config->formatUrl('saml2.metadata'));
        $this->assertSame(route('saml2.metadata', ['test']), $config->formatUrl('saml2.metadata', ['test']));
    }
}
