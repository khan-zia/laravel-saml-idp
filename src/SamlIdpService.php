<?php

namespace ZiaKhan\SamlIdp;

use ZiaKhan\SamlIdp\Exceptions\InvalidParameter;
use ZiaKhan\SamlIdp\Providers\Provider;

class SamlIdpService
{
    /**
     * @var Provider
     */
    protected ?Provider $serviceProvider = null;

    /**
     * Set an Instance of a service provider for the saml IDP.
     * specify a particular target service provider from the list of available providers.
     * If you don't specify a provider, a general purpose instance will be created.
     * 
     * @param string|null $provider Target Service Provider
     * @return self
     * 
     * @throws InvalidServiceProvider
     */
    public function setServiceProvider(?string $provider = null): self
    {
        // If no provider is specified, then set a generic instance
        if (!$provider) {
            $this->serviceProvider = new Provider;
            return $this;
        }

        // If a service provider is specified, create an instance of it.
        // Make sure the provider exists.
        if (class_exists($provider)) {
            $this->serviceProvider = new $provider;
        } else {
            throw InvalidParameter::InvalidServiceProvider($provider);
        }

        return $this;
    }

    /**
     * Get the target service provider
     * 
     * @return Provider
     */
    public function getServiceProvider(): Provider
    {
        return $this->serviceProvider;
    }
}
