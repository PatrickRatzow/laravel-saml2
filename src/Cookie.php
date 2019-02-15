<?php

namespace Aacotroneo\Saml2;

use Illuminate\Support\Facades\Cookie as CookieFacade;

class Cookie
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
    }

    /**
     * Set cookie.
     *
     * @param string|null $slug    Service Provider slug.
     * @param array       $data    Serializable data.
     * @param int         $minutes Cookie lifetime.
     *
     * @return void
     */
    public function set(?string $slug, array $data, int $minutes = 0): void
    {
        CookieFacade::queue($this->config->resolveSlugToken($slug), serialize($data), $minutes);
    }

    /**
     * Get cookie - optionally a specific key if provided.
     *
     * @param string|null $slug    Service Provider slug.
     * @param string|null $key     Data key to retrieve.
     * @param mixed       $default Default key value.
     *
     * @return mixed
     */
    public function get(?string $slug, string $key = null, $default = null)
    {
        $cookie = CookieFacade::get($this->config->resolveSlugToken($slug));
        $data = is_string($cookie) ? unserialize($cookie) : [];

        return $key ? ($data[$key] ?? $default) : $data;
    }

    /**
     * Whether cookie exist.
     *
     * @param stringünull $slug Service Provider slug.
     *
     * @return bool
     */
    public function has(?string $slug): bool
    {
        return CookieFacade::has($this->config->resolveSlugToken($slug));
    }

    /**
     * Forget cookie.
     *
     * @param stringünull $slug Service Provider slug.
     *
     * @return void
     */
    public function forget(?string $slug): void
    {
        CookieFacade::forget($this->config->resolveSlugToken($slug));
    }
}
