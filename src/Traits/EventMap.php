<?php

namespace ziakhan\SamlIdp\Traits;

trait EventMap
{
    /**
     * All of the Laravel SAML IdP event / listener mappings.
     *
     * @var array
     */
    protected $events = [
        'ziakhan\SamlIdp\Events\Assertion' => [],
        'Illuminate\Auth\Events\Logout' => [
            'ziakhan\SamlIdp\Listeners\SamlLogout',
        ],
        'Illuminate\Auth\Events\Authenticated' => [
            'ziakhan\SamlIdp\Listeners\SamlAuthenticated',
        ],
        'Illuminate\Auth\Events\Login' => [
            'ziakhan\SamlIdp\Listeners\SamlLogin',
        ],
    ];
}
