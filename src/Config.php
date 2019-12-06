<?php

namespace Aacotroneo\Saml2;

use Illuminate\Support\Arr;
use Aacotroneo\Saml2\Exceptions\FileNotReadableException;
use Aacotroneo\Saml2\Exceptions\IdentityProviderNotFoundException;
use Aacotroneo\Saml2\Exceptions\ServiceProviderNotFoundException;
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
     * @param array $config Combined saml2 configuration.
     *
     * @return void
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get login redirect route.
     *
     * @return string|null
     */
    public function getRouteLogin(): ?string
    {
        return $this->formatUrl(array_get($this->config['settings'], 'route_login'));
    }

    /**
     * Get logout redirect route.
     *
     * @return string|null
     */
    public function getRouteLogout(): ?string
    {
        return $this->formatUrl(array_get($this->config['settings'], 'route_logout'));
    }

    /**
     * Get error redirect route.
     *
     * @return string|null
     */
    public function getRouteError(): ?string
    {
        return $this->formatUrl(array_get($this->config['settings'], 'route_error'));
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
     * Get OneLogin configuration for the provided Service Provider slug.
     *
     * @param string|null $slug Service Provider slug.
     *
     * @throws \Aacotroneo\Saml2\Exceptions\ServiceProviderNotFoundException
     *     If no Service Provider was found with the specified slug.
     * @throws \Aacotroneo\Saml2\Exceptions\IdentityProviderNotFoundException
     *     If no Identity Provider was found with the specified slug.
     *
     * @return array
     */
    public function getOneLogin(string $slug = null): array
    {
        // Resolve slug.
        $slug = $this->resolveOneLoginSlugOrFail($slug);

        // Grab and process providers.
        $group = $this->test ? 'test' : 'prod';
        $sp = $this->config['sps'][$group][$slug];
        $idp = $this->config['idps'][$group][$slug];

        if (empty($sp['entityId'])) {
            $sp['entityId'] = route('saml2.metadata', compact('slug'));
        }
        if (empty($sp['assertionConsumerService']['url'])) {
            $sp['assertionConsumerService']['url'] = route('saml2.acs', compact('slug'));
        }
        if (!empty($sp['singleLogoutService']) && empty($sp['singleLogoutService']['url'])) {
            $sp['singleLogoutService']['url'] = route('saml2.sls', compact('slug'));
        }
        if (is_readable($sp['privateKey'])) {
            $sp['privateKey'] = $this->readPrivateKey($sp['privateKey'], $sp['passphrase'] ?? '');
        }
        if (is_readable($sp['x509cert'])) {
            $sp['x509cert'] = $this->readCertificate($sp['x509cert']);
        }
        if (isset($idp['x509cert']) && is_readable($idp['x509cert'])) {
            $idp['x509cert'] = $this->readCertificate($idp['x509cert']);
        }
        if (isset($idp['x509certMulti']['signing'], $idp['x509certMulti']['encryption'])) {
            $idp['x509certMulti']['signing'] = Arr::wrap($idp['x509certMulti']['signing']);
            $idp['x509certMulti']['encryption'] = Arr::wrap($idp['x509certMulti']['encryption']);
            foreach ($idp['x509certMulti'] as &$certificates) {
                $certificates = array_map(function (string $certificate) {
                    return is_readable($certificate) ? $this->readCertificate($certificate) : $certificate;
                }, $certificates);
            }
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

        // Metadata signing.
        if (isset($onelogin['security']['signMetadata']) && is_array($onelogin['security']['signMetadata'])) {
            $sign_metadata = &$onelogin['security']['signMetadata'];
            if (isset($sign_metadata['x509cert'], $sign_metadata['privateKey'])) {
                if (is_readable($sign_metadata['x509cert'])) {
                    $sign_metadata['x509cert'] = $this->readCertificate($sign_metadata['x509cert']);
                }
                if (is_readable($sign_metadata['privateKey'])) {
                    $sign_metadata['privateKey'] = $this->readPrivateKey(
                        $sign_metadata['privateKey'],
                        $sign_metadata['passphrase'] ?? ''
                    );
                    unset($sign_metadata['passphrase']);
                }
            }
        }

        return $onelogin + compact('sp', 'idp');
    }

    /**
     * Resolve to default Service Provider slug if none is provided.
     *
     * @param string|null $slug Service Provider slug.
     *
     * @return string|null Will return NULL if no Service Providers are defined.
     */
    public function resolveOneLoginSlug(string $slug = null): ?string
    {
        if (empty($slug)) {
            $group = $this->test ? 'test' : 'prod';
            $slug = $this->config['sps']['default'] ?? key($this->config['sps'][$group]);
        }

        return $slug;
    }

    /**
     * Resolve to default Service Provider slug if none is provided or fail.
     *
     * @param string|null $slug Service Provider slug.
     *
     * @throws \Aacotroneo\Saml2\Exceptions\ServiceProviderNotFoundException
     *     If no Service Provider was found with the specified slug.
     * @throws \Aacotroneo\Saml2\Exceptions\IdentityProviderNotFoundException
     *     If no Identity Provider was found with the specified slug.
     *
     * @return string
     */
    public function resolveOneLoginSlugOrFail(string $slug = null): ?string
    {
        $group = $this->test ? 'test' : 'prod';
        $sps = $this->config['sps'][$group];
        $idps = $this->config['idps'][$group];
        if (empty($slug)) {
            $slug = $this->config['sps']['default'] ?? key($sps);
        }

        if (!isset($sps[$slug])) {
            throw new ServiceProviderNotFoundException($slug);
        }
        if (!isset($idps[$slug])) {
            throw new IdentityProviderNotFoundException($slug);
        }

        return $slug;
    }

    /**
     * Format an URL depending on whether it's a named route or a specific path.
     *
     * @param string|null $path
     * @param array       $params
     *
     * @return string|null
     */
    public function formatUrl(?string $path, array $params = []): ?string
    {
        if (empty($path)) {
            return null;
        }

        return str_contains($path, '/') ? url($path, $params) : route($path, $params);
    }

    /**
     * Read (and unencrypt) a private key from the specified path.
     *
     * @param string $path
     * @param string $passphrase
     *
     * @throws \Aacotroneo\Saml2\Exceptions\FileNotReadableException If the private key could not be read.
     *
     * @return string
     */
    public function readPrivateKey(string $path, string $passphrase = ''): string
    {
        $resource = openssl_pkey_get_private('file://' . $path, $passphrase);
        if (empty($resource)) {
            throw new FileNotReadableException($path);
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
     * @throws \Aacotroneo\Saml2\Exceptions\FileNotReadableException If the certificate could not be read.
     *
     * @return string
     */
    public function readCertificate(string $path): string
    {
        $resource = openssl_x509_read('file://' . $path);
        if (empty($resource)) {
            throw new FileNotReadableException($path);
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
