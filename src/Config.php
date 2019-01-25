<?php

namespace Aacotroneo\Saml2;

use OutOfRangeException;
use RuntimeException;
use Aacotroneo\Saml2\Http\Controllers\Saml2Controller;

class Config
{
    /**
     * Configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param array $config
     *
     * @return void
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get routes controller, with fallback to package controller.
     *
     * @return string
     */
    public function getRoutesController(): string
    {
        return array_get($this->config['settings'], 'routes_controller') ?? Saml2Controller::class;
    }

    /**
     * Get OneLogin configuration for the provided Service Provider name.
     *
     * @param string|null $name
     *
     * @throws OutOfRangeException If no Service Provider was found with the specified name.
     * @throws OutOfRangeException If no Identity Provider was found with the specified name.
     *
     * @return array
     */
    public function getOneLogin(string $name = null): array
    {
        $group = $this->test ? 'test' : 'prod';
        $sps = $this->config['sps'][$group];
        $idps = $this->config['idps'][$group];
        if (empty($name)) {
            $name = $this->config['sps']['default'] ?? key($sps);
        }

        if (!isset($sps[$name])) {
            throw new OutOfRangeException('Invalid Service Provider name: ' . $name);
        }
        if (!isset($idps[$name])) {
            throw new OutOfRangeException('Invalid Identity Provider name: ' . $name);
        }

        // Grab and process providers.
        $sp = $sps[$name];
        $idp = $idps[$name];

        if (empty($sp['entityId'])) {
            $sp['entityId'] = route('saml2.metadata', compact('name'));
        }
        if (empty($sp['assertionConsumerService']['url'])) {
            $sp['assertionConsumerService']['url'] = route('saml2.acs', compact('name'));
        }
        if (!empty($sp['singleLogoutService']) && empty($sp['singleLogoutService']['url'])) {
            $sp['singleLogoutService']['url'] = route('saml2.sls', compact('name'));
        }
        if (is_readable($sp['privateKey'])) {
            $sp['privateKey'] = $this->readPrivateKey($sp['privateKey'], $sp['passphrase'] ?? '');
        }
        if (is_readable($sp['x509cert'])) {
            $sp['x509cert'] = $this->readCertificate($sp['x509cert']);
        }
        if (is_readable($idp['x509cert'])) {
            $idp['x509cert'] = $this->readCertificate($idp['x509cert']);
        }

        // Handle onelogin overrides.
        $onelogin = $this->config['onelogin'];
        if (isset($sp['onelogin'])) {
            $overrides = array_pull($sp, 'onelogin');
            if (!empty($overrides)) {
                foreach (array_dot($overrides) as $key => $value) {
                    array_set($onelogin, $key, $value);
                }
            }
        }

        return $onelogin + compact('sp', 'idp');
    }

    /**
     * Read (and unencrypt) a private key from the specified path.
     *
     * @param string $path
     * @param string $passphrase
     *
     * @throws RuntimeException If the private key couldn't be read.
     *
     * @return string
     */
    public function readPrivateKey(string $path, string $passphrase = ''): string
    {
        $resource = openssl_pkey_get_private('file://' . $path, $passphrase);
        if (empty($resource)) {
            throw new RuntimeException(sprintf("Could not read private key-file at path: '%s'", $path));
        }
        openssl_pkey_export($resource, $content);
        openssl_pkey_free($resource);

        return $content;
    }

    /**
     * Read a certificate from the specified path.
     *
     * @param string $path
     *
     * @throws RuntimeException If the certificate couldn't be read.
     *
     * @return string
     */
    public function readCertificate(string $path): string
    {
        $resource = openssl_x509_read('file://' . $path);
        if (empty($resource)) {
            throw new RuntimeException(sprintf("Could not read certificate-file at path: '%s'", $path));
        }
        openssl_x509_export($resource, $content);
        openssl_x509_free($resource);

        return $content;
    }

    /**
     * Magic property getter.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        $method = 'get' . studly_case($name);
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return array_get($this->config['settings'], $name);
    }

    /**
     * Magic check for variable existance.
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return array_has($this->config['settings'], $name);
    }

    /**
     * Magic method getter.
     *
     * @param string $name
     * @param array  $args
     *
     * @return mixed
     */
    public function __call(string $name, array $args)
    {
        if (starts_with($name, 'get')) {
            $property = snake_case(substr($name, 3));
            if (isset($this->$property)) {
                return array_get($this->config['settings'], $property);
            }
        }

        trigger_error('Call to undefined method ' . static::class . '::' . $name . '()', E_USER_ERROR);
    }
}
