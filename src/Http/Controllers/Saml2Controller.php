<?php

namespace Aacotroneo\Saml2\Http\Controllers;

use Closure;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use OutOfRangeException;
use Aacotroneo\Saml2\Facades\Saml2;

class Saml2Controller extends Controller
{
    /**
     * Handle incoming SAML2 assertion request.
     *
     * @param \Illuminate\Http\Request $request
     * @param string|null              $name
     *
     * @return \Illuminate\Http\Response
     */
    public function acs(Request $request, string $name = null): Response
    {
        return $this->rescue(function () use ($name) {
            Saml2::acs($name, $request->input('requestId'));

            $intended = $request->input('RelayState');
            if (empty($intended) || url()->full() === $intended) {
                $intended = Saml2::config()->route_login;
            }

            return redirect($intended);
        }, Saml2::config()->route_error);
    }

    /**
     * Initiate login request through Single Sign-On service.
     *
     * @param \Illuminate\Http\Request $request
     * @param string|null              $name
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request, string $name = null): RedirectResponse
    {
        return $this->rescue(function () use ($name) {
            // We want to handle the redirect ourselves.
            $url = Saml2::login($name, Saml2::config()->route_login, [], false, false, true, true);

            return redirect($url);
        });
    }

    /**
     * Initiate logout request through Single Logout service.
     *
     * @param \Illuminate\Http\Request $request
     * @param string|null              $name
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request, string $name = null): RedirectResponse
    {
        return $this->rescue(function () use ($request, $name) {
            $nameId = $request->input('nameId');
            $returnTo = $request->input('returnTo');
            $sessionIndex = $request->input('sessionIndex');
            // We want to handle the redirect ourselves.
            // We should end up in the 'sls' endpoint.
            $url = Saml2::logout($name, $returnTo, [], $nameId, $sessionIndex, true, null, null, null);

            return redirect($url);
        });
    }

    /**
     * Generate Service Provider metadata.
     *
     * @param \Illuminate\Http\Request $request
     * @param string|null              $name
     *
     * @return \Illuminate\Http\Response
     */
    public function metadata(Request $request, string $name = null): Response
    {
        return $this->rescue(function () use ($name) {
            $metadata = Saml2::metadata($name);

            return response($metadata, 200, ['Content-Type' => 'text/xml']);
        });
    }

    /**
     * Handle incoming Single Logout request.
     *
     * @param \Illuminate\Http\Request $request
     * @param string|null              $name
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sls(Request $request, string $name = null): RedirectResponse
    {
        return $this->rescue(function () use ($request, $name) {
            $requestId = $request->input('requestId');
            // We want to handle the redirect ourselves.
            $url = Saml2::sls($name, false, $requestId, Saml2::config()->params_from_server, true);

            return redirect($url ?: Saml2::config()->route_logout);
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
                $status = $e instanceof OutOfRangeException ? 404 : 422;
                abort($status, $message);
            }
            session()->flash('saml2_error', $e->getMessage());
        }

        return redirect($errorPath);
    }
}
