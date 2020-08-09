<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SAML idP configuration file
    |--------------------------------------------------------------------------
    |
    | Use this file to configure the service providers you want to use.
    |
     */

    // Outputs data to your laravel.log file for debugging
    'debug' => false,

    // Define the email address field name in the users table
    'email_field' => 'email',

    // The URI to your login page
    'login_uri' => 'login',

    // Log out of the IdP after SLO
    'logout_after_slo' => env('LOGOUT_AFTER_SLO', false),

    // The URI to the saml metadata file, this describes your idP
    'issuer_uri' => 'saml/metadata',

    // Name of the certificate PEM file
    'certname' => 'cert.pem',

    // Name of the certificate key PEM file
    'keyname' => 'key.pem',

    // Encrypt requests and reponses
    'encrypt_assertion' => true,

    // Make sure messages are signed
    'messages_signed' => true,

    // list of all service providers
    'sp' => [
        // SAML settings for Amazon Web Services to login a user to the web aws console
        'aws' => [
            // ACS URL of AWS
            'destination' => 'https://signin.aws.amazon.com/saml',

            // SLO URL of AWS
            'logout' => '',

            /**
             * The X.509 certificate of AWS.
             * This certificate is not constant. You will have to generate your x509 certificate from the "My security credentials" menu
             */
            'certificate' => '',

            // Whether to attach or not any query parameters to the destination URL of the SP. Use an array. If set to false, no params will be attached to the URL
            'query_params' => false,

            /**
             * Whether to return a RelayState or not with the SAMLResponse
             * AWS does not accept a RelayState at the moment.
             */
            'relay_state' => false,

            // NameID format. AWS currently supports all SAML 2.0 formats
            'nameid_format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',

            // AWS requires subject confirmation
            'subject_confirmation_method' => 'urn:oasis:names:tc:SAML:2.0:cm:bearer',

            // Subject confirmation data
            'subject_confirmation_data' => [
                'NotOnOrAfter' => '',
                'Recipient' => 'https://signin.aws.amazon.com/saml'
            ],

            // Required conditions. AWS currently requires AudienceRestriction
            'conditions' => [
                'AudienceRestriction' => [
                    'Audience' => 'urn:amazon:webservices'
                ]
            ],

            // Supported <Attribute> elements by AWS
            'attributes' => [
                // this value goes to the 'Name' attribute like <Attribute Name="">
                'https://aws.amazon.com/SAML/Attributes/Role' => [
                    // These values each goes to <AttributeValue> element inside this <Attribute>
                    'arn:aws:iam::803034018031:role/meveto-saml-test-role,arn:aws:iam::803034018031:saml-provider/Meveto'
                ],

                'https://aws.amazon.com/SAML/Attributes/RoleSessionName' => [
                    '' // This attribute accepts only one <AttributeValue> and usually this can be set to the email address of the authenticated user.
                ],

                /**
                 * Option Attributes
                 */

                // This is the number of seconds that the user will be logged in to AWS for. By default it is set to 3 hours. This value may be overwritten by a user config from the DB.
                'https://aws.amazon.com/SAML/Attributes/SessionDuration' => 10800,


            ],
        ]
    ],

    /**
     * If you need to redirect after SLO depending on SLO initiator.
     * key is beginning of HTTP_REFERER value from SERVER, value is redirect path
     */
    'sp_slo_redirects' => [
        // 'example.com' => 'https://example.com',
    ]
];
