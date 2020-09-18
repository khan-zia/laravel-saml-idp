<?php

namespace ZiaKhan\SamlIdp\Exceptions;

use Exception;

/**
 * THis class deals with throwing exceptions for bad or unexpected parameters.
 * 
 */
class InvalidParameter extends Exception
{
    /**
     * When an invalid/unsupported service provider is specified.
     * The class does not exist at ZiaKhan\SamlIdp\Providers
     * 
     * @param string $provider
     * @return self
     */
    public static function InvalidServiceProvider(string $provider): self
    {
        return new static("The specified service provider '{$provider}' is invalid.");
    }

    /**
     * When an int value does not fall under a required range.
     * 
     * @param string $parameter The incorrect parameter
     * @param int $min The minimum value
     * @param int $max The maximum value
     * @return self
     */
    public static function InvalidIntegerRange(string $parameter, int $min, int $max): self
    {
        return new static("The value of the '{$parameter}' parameter must be between ${min} and ${max}");
    }

    /**
     * When a SAMLResponse binding type, that is not supported, is specified,
     * this exception will be thrown.
     * 
     * @param string $binding The unsupported binding that is specified
     * @return self
     */
    public static function InvalidResponseBinding(string $binding): self
    {
        return new static("The specified response binding '{$binding}' is not supported/invalid.");
    }
}
