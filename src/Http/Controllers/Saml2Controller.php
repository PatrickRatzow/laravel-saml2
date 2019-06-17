<?php

namespace Aacotroneo\Saml2\Http\Controllers;

use Closure;
use Exception;
use Illuminate\Http\JsonResponse;
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
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->config = Saml2::config();
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
            Saml2::acs($slug, $request->input('requestId'));

            $intended = $request->input('RelayState');
            if (empty($intended) || url()->full() === $intended) {
                $intended = $this->config->route_login;
            }

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
        return $this->rescue(function () use ($request, $slug) {
            $return_to = $request->input('returnTo') ?: $this->config->route_login;
            $force_authn = filter_var($request->input('forceAuthn'), FILTER_VALIDATE_BOOLEAN);
            $is_passive = filter_var($request->input('passive'), FILTER_VALIDATE_BOOLEAN);
            // We want to handle the redirect ourselves.
            $url = Saml2::login($slug, $return_to, [], $force_authn, $is_passive, true, true);

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
            $name_id = $request->input('nameId');
            $return_to = $request->input('returnTo') ?: $this->config->route_logout;
            $session_index = $request->input('sessionIndex');
            // We want to handle the redirect ourselves.
            // We should end up in the 'sls' endpoint.
            $url = Saml2::logout($slug, $return_to, [], $name_id, $session_index, true, null, null, null);

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
     * List Service Provider routes.
     *
     * @param \Illuminate\Http\Request $request Request instance.
     * @param string|null              $slug    Service Provider slug.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function routes(Request $request, string $slug = null): JsonResponse
    {
        return $this->rescue(function () use ($slug) {
            $slug = Saml2::config()->resolveOneLoginSlugOrFail($slug);
            $routes = [
                'acs'      => route('saml2.acs', $slug),
                'login'    => route('saml2.login', $slug),
                'logout'   => route('saml2.logout', $slug),
                'metadata' => route('saml2.metadata', $slug),
                'sls'      => route('saml2.sls', $slug),
            ];

            return response()->json($routes);
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
            $request_id = $request->input('requestId');
            // We want to handle the redirect ourselves.
            $url = Saml2::sls($slug, false, $request_id, $this->config->params_from_server, true);

            return redirect($url ?: $this->config->route_logout);
        });
    }

    /**
     * Capture any exceptions and return them as an HTTP error.
     * If $error_path is provided, the user will be redirected to that URL instead,
     * and the error will be flashed in session as 'saml2_error'.
     *
     * @param \Closure    $callback
     * @param string|null $error_path
     *
     * @return mixed
     */
    protected function rescue(Closure $callback, string $error_path = null)
    {
        try {
            return $callback();
        } catch (Exception $e) {
            report($e);
            if (empty($error_path)) {
                $message = sprintf('[%d] %s', $e->getCode(), $e->getMessage());
                $status = $e instanceof ProviderNotFoundException ? 404 : 422;
                abort($status, $message);
            }
            session()->flash('saml2_error', $e->getMessage());
        }

        return redirect($error_path);
    }
}
