<?php

Route::group([
    'as'         => 'saml2.',
    'prefix'     => Saml2::config()->routes_prefix,
    'middleware' => Saml2::config()->routes_middleware,
], function () {
    $controller = Saml2::config()->routes_controller;

    Route::post('acs/{slug?}', $controller . '@acs')->name('acs');
    Route::get('login/{slug?}', $controller . '@login')->name('login');
    Route::get('logout/{slug?}', $controller . '@logout')->name('logout');
    Route::get('metadata/{slug?}', $controller . '@metadata')->name('metadata');
    Route::get('sls/{slug?}', $controller . '@sls')->name('sls');
});
