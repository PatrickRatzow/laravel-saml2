<?php

namespace Aacotroneo\Saml2\Http\Controllers;

// use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use Aacotroneo\Saml2\Facades\Saml2;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OutOfRangeException;
use Exception;
use Closure;

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
        //
    }

    /**
     * Initiate login request.
     *
     * @param \Illuminate\Http\Request $request
     * @param string|null              $name
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request, string $name = null): Response
    {
        //
    }

    /**
     * Initiate logout request throughout the SSO infrastructure.
     *
     * @param \Illuminate\Http\Request $request
     * @param string|null              $name
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request, string $name = null): Response
    {
        //
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
     * Handle incoming SAML2 logout request.
     *
     * @param \Illuminate\Http\Request $request
     * @param string|null              $name
     *
     * @return \Illuminate\Http\Response
     */
    public function sls(Request $request, string $name = null): Response
    {
        //
    }

    /**
     * Capture any exceptions and return them as an HTTP error.
     *
     * @param Closure $callback
     *
     * @return \Illuminate\Http\Response
     */
    protected function rescue(Closure $callback): Response
    {
        try {
            return $callback();
        } catch (Exception $e) {
            report($e);
            $message = sprintf('[%d] %s', $e->getCode(), $e->getMessage());
            $status = $e instanceof OutOfRangeException ? 404 : 409;
            abort($status, $message);
        }
    }

    // /**
    //  * Process an incoming saml2 assertion request.
    //  * Fires 'Saml2LoginEvent' event if a valid user is Found
    //  */
    // public function acs()
    // {
    //     $errors = $this->saml2Auth->acs();
    //
    //     if (!empty($errors)) {
    //         logger()->error('Saml2 error_detail', ['error' => $this->saml2Auth->getLastErrorReason()]);
    //         session()->flash('saml2_error_detail', [$this->saml2Auth->getLastErrorReason()]);
    //
    //         logger()->error('Saml2 error', $errors);
    //         session()->flash('saml2_error', $errors);
    //         return redirect(config('saml2_settings.errorRoute'));
    //     }
    //     $user = $this->saml2Auth->getSaml2User();
    //
    //     event(new Saml2LoginEvent($user, $this->saml2Auth));
    //
    //     $redirectUrl = $user->getIntendedUrl();
    //
    //     if ($redirectUrl !== null) {
    //         return redirect($redirectUrl);
    //     } else {
    //
    //         return redirect(config('saml2_settings.loginRoute'));
    //     }
    // }
    //
    // /**
    //  * Process an incoming saml2 logout request.
    //  * Fires 'Saml2LogoutEvent' event if its valid.
    //  * This means the user logged out of the SSO infrastructure, you 'should' log him out locally too.
    //  */
    // public function sls()
    // {
    //     $error = $this->saml2Auth->sls(config('saml2_settings.retrieveParametersFromServer'));
    //     if (!empty($error)) {
    //         throw new \Exception("Could not log out");
    //     }
    //
    //     return redirect(config('saml2_settings.logoutRoute')); //may be set a configurable default
    // }
    //
    // /**
    //  * This initiates a logout request across all the SSO infrastructure.
    //  */
    // public function logout(Request $request)
    // {
    //     $returnTo = $request->query('returnTo');
    //     $sessionIndex = $request->query('sessionIndex');
    //     $nameId = $request->query('nameId');
    //     $this->saml2Auth->logout($returnTo, $nameId, $sessionIndex); //will actually end up in the sls endpoint
    //     //does not return
    // }
    //
    //
    // /**
    //  * This initiates a login request
    //  */
    // public function login()
    // {
    //     $this->saml2Auth->login(config('saml2_settings.loginRoute'));
    // }

}
