<?php

namespace Aacotroneo\Saml2\Exceptions;

class FileNotReadableException extends Exception
{
    /**
     * File path.
     *
     * @var string
     */
    protected $path;

    /**
     * Constructor.
     *
     * @param string $path File path.
     *
     * @return void
     */
    public function __construct(string $path)
    {
        parent::__construct('Could not read file at path: ' . $path);

        $this->path = $path;
    }

    /**
     * Get file path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
