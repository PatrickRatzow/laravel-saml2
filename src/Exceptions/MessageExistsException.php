<?php

namespace Aacotroneo\Saml2\Exceptions;

class MessageExistsException extends Exception
{
    /**
     * Provider slug.
     *
     * @var string|null
     */
    protected $slug;

    /**
     * Message ID.
     *
     * @var string
     */
    protected $message_id;

    /**
     * Constructor.
     *
     * @param string $slug       Service Provider slug.
     * @param string $message_id Message ID.
     *
     * @return void
     */
    public function __construct(string $slug, string $message_id)
    {
        parent::__construct(sprintf("Message with slug '%s' and message ID '%s' already exists", $slug, $message_id));

        $this->slug = $slug;
        $this->message_id = $message_id;
    }

    /**
     * Get Service Provider slug.
     *
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * Get message ID.
     *
     * @return string
     */
    public function getMessageId(): string
    {
        return $this->message_id;
    }
}
