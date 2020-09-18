<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'api/saml',
    'namespace' => '\ZiaKhan\SamlIdp\Http\Controllers',
    'middleware' => ['auth:api', 'scope:user-access']
], function () {
    Route::post('create-client', 'SamlClientController@store');
    Route::get('sso', 'SamlClientController@sso');

    // Route::post('user/new-device', 'UserDeviceController@store');

    // Route::put('user/information', 'UserInfoController@update');

    // Route::delete('user/device/{id}', 'UserDeviceController@delete');
});
