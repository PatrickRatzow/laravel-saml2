<?php

namespace Aacotroneo\Saml2\Events;

// use Aacotroneo\Saml2\Saml2User;
// use Aacotroneo\Saml2\Saml2Auth;

class Saml2LoginEvent
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

// class Saml2LoginEvent {
//
//     protected $user;
//     protected $auth;
//
//     function __construct(Saml2User $user, Saml2Auth $auth)
//     {
//         $this->user = $user;
//         $this->auth = $auth;
//     }
//
//     public function getSaml2User()
//     {
//         return $this->user;
//     }
//
//     public function getSaml2Auth()
//     {
//         return $this->auth;
//     }
//
// }
