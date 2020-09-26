<?php

declare(strict_types=1);

namespace ZiaKhan\SamlIdp\Contracts;

use SAML2\Constants;
use ZiaKhan\SamlIdp\Exceptions\InvalidParameter;

/**
 * This class represents any SAML service provider application.
 * The class provides a general interface for working with elements of a service provider.
 */
abstract class ServiceProvider
{
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
    protected ?string $responseBidingType = 'post';

    /**
     * An associative array of metadata specifying a service provider subject.
     * 
     * @var array|null
     */
    protected ?array $subjectMetaData = null;

    /**
     * Set FQDN or any publicly identifiable name for the service provider
     * 
     * @param string $name Public name of the service provider
     * @return void
     */
    public function setName(string $name): void
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
     * @param string $eid
     * @return void
     */
    public function setEntityId(string $eid): void
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
     * @param string $acs
     * @return void
     */
    public function setAssertionConsumerServiceURL(string $acs): void
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
     * @param string $destination
     * @return void
     */
    public function setDestination(string $destination): void
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
     * @param string $relayState
     * @return void
     */
    public function setRelayState(string $relayState): void
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
     * @param string $x509
     * @return void
     */
    public function setCertificate(string $x509): void
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
    public function setNameIDFormat(string $nameIdFormat): void
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
     * Set NameID Value for the SAMLResponse.
     * 
     * @param string $nameIdValue
     * @return void
     */
    public function setNameIDValue(string $nameIdValue): void
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
     * 
     * @param string $responseBidingType Must be one of ['post', 'redirect']
     * @return void
     * 
     * @throws InvalidParameter
     */
    public function setResponseBidingType(string $responseBidingType): void
    {
        // The list of currently supported bindings
        $supported = [
            'post',
            'redirect'
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

    /**
     * Set an associative array of key value pairs that describes a subject/user for the
     * target service provider. Some service providers require this information to identify
     * the user, adjust their privileges and control sign in sessions.
     * 
     * Array Format
     * [
     *  string 'metadata/attribute name' => array ['attribute value 1', 'attribute value 2', '...'],
     * ]
     * 
     * @param array $subjectMetaData
     * @return self
     */
    public function setSubjectMetaData(array $subjectMetaData): self
    {
        $this->subjectMetaData = $subjectMetaData;
        return $this;
    }

    /**
     * Get metadata that describes the subject for the service provider
     * 
     * @return array|null
     */
    public function getSubjectMetaData(): ?array
    {
        return $this->subjectMetaData;
    }
}
