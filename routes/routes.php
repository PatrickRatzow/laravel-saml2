<?php

Route::group([
    'as'         => 'saml2.',
    'prefix'     => Saml2::config()->routes_prefix,
    'middleware' => Saml2::config()->routes_middleware,
], function () {
    // Grab config.
    $config = Saml2::config();
    $controller = $config->routes_controller;

    Route::post('acs/{slug?}', $controller . '@acs')->name('acs');
    Route::get('login/{slug?}', $controller . '@login')->middleware($config->route_login_middleware)->name('login');
    Route::get('logout/{slug?}', $controller . '@logout')->middleware($config->route_logout_middleware)->name('logout');
    Route::get('metadata/{slug?}', $controller . '@metadata')->name('metadata');
    Route::get('routes/{slug?}', $controller . '@routes')->name('routes');
    Route::get('sls/{slug?}', $controller . '@sls')->name('sls');
});
