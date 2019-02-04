<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Package settings
    |--------------------------------------------------------------------------
    */

    // If 'setup_routes' is set to TRUE, the package defines five new routes:
    //
    // Method | URI                             | Name
    // -------|---------------------------------|----------------
    // POST   | {route_prefix}/acs/{slug?}      | saml2.acs
    // GET    | {route_prefix}/login/{slug?}    | saml2.login
    // GET    | {route_prefix}/logout/{slug?}   | saml2.logout
    // GET    | {route_prefix}/metadata/{slug?} | saml2.metadata
    // GET    | {route_prefix}/sls/{slug?}      | saml2.sls
    'setup_routes' => true,

    // The prefix used in all package routes.
    'routes_prefix' => 'saml2',

    // Defaults to the controller provided by the package.
    'routes_controller' => null,

    // What middleware should be applied to the routes.
    'routes_middleware' => [],

    // Where to redirect users after login.
    // Route name is assumed, unless it contains a slash (/).
    'route_login' => '/',

    // Where to redirect users after logout.
    // Route name is assumed, unless it contains a slash (/).
    'route_logout' => '/',

    // Where to redirect users on errors.
    // Route name is assumed, unless it contains a slash (/).
    'route_error' => '/',

    // Indicates how the parameters will be
    // retrieved from the SLS request for signature validation.
    'params_from_server' => false,

    // If 'proxy_vars' is TRUE, then the Saml lib will trust proxy headers
    // e.g X-Forwarded-Proto / HTTP_X_FORWARDED_PROTO. This is useful if
    // your application is running behind a load balancer which terminates SSL.
    'proxy_vars' => false,

    // Whether the system should use test providers.
    'test' => env('APP_ENV') !== 'production',

];
