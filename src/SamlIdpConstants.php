<?php

declare(strict_types=1);

namespace ZiaKhan\SamlIdp;

use SAML2\Constants;

/**
 * This class contains various saml IDP constants.
 * These constants help identify various elements such as service providers.
 */
class SamlIdpConstants extends Constants
{
    /**
     |--------------------------------------------------------------------------
     | Constants that identify available saml Service Providers
     |--------------------------------------------------------------------------
     |
     | Use these constants to refer to the saml Service Providers that are
     | supported by the library out of the box. The constant values are
     | exactly the same as name of the corresponding classes in the providers
     | directory.
     |
     */

    /**
     * The namespace for Service Providers
     */
    const NAME_SPACE = "\ZiaKhan\SamlIdp\Providers\\";

    /**
     * Amazon Web Services (AWS) web console as a target Service Provider
     */
    const AWS_CONSOLE = self::NAME_SPACE . 'AWS';
}
