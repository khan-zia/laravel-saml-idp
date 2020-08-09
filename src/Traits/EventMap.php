<?php

namespace khan-zia\SamlIdp\Traits;

trait EventMap
{
    /**
     * All of the Laravel SAML IdP event / listener mappings.
     *
     * @var array
     */
    protected $events = [
        'khan-zia\SamlIdp\Events\Assertion' => [],
        'Illuminate\Auth\Events\Logout' => [
            'khan-zia\SamlIdp\Listeners\SamlLogout',
        ],
        'Illuminate\Auth\Events\Authenticated' => [
            'khan-zia\SamlIdp\Listeners\SamlAuthenticated',
        ],
        'Illuminate\Auth\Events\Login' => [
            'khan-zia\SamlIdp\Listeners\SamlLogin',
        ],
    ];
}
