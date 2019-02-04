<?php

namespace Aacotroneo\Saml2\Events;

class LogoutEvent
{
    /**
     * Service Provider slug.
     *
     * @var string|null
     */
    protected $slug;

    /**
     * Constructor.
     *
     * @param string|null $slug Service Provider slug.
     *
     * @return void
     */
    public function __construct(?string $slug)
    {
        $this->slug = $slug;
    }

    /**
     * Get Service Provider slug.
     *
     * @return string|null
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }
}
