<?php

namespace Aacotroneo\Saml2\Events;

use Aacotroneo\Saml2\Models\User;

class LoginEvent
{
    /**
     * Service Provider slug.
     *
     * @var string|null
     */
    protected $slug;

    /**
     * Saml2 user instance.
     *
     * @var \Aacotroneo\Saml2\Models\User
     */
    protected $user;

    /**
     * Constructor.
     *
     * @param string|null                   $slug Service Provider slug.
     * @param \Aacotroneo\Saml2\Models\User $user Saml2 user instance.
     *
     * @return void
     */
    public function __construct(?string $slug, User $user)
    {
        $this->slug = $slug;
        $this->user = $user;
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

    /**
     * Get Saml2 user.
     *
     * @return \Aacotroneo\Saml2\Models\User
     */
    public function getUser(): User
    {
        return $this->user;
    }
}
