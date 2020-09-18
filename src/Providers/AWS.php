<?php

declare(strict_types=1);

namespace ZiaKhan\SamlIdp\Providers;

use ZiaKhan\SamlIdp\SamlIdpConstants;

class AWS extends Provider
{
    const AWS_SAML_URL = 'https://signin.aws.amazon.com/saml';

    /**
     * Bootstrap a response for AWS console
     * 
     * @return void
     */
    public function __construct()
    {
        // Initialize the Provider
        parent::__construct();

        // Bootstrap this particular service provider's info
        $this->setName("Amazon Web Services, Inc.");
        $this->setEntityId(self::AWS_SAML_URL);
        $this->setAssertionConsumerServiceURL(self::AWS_SAML_URL);
        $this->setDestination(self::AWS_SAML_URL);
    }

    /**
     * Prepare a SAMLResponse for AWS console
     * 
     * @return self
     */
    public function prepareResponse(): self
    {
        // Invoke the response preparation on the parent (General) provider.
        parent::prepareResponse();

        // Then override or add to fine tune as per this specific provider

        // Prepare SubjectConfirmation element
        $this->subjectConfirmation->setMethod(SamlIdpConstants::CM_BEARER);
        $this->subjectConfirmationData->setNotOnOrAfter($this->notOnOrAfter);
        $this->subjectConfirmationData->setRecipient(self::AWS_SAML_URL);
        $this->subjectConfirmation->setSubjectConfirmationData($this->subjectConfirmationData);

        // Set valid audience specifying AWS
        $this->assertion->setValidAudiences(['urn:amazon:webservices']);

        return $this;
    }
}
