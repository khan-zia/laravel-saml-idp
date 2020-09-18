<?php

return [

    /*
    |--------------------------------------------------------------------------
    | The SAML IDP configuration file.
    |--------------------------------------------------------------------------
    |
    | You can use this file to configure your IDP settings.
    |
     */

    /**
     * Outputs data to your laravel.log file for debugging.
     */
    'debug' => false,

    /**
     * The single sign on URL of this IDP.
     * Service providers will redirect users to this URL for authentication at this IDP.
     */
    'sso_uri' => 'login',

    /**
     * The single logout URL of this IDP.
     * Service providers will redirect users to this URL for single logout.
     */
    'slo_uri' => 'login',

    /**
     * Whether to log a user out of this IDP as well or not when a service provider requests a single logout.
     * You can control this from your app's .env by using "LOGOUT_AFTER_SLO" key. It defaults to false.
     */
    'logout_after_slo' => env('LOGOUT_AFTER_SLO', false),

    /**
     * The URL of this IDP at which the metadata xml file exist.
     * Service Providers can make use of this URL to learn configuration requirements for this IDP.
     */
    'issuer_uri' => 'saml/metadata',

    /**
     * The SHA512 RSA private key of this IDP. (Key must be SHA512)
     * This key will be used to generate and verify signatures and also encrypt or decrypt responses.
     * We recommend using 4096 bits long key instead of the otherwise typical 2048 bits.
     */
    'private_key' => env('SAML_PRIVATE_KEY', ''),

    /**
     * The X.509 certificate of this IDP. This must be generated from the private key specified above.
     * Service Providers will be able to use this certificate to verify signatures of this IDP and to send signed requests to this IDP.
     */
    'x509_cert' => env('SAML_X509_CERT', ''),

    /**
     * Whether to encrypt SAMLRequest and SAMLResponse messages or not.
     * defaults to false.
     */
    'use_encryption' => false,

    /**
     * Whether to sign SAML assertions or not.
     * It is highly recommended to use signed assertions. Defaults to true
     */
    'sign_assertions' => true,

    /**
     * Whether to sign SAMLResponse as a whole or not.
     * It is highly recommended to use signed responses. Defaults to true
     */
    'sign_response' => true,

    /**
     * The number of minutes that a SAMLResponse message will be valid for from the time of its creation.
     */
    'saml_response_validity_in_minutes' => 2
];
