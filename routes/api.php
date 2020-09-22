<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'api/saml',
    'namespace' => '\ZiaKhan\SamlIdp\Http\Controllers',
    'middleware' => ['auth:api', 'scope:user-access']
], function () {

    Route::post('client', 'SamlClientController@store');
    Route::get('clients', 'SamlClientController@getSamlClients');
    Route::delete('client/{client_id}', 'SamlClientController@delete');
    Route::put('enable-client/{client_id}', 'SamlClientController@enable');
    Route::put('disable-client/{client_id}', 'SamlClientController@disable');

    // Completes SSO process for HTTP-Post bindings
    Route::get('sso', 'SamlClientController@sso');
});
