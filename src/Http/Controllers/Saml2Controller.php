<?php

namespace Aacotroneo\Saml2\Http\Controllers;

use Closure;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Aacotroneo\Saml2\Exceptions\ProviderNotFoundException;
use Aacotroneo\Saml2\Facades\Saml2;

class Saml2Controller extends Controller
{
    /**
     * Config instance.
     *
     * @var \Aacotroneo\Saml2\Config
     */
    protected $config;

    /**
     * Cookie instance.
     *
     * @var \Aacotroneo\Saml2\Cookie
     */
    protected $cookie;

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->config = Saml2::config();
        $this->cookie = Saml2::cookie();
    }

    /**
     * Handle incoming SAML2 assertion request.
     *
     * @param \Illuminate\Http\Request $request Request instance.
     * @param string|null              $slug    Service Provider slug.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function acs(Request $request, string $slug = null): RedirectResponse
    {
        return $this->rescue(function () use ($request, $slug) {
            $user = Saml2::acs($slug, $request->input('requestId'));

            $intended = $request->input('RelayState');
            if (empty($intended) || url()->full() === $intended) {
                $intended = $this->config->route_login;
            }

            // Save name ID and session index to cookie.
            $data = Arr::only($user->toArray(), ['name_id', 'session_index']);
            $this->cookie->set($slug, $data);

            return redirect($intended);
        }, $this->config->route_error);
    }

    /**
     * Initiate login request through Single Sign-On service.
     *
     * @param \Illuminate\Http\Request $request Request instance.
     * @param string|null              $slug    Service Provider slug.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request, string $slug = null): RedirectResponse
    {
        return $this->rescue(function () use ($slug) {
            // We want to handle the redirect ourselves.
            $url = Saml2::login($slug, $this->config->route_login, [], false, false, true, true);

            return redirect($url);
        });
    }

    /**
     * Initiate logout request through Single Logout service.
     *
     * @param \Illuminate\Http\Request $request Request instance.
     * @param string|null              $slug    Service Provider slug.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request, string $slug = null): RedirectResponse
    {
        return $this->rescue(function () use ($request, $slug) {
            $nameId = $request->input('nameId') ?: $this->cookie->get($slug, 'name_id');
            $returnTo = $request->input('returnTo') ?: $this->config->route_logout;
            $sessionIndex = $request->input('sessionIndex') ?: $this->cookie->get($slug, 'session_index');
            // We want to handle the redirect ourselves.
            // We should end up in the 'sls' endpoint.
            $url = Saml2::logout($slug, $returnTo, [], $nameId, $sessionIndex, true, null, null, null);

            return redirect($url);
        });
    }

    /**
     * Generate Service Provider metadata.
     *
     * @param \Illuminate\Http\Request $request Request instance.
     * @param string|null              $slug    Service Provider slug.
     *
     * @return \Illuminate\Http\Response
     */
    public function metadata(Request $request, string $slug = null): Response
    {
        return $this->rescue(function () use ($slug) {
            $metadata = Saml2::metadata($slug);

            return response($metadata, 200, ['Content-Type' => 'text/xml']);
        });
    }

    /**
     * Handle incoming Single Logout request.
     *
     * @param \Illuminate\Http\Request $request Request instance.
     * @param string|null              $slug    Service Provider slug.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sls(Request $request, string $slug = null): RedirectResponse
    {
        return $this->rescue(function () use ($request, $slug) {
            $requestId = $request->input('requestId');
            // We want to handle the redirect ourselves.
            $url = Saml2::sls($slug, false, $requestId, $this->config->params_from_server, true);

            // Forget session cookie.
            $this->cookie->forget($slug);

            return redirect($url ?: $this->config->route_logout);
        });
    }

    /**
     * Capture any exceptions and return them as an HTTP error.
     * If $errorPath is provided, the user will be redirected to that URL instead,
     * and the error will be flashed in session as 'saml2_error'.
     *
     * @param \Closure    $callback
     * @param string|null $errorPath
     *
     * @return mixed
     */
    protected function rescue(Closure $callback, string $errorPath = null)
    {
        try {
            return $callback();
        } catch (Exception $e) {
            report($e);
            if (empty($errorPath)) {
                $message = sprintf('[%d] %s', $e->getCode(), $e->getMessage());
                $status = $e instanceof ProviderNotFoundException ? 404 : 422;
                abort($status, $message);
            }
            session()->flash('saml2_error', $e->getMessage());
        }

        return redirect($errorPath);
    }
}
