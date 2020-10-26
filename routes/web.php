<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'idp/saml',
    'namespace' => '\ZiaKhan\SamlIdp\Http\Controllers',
    'middleware' => 'web'
], function () {
    Route::get('sso-post/{token}', 'SsoController@httpPost');

    // Route::get('json', function () {
    //     echo json_encode(
    //         [
    //             "Meveto.Salesforce.Username" => "zmajrohi323-qsng@force.com"
    //         ]
    //     );
    //     return '';
    // });

    // Route::resource('metadata', 'MetadataController')->only('index');
    // Route::resource('logout', 'LogoutController')->only('index');
});
