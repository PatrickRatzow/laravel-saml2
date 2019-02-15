<?php

namespace Aacotroneo\Saml2;

use Illuminate\Support\Facades\Session as SessionFacade;

class Session
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
     * Put session.
     *
     * @param string|null $slug Service Provider slug.
     * @param array       $data Serializable data.
     *
     * @return void
     */
    public function put(?string $slug, array $data): void
    {
        SessionFacade::put($this->config->resolveSlugToken($slug), $data);
    }

    /**
     * Get session - optionally a specific key if provided.
     *
     * @param string|null $slug    Service Provider slug.
     * @param string|null $key     Data key to retrieve.
     * @param mixed       $default Default key value.
     *
     * @return mixed
     */
    public function get(?string $slug, string $key = null, $default = null)
    {
        $data = SessionFacade::get($this->config->resolveSlugToken($slug), []);

        return $key ? ($data[$key] ?? $default) : $data;
    }

    /**
     * Whether session exist.
     *
     * @param stringünull $slug Service Provider slug.
     *
     * @return bool
     */
    public function has(?string $slug): bool
    {
        return SessionFacade::has($this->config->resolveSlugToken($slug));
    }

    /**
     * Forget session.
     *
     * @param stringünull $slug Service Provider slug.
     *
     * @return void
     */
    public function forget(?string $slug): void
    {
        SessionFacade::forget($this->config->resolveSlugToken($slug));
    }
}
