<?php

namespace Aacotroneo\Saml2;

use InvalidArgumentException;
use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Error;
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
     * Constructor.
     *
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

    /**
     * Get metadata for the specified Service Provider.
     *
     * @param string|null $name
     *
     * @throws InvalidArgumentException If metadata validation fails.
     *
     * @return string
     */
    public function metadata(string $name = null): string
    {
        $settings = $this->loadAuth($name)->getSettings();
        $metadata = $settings->getSPMetadata();
        $errors = $settings->validateMetadata($metadata);

        if (empty($errors)) {
            return $metadata;
        }

        throw new InvalidArgumentException(
            'Invalid Service Provider metadata: ' . implode(', ', $errors),
            Error::METADATA_SP_INVALID
        );
    }

    /**
     * Load OneLogin Auth instance for a specific Service Provider.
     *
     * @param string|null $name
     *
     * @return \OneLogin\Saml2\Auth
     */
    public function loadAuth(string $name = null): Auth
    {
        return new Auth($this->config->getOneLogin($name));
    }
}
