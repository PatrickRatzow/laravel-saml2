<?php

Route::group([
    'as'         => 'saml2.',
    'prefix'     => Saml2::config()->routes_prefix,
    'middleware' => Saml2::config()->routes_middleware,
], function () {
    $controller = Saml2::config()->routes_controller;

    Route::get('login/{name?}', $controller . '@login')->name('login');
    Route::get('logout/{name?}', $controller . '@logout')->name('logout');
    Route::get('metadata/{name?}', $controller . '@metadata')->name('metadata');
    Route::get('sls/{name?}', $controller . '@sls')->name('sls');
    Route::post('acs/{name?}', $controller . '@acs')->name('acs');
});
