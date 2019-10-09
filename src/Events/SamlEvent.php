<?php

namespace Aacotroneo\Saml2\Events;

class SamlEvent
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
     * Constructor.
     *
     * @param string|null $slug       Service Provider slug.
     * @param string      $message_id Last message ID.
     *
     * @return void
     */
    public function __construct(?string $slug, string $message_id)
    {
        $this->slug = $slug;
        $this->message_id = $message_id;
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
}
