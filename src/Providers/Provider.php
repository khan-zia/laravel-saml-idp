<?php

declare(strict_types=1);

namespace ZiaKhan\SamlIdp\Providers;

use ZiaKhan\SamlIdp\Contracts\ServiceProvider;
use ZiaKhan\SamlIdp\Container;
use ZiaKhan\SamlIdp\SamlIdpConstants;
use Carbon\Carbon;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Assertion;
use SAML2\Binding;
use SAML2\Compat\ContainerSingleton;
use SAML2\HTTPPost;
use SAML2\HTTPRedirect;
use SAML2\Response;
use SAML2\Utils;
use SAML2\XML\saml\Issuer;
use SAML2\XML\saml\NameID;
use SAML2\XML\saml\SubjectConfirmation;
use SAML2\XML\saml\SubjectConfirmationData;

/**
 * This class represents a general instance for any saml Service Provider.
 * That's any application that will be able to consume SAMLResponse from this IDP
 * 
 * This class is supposed to be extended by other specific service provider classes.
 * The Instance of this class will be created by the setServiceProvider() method of SamlIdp Facade.
 * 
 * Even if the setServiceProvider() method is used to instantiate a specific service provider i.e. AWS,
 * the constructor of the specific child provider class will first invoke the constructor of this class
 * and it will be booted up as most of the SAMLResponse message is prepared here and the child classes
 * are only meant to override for Service Provider specific data.
 * 
 * @see ZiaKhan\SamlIdp\SamlIdpService@setServiceProvider()
 * 
 * @author Zia U Rehman Khan
 * 
 * @license Proprietary
 * 
 * @copyright Meveto Inc, California.
 */
class Provider extends ServiceProvider
{
    /**
     * The current timestamp that could be used for IssueInstant attribute
     * 
     * @var int
     */
    protected int $timeStamp;

    /**
     * The timestamp for NotBefore attribute
     * 
     * @var int
     */
    protected int $notBefore;

    /**
     * The timestamp for NotOnOrAfter attribute
     * 
     * @var int
     */
    protected int $notOnOrAfter;

    /**
     * The SAMLResponse message container
     * 
     * @var Response
     */
    protected Response $response;

    /**
     * The RSA private key of this IDP
     * 
     * @var XMLSecurityKey
     */
    protected XMLSecurityKey $privateKey;

    /**
     * The issuer URI of this IDP
     * 
     * @var Issuer
     */
    protected Issuer $issuer;

    /**
     * The Assertion element for this SAMLResponse
     * 
     * @var Assertion
     */
    protected Assertion $assertion;

    /**
     * The NameID element for this SAMLResponse
     * 
     * @var NameID
     */
    protected NameID $nameIdElement;

    /**
     * The SubjectConfirmation element for this SAMLResponse
     * 
     * @var SubjectConfirmation
     */
    protected SubjectConfirmation $subjectConfirmation;

    /**
     * The SubjectConfirmationData element for this SAMLResponse
     * 
     * @var SubjectConfirmationData
     */
    protected SubjectConfirmationData $subjectConfirmationData;

    /**
     * The binding instance for this SAMLResponse
     * 
     * @var HTTPPost|HTTPRedirect|null
     */
    protected ?Binding $responseBinding = null;

    /**
     * Bootstrap the saml IDP service and make it ready for generating a saml message.
     * 
     * @param
     * @return void
     */
    public function __construct()
    {
        // Set time instance
        $time = Carbon::now();
        $this->timeStamp = $time->timestamp;
        $this->notBefore = $time->addSeconds(2)->timestamp;
        $this->notOnOrAfter = $time->addMinutes(config('samlidp.saml_response_validity_in_minutes'))->timestamp;

        // Set the SAML2 container
        ContainerSingleton::setContainer(new Container);

        // Initialize this IDP's private kye
        $this->privateKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA512, ['type' => 'private']);
        $this->privateKey->loadKey(config('samlidp.private_key'));

        // Initialize an Issuer
        $this->issuer = new Issuer();
        $this->issuer->setValue(config('samlidp.issuer_uri'));

        // Initialize SAMLResponse
        $this->response = new Response();
        // Set the issuer on response
        $this->response->setIssuer($this->issuer);
        // if the SAMLResponse message is supposed to be signed
        if (config('samlidp.sign_response')) {
            $this->response->setCertificates([config('samlidp.x509_cert')]);
            $this->response->setSignatureKey($this->privateKey);
        }

        // Initialize Assertion
        $this->assertion = new Assertion();
        // if assertions are supposed to be signed
        if (config('samlidp.sign_assertions')) {
            $this->assertion->setCertificates([config('samlidp.x509_cert')]);
            $this->assertion->setSignatureKey($this->privateKey);
        }

        // Initialize NameID
        $this->nameIdElement = new NameID();

        // Initialize SubjectConfirmation
        $this->subjectConfirmation = new SubjectConfirmation();

        // Initialize SubjectConfirmationData
        $this->subjectConfirmationData = new SubjectConfirmationData();

        // Initialize an instance of appropriate response binding
        switch ($this->getResponseBidingType()) {
            case 'post':
                $this->responseBinding = new HTTPPost();
                break;
            case 'redirect':
                $this->responseBinding = new HTTPRedirect();
                break;
        }
    }

    /**
     * Prepare a SAMLResponse message
     * 
     * @return self
     */
    public function prepareResponse(): self
    {
        // Set destination on the response
        $this->response->setDestination($this->getDestination());

        // Set NameID Element
        $this->nameIdElement->setFormat($this->getNameIDFormat());
        $this->nameIdElement->setValue($this->getNameIDValue());

        // Make assertions
        $this->assertion->setIssueInstant($this->timeStamp);
        $this->assertion->setIssuer($this->issuer);
        $this->assertion->setAuthnInstant($this->timeStamp);
        $this->assertion->setNotBefore($this->notBefore);
        $this->assertion->setNotOnOrAfter($this->notOnOrAfter);
        $this->assertion->setAuthnContextClassRef(SamlIdpConstants::NAMEID_UNSPECIFIED);
        $this->assertion->setNameId($this->nameIdElement);
        $this->assertion->setSubjectConfirmation([$this->subjectConfirmation]);

        // Set assertions on the response
        $this->response->setAssertions([$this->assertion]);

        return $this;
    }

    /**
     * Set the requested subject metadata/attributes for the service provider
     * 
     * @param array $subjectMetadata
     * @return self
     */
    public function setSubjectMetadata(array $subjectMetadata): self
    {
        $this->assertion->setAttributes($subjectMetadata);

        return $this;
    }

    /**
     * Process and serialize the prepared SAMlResponse.
     * This will create the XML document from the attributes set on this provider.
     * The XML document can then be obtained from the getData() method of the container.
     * use getContainer() method of this class to obtain an instance of the container.
     * 
     * @return self
     */
    public function processXMLDocument(): self
    {
        // Based on the response binding method, process and prepare the SAMLResponse XML document.
        $this->responseBinding->send($this->response);

        return $this;
    }

    /**
     * Get the SAML2 container instance.
     * This is useful to provide access to the consumer application of this library
     * to access the final SAML message, debug and write responses to files among other.
     * 
     * @return Container
     */
    public function getContainer(): Container
    {
        return Utils::getContainer();
    }
}
