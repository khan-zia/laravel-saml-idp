<?php

declare(strict_types=1);

namespace ZiaKhan\SamlIdp;

/**
|--------------------------------------------------------------------------
| Constants that are specific to this package
|--------------------------------------------------------------------------
|
| These constants are not defined by the SAML 2.0 specifications.
| These constants are specific to this package and not helps in making
| the code neat and clean but also allows for easy development process
| while working with different SAML service providers.
|
 */
class SamlIdpConstants
{
    /**
     * The list of currently supported Service Provider applications/cloud services
     * The values of these constants MUST always match exactly 'name' attribute
     * of the 'service_providers'.
     * 
     * @see ZiaKhan\SamlIdp\Models\ServiceProvider
     */
    const AWS = "AWS Management Console Single Sign-On";
    const ATLASSIAN = "Atlassian Cloud";
    const CLOUDFLARE = "Cloudflare Access";
    const SLACK = "Slack";
    const FASTLY = "Fastly";
    const HUBSPOT = "HubSpot";
    const GITHUB = "GitHub Enterprise";
    const SALESFORCE = "Salesforce";
    const DROPBOX = "Dropbox";

    /**
     * Possible required values by a SAML service provider in a SAMLResponse message's
     * assertions.
     */
    const USER_ID = "user_id";
    const USERNAME = "username";
    const EMAIL = "email";
    const FULL_NAME = "full_name";
    const FIRST_NAME = "first_name";
    const LAST_NAME = "last_name";
    const BIRTHDAY = "birthday";
    const GENDER = "gender";
    const PHONE_NUMBER = "phone_number";
    const COUNTRY = "country";

    /**
     * Validation rules and custom messages for different service providers
     * The array KEYS maps to the IDs of service providers in the database
     */
    const VALIDATION = [
        self::AWS => [
            'RULES' => [
                'provider' => 'required|string|min:3', // The name of the IDP provider set at AWS
                'account' => 'required|numeric|min:11', // The AWS Account number
                'role_session_name' => 'required|string|min:3', // Name of the login session for AWS
                'role_session_time' => 'nullable|numeric|min:900|max:43200', // The time the login session is valid for in seconds.
                'roles' => 'required|array|min:1', // An array for defining AWS roles.
                'roles.*' => 'required|string|distinct|min:3', // Each role
            ],
            'MESSAGES' => [
                'provider.required' => 'Specify name of the SAML Identity Provider that you set at Amazon Web Services management console.',
                'provider.string' => 'Name of the SAML Identity Provider is invalid.',
                'provider.min' => 'Name of the SAML Identity Provider must be at least :min characters long.',
                'account.required' => 'Specify your Amazon Web Services account number.',
                'account.numeric' => 'Amazon Web Services account is invalid.',
                'account.min' => 'Amazon Web Services account must be at least :min characters long.',
                'role_session_name.required' => 'Specify a session name for the role that you want to login to.',
                'role_session_name.string' => 'The specified role session name is invalid.',
                'role_session_name.min' => 'The role session name must be at least :min characters long.',
                'role_session_time.min' => 'The role session time must be between 900 and 43200 seconds.',
                'role_session_time.max' => 'The role session time must be between 900 and 43200 seconds.',
                'roles.required' => 'Specify Amazon Web Services roles that you would like to assume.',
                'roles.array' => 'The specified roles are invalid.',
                'roles.min' => 'Specify at least 1 Amazon Web Services role.',
                'roles.*.required' => 'Specified Amazon Web Services role can not be empty.',
                'roles.*.string' => 'An invalid Amazon Web Services role has been specified.',
                'roles.*.distinct' => 'Each Amazon Web Services role must be specified only once.',
                'roles.*.min' => 'Make your Amazon Web Services roles at least :min characters long.',
            ]
        ],
        self::ATLASSIAN => [
            'RULES' => [
                'org_id' => 'required|alpha_dash',
            ],
            'MESSAGES' => [
                'org_id.required' => 'ID of your Atlassian cloud organization is required.',
                'org_id.alpha_dash' => 'ID of your Atlassian cloud organization is supposed to be a string of random characters without any spaces.',
            ]
        ],
        self::CLOUDFLARE => [
            'RULES' => [
                'domain' => 'required|alpha_dash',
            ],
            'MESSAGES' => [
                'domain.required' => "What is your organization's domain at Cloudflare?",
                'domain.alpha_dash' => "Your Cloudflare organization's domain name must be an alphanumeric string and can not contain spaces.",
            ]
        ],
        self::SLACK => [
            'RULES' => [
                'domain' => 'required|alpha_dash',
            ],
            'MESSAGES' => [
                'domain.required' => "What is your organization's domain at Slack?",
                'domain.alpha_dash' => "Your Slack organization's domain name must be an alphanumeric string and can not contain spaces.",
            ]
        ],
        self::FASTLY => [
            'RULES' => [
                'token' => 'required|alpha_dash',
            ],
            'MESSAGES' => [
                'token.required' => "What is your Fastly account's SSO token?",
                'token.alpha_dash' => "SSO token of your Fastly account must be an alphanumeric string and can not contain spaces.",
            ]
        ],
        self::HUBSPOT => [
            'RULES' => [
                'account' => 'required|alpha_dash',
            ],
            'MESSAGES' => [
                'account.required' => "What is your HubSpot account ID?",
                'account.alpha_dash' => "Your HubSpot account's ID must be an alphanumeric string and can not contain spaces.",
            ]
        ],
        self::GITHUB => [
            'RULES' => [
                'domain' => 'required|alpha_dash',
            ],
            'MESSAGES' => [
                'domain.required' => "Your GitHub organization's name is required.",
                'domain.alpha_dash' => "Your GitHub organization's name must be an alphanumeric string and can not contain spaces.",
            ]
        ],
        self::SALESFORCE => [
            'RULES' => [
                'domain' => 'required|alpha_dash',
            ],
            'MESSAGES' => [
                'domain.required' => "What is domain name of your Salesforce organization?",
                'domain.alpha_dash' => "Domain name of your Salesforce organization must be an alphanumeric string and can not contain spaces.",
            ]
        ],
        self::DROPBOX => [
            'RULES' => [],
            'MESSAGES' => []
        ],
    ];
}
