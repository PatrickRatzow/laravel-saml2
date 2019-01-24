<?php

namespace Aacotroneo\Saml2;

use OneLogin\Saml2\Utils;

class Saml2
{
    /**
     * Config instance.
     *
     * @var \Aacotroneo\Saml2\Config
     */
    protected $config;

    /**
     * Constructor
     * .
     * @param \Aacotroneo\Saml2\Config $config
     *
     * @return void
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        // Should we enable proxy vars?
        if ($this->config->proxy_vars) {
            Utils::setProxyVars(true);
        }
    }

    /**
     * Get config instance.
     *
     * @return \Aacotroneo\Saml2\Config
     */
    public function config(): Config
    {
        return $this->config;
    }

    public function load(string $name = null)
    {
        //
    }
}
