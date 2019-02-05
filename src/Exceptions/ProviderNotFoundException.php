<?php

namespace Aacotroneo\Saml2\Exceptions;

class ProviderNotFoundException extends Exception
{
    /**
     * Provider slug.
     *
     * @var string|null
     */
    protected $slug;

    /**
     * Constructor.
     *
     * @param string|null $slug Provider slug.
     *
     * @return void
     */
    public function __construct(?string $slug)
    {
        parent::__construct('Invalid slug: ' . ($slug ?: 'null'));

        $this->slug = $slug;
    }

    /**
     * Get provider slug.
     *
     * @return string|null
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }
}
