<?php

namespace Aacotroneo\Saml2\Models;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

use OneLogin\Saml2\Auth;

/**
 * @property-read array $attributes
 * @property-read array $attributes_with_friendly_name
 * @property-read bool $is_authenticated
 * @property-read string $last_assertion_id
 * @property-read int $last_assertion_not_on_or_after
 * @property-read string $last_message_id
 * @property-read string|null $last_response_xml
 * @property-read string $name_id
 * @property-read string $name_id_format
 * @property-read string $name_id_name_qualifier
 * @property-read string $name_id_sp_name_qualifier
 * @property-read int|null $session_expiration
 * @property-read string|null $session_index
 * @method array|null getAttribute()
 * @method array getAttributes()
 * @method array getAttributesWithFriendlyName()
 * @method array|null getAttributeWithFriendlyName()
 * @method string getLastAssertionId()
 * @method int getLastAssertionNotOnOrAfter()
 * @method string getLastMessageId()
 * @method string|null getLastResponseXML()
 * @method string getNameId()
 * @method string getNameIdFormat()
 * @method string getNameIdNameQualifier()
 * @method string getNameIdSPNameQualifier()
 * @method int|null getSessionExpiration()
 * @method string|null getSessionIndex()
 * @method bool isAuthenticated()
 */
class User implements Arrayable, Jsonable, JsonSerializable
{
    /**
     * OneLogin Auth instance.
     *
     * @var \OneLogin\Saml2\Auth
     */
    protected $auth;

    /**
     * Proxy mappings to auth instance.
     *
     * @var array
     */
    protected $proxy = [
        'attributes'                     => 'getAttributes',
        'attributes_with_friendly_name'  => 'getAttributesWithFriendlyName',
        'is_authenticated'               => 'isAuthenticated',
        'last_assertion_id'              => 'getLastAssertionId',
        'last_assertion_not_on_or_after' => 'getLastAssertionNotOnOrAfter',
        'last_message_id'                => 'getLastMessageId',
        'last_response_xml'              => 'getLastResponseXML',
        'name_id'                        => 'getNameId',
        'name_id_format'                 => 'getNameIdFormat',
        'name_id_name_qualifier'         => 'getNameIdNameQualifier',
        'name_id_sp_name_qualifier'      => 'getNameIdSPNameQualifier',
        'session_expiration'             => 'getSessionExpiration',
        'session_index'                  => 'getSessionIndex',
    ];

    /**
     * Mapped properties.
     *
     * @var array
     */
    protected $mapped = [];

    /**
     * Constructor.
     *
     * @param \OneLogin\Saml2\Auth $auth OneLogin Auth instance.
     *
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Get OneLogin Auth instance.
     *
     * @return \OneLogin\Saml2\Auth
     */
    public function getAuth(): Auth
    {
        return $this->auth;
    }

    /**
     * Map SAML attributes to custom properties.
     *
     * @param array $map List similiar to ['email' => 'urn:oid:0.9.2342.19200300.100.1.3'].
     *
     * @return array Mapped values with keys kept as provided.
     */
    public function mapAttributes(array $map): array
    {
        foreach ($map as $key => &$value) {
            $value = $this->getAttribute($value);
        }

        return $this->mapped = $map;
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
        if (array_has($this->proxy, $name)) {
            return call_user_func([$this->auth, $this->proxy[$name]]);
        }

        return array_get($this->mapped, $name);
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
        return array_has($this->proxy, $name) || array_has($this->mapped, $name);
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
        if (in_array($name, $this->proxy) || in_array($name, ['getAttribute', 'getAttributeWithFriendlyName'])) {
            return call_user_func_array([$this->auth, $name], $args);
        }

        trigger_error('Call to undefined method ' . static::class . '::' . $name . '()', E_USER_ERROR);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return collect($this->proxy)
            ->map(function (string $method) {
                return call_user_func([$this->auth, $method]);
            })
            ->toArray();
    }

    /**
     * Get the data as JSON.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Convert the data into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
