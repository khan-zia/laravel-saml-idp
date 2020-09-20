<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'idp/saml',
    'namespace' => '\ZiaKhan\SamlIdp\Http\Controllers',
    'middleware' => 'web'
], function () {
    Route::get('sso-post/{token}', 'SsoController@httpPost');

    // Route::resource('metadata', 'MetadataController')->only('index');
    // Route::resource('logout', 'LogoutController')->only('index');
});
