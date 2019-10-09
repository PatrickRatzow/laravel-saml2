<?php

namespace Aacotroneo\Saml2\Events;

use Aacotroneo\Saml2\User;

class LoginEvent extends SamlEvent
{
    /**
     * Saml2 user instance.
     *
     * @var \Aacotroneo\Saml2\User
     */
    protected $user;

    /**
     * Constructor.
     *
     * @param string|null            $slug       Service Provider slug.
     * @param string                 $message_id Last message ID.
     * @param \Aacotroneo\Saml2\User $user       Saml2 user instance.
     *
     * @return void
     */
    public function __construct(?string $slug, string $message_id, User $user)
    {
        parent::__construct($slug, $message_id);

        $this->user = $user;
    }

    /**
     * Get Saml2 user.
     *
     * @return \Aacotroneo\Saml2\User
     */
    public function getUser(): User
    {
        return $this->user;
    }
}
