<?php

namespace Aacotroneo\Saml2;

use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Error;
use OneLogin\Saml2\Utils;
use Aacotroneo\Saml2\Events\LoginEvent;
use Aacotroneo\Saml2\Events\LogoutEvent;
use Aacotroneo\Saml2\Exceptions\Exception;

class Saml2
{
    /**
     * Config instance.
     *
     * @var \Aacotroneo\Saml2\Config
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param \Aacotroneo\Saml2\Config $config
     *
     * @return void
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        // Should we enable proxy vars?
        if ($this->config->proxy_vars) {
            Utils::setProxyVars(true);
        }
    }

    /**
     * Get config instance.
     *
     * @return \Aacotroneo\Saml2\Config
     */
    public function config(): Config
    {
        return $this->config;
    }

    /**
     * Process Assertion Consumer Services response from Identity Provider.
     *
     * @param string|null $slug       Service Provider slug.
     * @param string|null $request_id The ID of the AuthNRequest sent by this SP to the IdP.
     *
     * @throws \Aacotroneo\Saml2\Exceptions\Exception On any error.
     *
     * @return \Aacotroneo\Saml2\User
     */
    public function acs(string $slug = null, string $request_id = null): User
    {
        $auth = $this->loadAuth($slug);
        $auth->processResponse($request_id);
        $error_exception = $auth->getLastErrorException();
        if (!empty($error_exception)) {
            throw new Exception($auth->getLastErrorReason(), 0, $error_exception);
        }

        $user = new User($auth);
        event(new LoginEvent($this->config->resolveOneLoginSlug($slug), $auth->getLastMessageId(), $user));

        return $user;
    }

    /**
     * Initiate the Single Sign-On process.
     *
     * @param string|null $slug               Service Provider slug.
     * @param string|null $return_to          The target URL the user should be returned to after login.
     * @param array       $parameters         Extra parameters to be added to the GET request.
     * @param bool        $force_authn        When TRUE the AuthNRequest will set the ForceAuthn='true'.
     * @param bool        $is_passive         When TRUE the AuthNRequest will set the IsPassive='true'.
     * @param bool        $stay               TRUE to stay (returns the URL string), FALSE to redirect.
     * @param bool        $set_name_id_policy When TRUE the AuthNRequest will set a NameIDPolicy element.
     *
     * @return string|null If $stay is TRUE, a string with the SSO URL + LogoutRequest + parameters is returned instead.
     */
    public function login(
        string $slug = null,
        string $return_to = null,
        array $parameters = [],
        bool $force_authn = false,
        bool $is_passive = false,
        bool $stay = false,
        bool $set_name_id_policy = true
    ): ?string {
        return $this->loadAuth($slug)->login(
            $return_to,
            $parameters,
            $force_authn,
            $is_passive,
            $stay,
            $set_name_id_policy
        );
    }

    /**
     * Initiate the Single Logout process.
     *
     * @param string|null $slug                      Service Provider slug.
     * @param string|null $return_to                 The target URL the user should be returned to after logout.
     * @param array       $parameters                Extra parameters to be added to the GET.
     * @param string|null $name_id                   The NameID that will be set in the LogoutRequest.
     * @param string|null $session_index             The SessionIndex (taken from the SAML Response in the SSO process).
     * @param bool        $stay                      TRUE to stay (returns the URL string), FALSE to redirect.
     * @param string|null $name_id_format            The NameID Format will be set in the LogoutRequest.
     * @param string|null $name_id_name_qualifier    The NameID NameQualifier will be set in the LogoutRequest.
     * @param string|null $name_id_sp_name_qualifier The NameID SP NameQualifier will be set in the LogoutRequest.
     *
     * @throws \OneLogin\Saml2\Error If Identity Provider doesn't support Single Logout.
     *
     * @return string|null If $stay is TRUE, a string with the SLO URL + LogoutRequest + parameters is returned instead.
     */
    public function logout(
        string $slug = null,
        string $return_to = null,
        array $parameters = [],
        string $name_id = null,
        string $session_index = null,
        bool $stay = false,
        string $name_id_format = null,
        string $name_id_name_qualifier = null,
        string $name_id_sp_name_qualifier = null
    ): ?string {
        return $this->loadAuth($slug)->logout(
            $return_to,
            $parameters,
            $name_id,
            $session_index,
            $stay,
            $name_id_format,
            $name_id_name_qualifier,
            $name_id_sp_name_qualifier
        );
    }

    /**
     * Get metadata for the specified Service Provider.
     *
     * @param string|null $slug Service Provider slug.
     *
     * @throws \OneLogin\Saml2\Error If metadata validation fails.
     *
     * @return string Metadata XML string.
     */
    public function metadata(string $slug = null): string
    {
        $settings = $this->loadAuth($slug)->getSettings();
        $metadata = $settings->getSPMetadata();
        $errors = $settings->validateMetadata($metadata);

        if (empty($errors)) {
            return $metadata;
        }

        throw new Error('Invalid Service Provider metadata: %s', Error::METADATA_SP_INVALID, [implode(', ', $errors)]);
    }

    /**
     * Process Single Logout request/response.
     *
     * @param  string|null $slug               Service Provider slug.
     * @param  bool        $keep_local_session When FALSE will destroy the local session, otherwise will keep it
     * @param  string|null $request_id         The ID of the LogoutRequest sent by this SP to the IdP.
     * @param  bool        $params_from_server TRUE to use parameters from $_SERVER to validate the signature.
     * @param  bool        $stay               TRUE to stay (returns the URL string), FALSE to redirect.
     *
     * @throws \OneLogin\Saml2\Error                  If SAML LogoutRequest/LogoutResponse wasn't found.
     * @throws \Aacotroneo\Saml2\Exceptions\Exception On any other error.
     *
     * @return string|null If $stay is TRUE, a string with the SLO URL + LogoutRequest + parameters is returned instead.
     */
    public function sls(
        string $slug = null,
        bool $keep_local_session = false,
        string $request_id = null,
        bool $params_from_server = false,
        bool $stay = false
    ): ?string {
        $success = false;
        $auth = $this->loadAuth($slug);
        $url = $auth->processSLO(
            $keep_local_session,
            $request_id,
            $params_from_server,
            function () use (&$success) {
                $success = true;
            },
            $stay
        );
        $error_exception = $auth->getLastErrorException();
        if (!empty($error_exception)) {
            throw new Exception($auth->getLastErrorReason(), 0, $error_exception);
        }
        if ($success) {
            event(new LogoutEvent($this->config->resolveOneLoginSlug($slug), $auth->getLastMessageId()));
        }

        return $url;
    }

    /**
     * Load OneLogin Auth instance for a specific Service Provider.
     *
     * @param string|null $slug Service Provider slug.
     *
     * @return \OneLogin\Saml2\Auth
     */
    public function loadAuth(string $slug = null): Auth
    {
        return new Auth($this->config->getOneLogin($slug));
    }
}
