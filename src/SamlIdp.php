<?php

namespace ZiaKhan\SamlIdp;

class SamlIdp
{
    /**
     * Call a SamlIdp method statically although the method may not be static.
     * This is based on the Laravel's Facades concept.
     * 
     * @param string $method Name of the method to call
     * @param array $arguments List of arguments to pass to the method
     */
    public static function __callStatic($method, $arguments)
    {
        return (self::resolveFacade('SamlIdp')->$method(...$arguments));
    }

    /**
     * Resolve the requested Facade
     * @param string $name Name of the Facade
     * @return mix
     */
    protected static function resolveFacade(string $name)
    {
        return app()->make($name);
    }
}
