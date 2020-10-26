<?php

declare(strict_types=1);

namespace ZiaKhan\SamlIdp\Contracts;

use SAML2\Constants;
use ZiaKhan\SamlIdp\Exceptions\InvalidParameter;
use ZiaKhan\SamlIdp\Models\ServiceProvider as ServiceProviderModel;

/**
 * This class represents any SAML service provider application.
 * The class provides a general interface for working with elements of a service provider.
 */
abstract class ServiceProvider
{
    /**
     * @var ServiceProviderModel|null
     */
    protected ?ServiceProviderModel $serviceProvider = null;

    /**
     * The Fully Qualified Domain Name (FQDN) or any publicly identifiable name of the service provider.
     * 
     * @var string|null
     */
    protected ?string $name = null;

    /**
     * EntityID of the service provider that will be used for the SAMLResponse
     * 
     * @var string|null
     */
    protected ?string $entityId = null;

    /**
     * The Assertion Consumer Service URL of the service provider that will receive the SAMLResponse
     * 
     * @var string|null
     */
    protected ?string $acsUrl = null;

    /**
     * The destination URL for SAMLResponse
     * 
     * @var string|null
     */
    protected ?string $destination = null;

    /**
     * The Relay State if required by the service provider along with a SAMLResponse
     * 
     * @var string|null
     */
    protected ?string $relayState = null;

    /**
     * An associative array of query parameters that will be attached to the end of the ACS URL along with SAMLResponse
     * 
     * @var array|null
     */
    protected ?array $queryParams = null;

    /**
     * The X.509 certificate of the service provider that will be used for encrypting assertions if required.
     * 
     * @var string|null
     */
    protected ?string $certificate = null;

    /**
     * The NameID format that is required/supported by the service provider.
     * 
     * @var string
     */
    protected string $nameIdFormat = Constants::NAMEID_UNSPECIFIED;

    /**
     * The NameQualifier value.
     * 
     * @var string|null
     */
    protected ?string $nameQualifier = null;

    /**
     * The SPNameQualifier value.
     * 
     * @var string|null
     */
    protected ?string $SPNameQualifier = null;

    /**
     * The NameID value.
     * 
     * @var string|null
     */
    protected ?string $nameIdValue = null;

    /**
     * The SAMLResponse binding type. i.e. How the SAMLResponse should be sent.
     * Defaults to HTTP-POST binding
     * 
     * @var string
     */
    protected ?string $responseBidingType = Constants::BINDING_HTTP_POST;

    /**
     * Set a service provider model instance on the object of this class
     * 
     * @param ServiceProviderModel $serviceProvider
     * @return void
     */
    public function setServiceProvider(ServiceProviderModel $serviceProvider): void
    {
        $this->serviceProvider = $serviceProvider;
        $this->setName($serviceProvider->name);
        $this->setEntityId($serviceProvider->entity_id);
        $this->setAssertionConsumerServiceURL($serviceProvider->acs_url);
        $this->setDestination($serviceProvider->acs_url);
        $this->setCertificate($serviceProvider->x509);
        $this->setNameIDFormat($serviceProvider->nameid_format);
        $this->setResponseBidingType($serviceProvider->binding);
    }

    /**
     * Get instance of the service provider modal
     * 
     * @return ServiceProviderModel|null
     */
    public function getServiceProvider(): ?ServiceProviderModel
    {
        return $this->serviceProvider;
    }

    /**
     * Set FQDN or any publicly identifiable name for the service provider
     * 
     * @param string|null $name Public name of the service provider
     * @return void
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get FQDN of the service provider
     * 
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set EntityID of the service provider application
     * 
     * @param string|null $eid
     * @return void
     */
    public function setEntityId(?string $eid): void
    {
        $this->entityId = $eid;
    }

    /**
     * Get EntityID of the service provider application
     * 
     * @return string|null
     */
    public function getEntityId(): ?string
    {
        return $this->entityId;
    }

    /**
     * Set the Assertion Consumer Service (ACS) URL for the SAMLResponse
     * 
     * @param string|null $acs
     * @return void
     */
    public function setAssertionConsumerServiceURL(?string $acs): void
    {
        $this->acsUrl = $acs;
    }

    /**
     * Get the Assertion Consumer Service (ACS) URL for the SAMLResponse
     * 
     * @return string|null
     */
    public function getAssertionConsumerServiceURL(): ?string
    {
        return $this->acsUrl;
    }

    /**
     * Set the destination URL for the SAMLResponse
     * 
     * @param string|null $destination
     * @return void
     */
    public function setDestination(?string $destination): void
    {
        $this->destination = $destination;
    }

    /**
     * Get the destination URL for the SAMLResponse
     * 
     * @return string|null
     */
    public function getDestination(): ?string
    {
        return $this->destination;
    }

    /**
     * Set the relay state for the SAMLResponse
     * 
     * @param string|null $relayState
     * @return void
     */
    public function setRelayState(?string $relayState): void
    {
        $this->relayState = $relayState;
    }

    /**
     * Get the relay state for the SAMLResponse
     * 
     * @return string|null
     */
    public function getRelayState(): ?string
    {
        return $this->relayState;
    }

    /**
     * Set query parameters for the SAMLResponse.
     * These parameters will be added to the end of the specified ACS URL of the service provider.
     * Use an associative array to define query parameters.
     * 
     * @param array $queryParams
     * @return void
     */
    public function setQueryParams(array $queryParams): void
    {
        $this->queryParams = $queryParams;
    }

    /**
     * Get the query parameters for the SAMLResponse
     * 
     * @return array|null
     */
    public function getQueryParams(): ?array
    {
        return $this->queryParams;
    }

    /**
     * Set the X.509 certificate of the service provider. It must be PEM encoded.
     * 
     * @param string|null $x509
     * @return void
     */
    public function setCertificate(?string $x509): void
    {
        $this->certificate = $x509;
    }

    /**
     * Get the X.509 certificate of the service provider.
     * 
     * @return string|null
     */
    public function getCertificate(): ?string
    {
        return $this->certificate;
    }

    /**
     * Set the NameID Format for the SAMLResponse.
     * If you do not specify a nameID Format, then it will default to
     * * "urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified"
     * 
     * TODO: Add exception handling if nameIDFormat is invalid.
     * 
     * @param string $nameIdFormat
     * @return void
     */
    public function setNameIDFormat(string $nameIdFormat = 'urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified'): void
    {
        $this->nameIdFormat = $nameIdFormat;
    }

    /**
     * Get the NameID Format of the SAMLResponse
     * 
     * @return string
     */
    public function getNameIDFormat(): string
    {
        return $this->nameIdFormat;
    }

    /**
     * Set the NameQualifier attribute on the NameID element for the SAMLResponse.
     * 
     * @param string|null $nameQualifier
     * @return void
     */
    public function setNameQualifier(?string $nameQualifier): void
    {
        $this->nameQualifier = $nameQualifier;
    }

    /**
     * Get value of the NameQualifier attribute on the NameID element.
     * 
     * @return string|null
     */
    public function getNameQualifier()
    {
        return $this->nameQualifier;
    }

    /**
     * Set the SPNameQualifier attribute on the NameID element for the SAMLResponse.
     * 
     * @param string|null $SPNameQualifier
     * @return void
     */
    public function setSPNameQualifier(?string $SPNameQualifier): void
    {
        $this->SPNameQualifier = $SPNameQualifier;
    }

    /**
     * Get value of the SPNameQualifier attribute on the NameID element.
     * 
     * @return string|null
     */
    public function getSPNameQualifier()
    {
        return $this->SPNameQualifier;
    }

    /**
     * Set NameID Value for the SAMLResponse.
     * 
     * @param string|null $nameIdValue
     * @return void
     */
    public function setNameIDValue(?string $nameIdValue): void
    {
        $this->nameIdValue = $nameIdValue;
    }

    /**
     * Get the NameID Value of the SAMLResponse
     * 
     * @return string|null
     */
    public function getNameIDValue(): ?string
    {
        return $this->nameIdValue;
    }

    /**
     * Set the SAML response binding type. Either HTTP-Post or HTTP-Redirect
     * bindings are supported at the moment.
     * defaults to 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST'
     * 
     * @param string $responseBidingType Must be one of 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST', 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect'
     * @return void
     * 
     * @throws InvalidParameter
     */
    public function setResponseBidingType(string $responseBidingType = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST'): void
    {
        // The list of currently supported bindings
        $supported = [
            Constants::BINDING_HTTP_POST,
            Constants::BINDING_HTTP_REDIRECT
        ];

        if (!in_array($responseBidingType, $supported)) {
            throw InvalidParameter::InvalidResponseBinding($responseBidingType);
        }

        $this->responseBidingType = $responseBidingType;
    }

    /**
     * Get the SAML response binding type.
     * 
     * @return string
     */
    public function getResponseBidingType(): string
    {
        return $this->responseBidingType;
    }
}
