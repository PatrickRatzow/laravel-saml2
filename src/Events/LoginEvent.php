<?php

namespace Aacotroneo\Saml2\Events;

use Aacotroneo\Saml2\User;

class LoginEvent
{
    /**
     * Service Provider slug.
     *
     * @var string|null
     */
    protected $slug;

    /**
     * Last message ID.
     *
     * @var string
     */
    protected $message_id;

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
        $this->slug = $slug;
        $this->message_id = $message_id;
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
     * Get last message ID.
     *
     * @return string
     */
    public function getMessageId(): string
    {
        return $this->message_id;
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
