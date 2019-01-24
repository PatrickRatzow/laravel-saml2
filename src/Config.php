<?php

namespace Aacotroneo\Saml2;

use Aacotroneo\Saml2\Http\Controllers\Saml2Controller;

class Config
{
    /**
     * Configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param array $config
     *
     * @return void
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get routes controller, with fallback to package controller.
     *
     * @return string
     */
    public function getRoutesController(): string
    {
        return array_get($this->config['setting'], 'routes_controller', Saml2Controller::class);
    }

    /**
     * Magic property getter.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        $method = 'get' . studly_case($name);
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return array_get($this->config['settings'], $name);
    }

    /**
     * Magic check for variable existance.
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return array_has($this->config['settings'], $name);
    }

    /**
     * Magic method getter.
     *
     * @param string $name
     * @param array  $args
     *
     * @return mixed
     */
    public function __call(string $name, array $args)
    {
        if (starts_with($name, 'get')) {
            $property = snake_case(substr($name, 3));
            if (isset($this->$property)) {
                return array_get($this->config['settings'], $property);
            }
        }

        trigger_error('Call to undefined method ' . static::class . '::' . $name . '()', E_USER_ERROR);
    }
}
