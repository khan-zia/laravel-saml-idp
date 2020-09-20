<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'api/saml',
    'namespace' => '\ZiaKhan\SamlIdp\Http\Controllers',
    'middleware' => ['auth:api', 'scope:user-access']
], function () {

    Route::post('create-client', 'SamlClientController@store');
    Route::get('sso', 'SamlClientController@sso');
    Route::get('sso-clients', 'SamlClientController@getSamlClients');
});
