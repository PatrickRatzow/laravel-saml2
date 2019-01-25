<?php

namespace Aacotroneo\Saml2\Events;

class Saml2LogoutEvent
{
    /**
     * Service Provider name.
     *
     * @var string|null
     */
    protected $name;

    /**
     * Constructor.
     *
     * @param string|null $name Service Provider name.
     */
    public function __construct(string $name = null)
    {
        $this->name = $name;
    }

    /**
     * Get Service Provider name.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }
}
