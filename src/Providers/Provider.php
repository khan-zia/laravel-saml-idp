<?php

declare(strict_types=1);

namespace ZiaKhan\SamlIdp\Providers;

use ZiaKhan\SamlIdp\Contracts\ServiceProvider;
use ZiaKhan\SamlIdp\Container;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use SAML2\AuthnRequest;
use SAML2\Constants;
use ZiaKhan\SamlIdp\Models\ServiceProvider as ModelsServiceProvider;
use ZiaKhan\SamlIdp\Models\UserSamlClient;
use ZiaKhan\SamlIdp\SamlIdpConstants;

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
     * The SAML SSO instance set by the user
     * 
     * @var UserSamlClient|null
     */
    protected ?UserSamlClient $samlClient = null;

    /**
     * Instance of the authentication request.
     * 
     * @var AuthnRequest|null
     */
    protected ?AuthnRequest $authnRequest = null;

    /**
     * Bootstrap the saml IDP service and make it ready for generating a saml message.
     * 
     * @param UserSamlClient|AuthnRequest $responseDriver What should influence the SAMLResponse for a service provider?
     * This value can be either a pre-defined SAML Service Provider instance or an Authentication request in an SP-initiated
     * flow.
     * 
     * @return void
     */
    public function __construct($responseDriver = null)
    {
        // Set time instance
        $time = Carbon::now();
        $this->timeStamp = $time->timestamp;
        $this->notBefore = $time->addSeconds(2)->timestamp;
        $this->notOnOrAfter = $time->addMinutes(config('samlidp.saml_response_validity_in_minutes'))->timestamp;

        // Set the SAML2 container
        ContainerSingleton::setContainer(new Container);

        // Initialize this IDP's private kye
        $this->privateKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
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

        /**
         * How should the SAMLResponse message be generated?
         * The driver as we call it, could be either a pre-defined instance for a
         * service provider or an on the fly SAMLRequest message of the AuthnRequest
         * type.
         */
        if ($responseDriver instanceof UserSamlClient) {
            $this->samlClient = $responseDriver;
        }

        if ($responseDriver instanceof AuthnRequest) {
            $this->authnRequest = $responseDriver;

            // Set the InResponseTo attribute on the SAMLResponse message
            $this->response->setInResponseTo($responseDriver->getId());

            // Set other values that will be used in the SAMLResponse message.
            $this->setName($responseDriver->getProviderName());
            $this->setEntityId($responseDriver->getIssuer()->getValue());
            $this->setAssertionConsumerServiceURL($responseDriver->getAssertionConsumerServiceURL());
            $this->setDestination($responseDriver->getAssertionConsumerServiceURL());
            $this->setCertificate($responseDriver->getCertificates()[0] ?? null);
            $this->setNameIDFormat($responseDriver->getNameIdPolicy()['Format']);
            $this->setResponseBidingType($responseDriver->getProtocolBinding() ?? Constants::BINDING_HTTP_POST);
            $this->setRelayState($responseDriver->getRelayState());
            $this->setSubjectConfirmation($responseDriver->getId());

            // If the Issuer is a known Service Provider, then process any required attributes
            $sp = ModelsServiceProvider::where('entity_id', '=', $responseDriver->getIssuer()->getValue())->first();
            if ($sp) $this->setSubjectMetadata($sp);
        }

        // Initialize an instance of appropriate response binding
        switch ($this->getResponseBidingType()) {
            case Constants::BINDING_HTTP_POST:
                $this->responseBinding = new HTTPPost();
                break;
            case Constants::BINDING_HTTP_REDIRECT:
                $this->responseBinding = new HTTPRedirect();
                break;
        }
    }

    /**
     * Process the user defined SAML SSO instance.
     * 
     * @return self
     */
    public function processSamlClient(): self
    {
        if ($this->samlClient) {
            // Set instance of the service provider model
            $this->setServiceProvider($this->samlClient->serviceProvider);

            // Set relay state if the service provider has one for this UserSamlClient instance
            $this->setRelayState($this->samlClient->relay_state);

            // Check if NameQualifier and SPNameQualifier attributes are defined for this instance, if so, set them.
            $this->setNameQualifier($this->samlClient->name_qualifier);
            $this->setSPNameQualifier($this->samlClient->spname_qualifier);

            /**
             * If the service provider has different rather than common entity ID and ACS URL for its users,
             * then set the entity ID and ACS url as these values are NULL on the model for such service providers.
             */
            if ($this->serviceProvider->entity_id === null) $this->setEntityId($this->samlClient->entity_id);

            if ($this->serviceProvider->acs_url === null) {
                $this->setAssertionConsumerServiceURL($this->samlClient->acs_url);
                $this->setDestination($this->samlClient->acs_url);
            }
        }

        return $this;
    }

    /**
     * Set the requested subject metadata/attributes for the service provider
     * 
     * @param ModelsServiceProvider|null $serviceProvider
     * @return self
     */
    public function setSubjectMetadata(?ModelsServiceProvider $serviceProvider = null): self
    {
        $metadata = [];
        $SPRequiredAttributes = null;
        $SPProvidedAttributes = null;

        if ($serviceProvider) {
            $SPRequiredAttributes = $serviceProvider->subject_metadata;
        }

        // If a ServiceProvider instance is not passed, then $this->samlClient must be defined.
        if ($this->samlClient) {
            /**
             * Subject metadata is of 2 types:
             * 1. Service providers may require value for an attribute from this IDP.
             * 2. Service provider may provide a pre defined set of values to the user that wishes to SSO through this IDP
             * and those values may or may not be present in the SAMLResponse that this IDP returns.
             * 
             * first process `subject_metadata` if defined on the service provider's model, next,
             * `subject_metadata` that may or may not be defined on the user's service provider instance.
             * 
             * Extract subject metadata first
             */

            $SPRequiredAttributes = $this->samlClient->serviceProvider->subject_metadata;
            $SPProvidedAttributes = $this->samlClient->subject_metadata;
        }

        // If not NULL, json_decode, otherwise set to an empty array.
        $SPRequiredAttributes = $SPRequiredAttributes ? json_decode($SPRequiredAttributes, true) : [];
        $SPProvidedAttributes = $SPProvidedAttributes ? json_decode($SPProvidedAttributes, true) : [];

        // Merge both arrays
        $metadata = array_merge($SPRequiredAttributes, $SPProvidedAttributes);

        // If metadata is not an empty array so far then process it.
        if (!empty($metadata)) {
            $set = [];

            // Loop through and assign required values
            foreach ($metadata as $attributeName => $requiredValue) {
                switch ($requiredValue) {
                    case SamlIdpConstants::USER_ID:
                        $set[$attributeName] = [Auth::user()->id];
                        break;
                    case SamlIdpConstants::USERNAME:
                        $set[$attributeName] = [Auth::user()->username];
                        break;
                    case SamlIdpConstants::EMAIL:
                        $set[$attributeName] = [Auth::user()->email];
                        break;
                    case SamlIdpConstants::FULL_NAME:
                        $set[$attributeName] = [Auth::user()->info->first_name . ' ' . Auth::user()->info->last_name];
                        break;
                    case SamlIdpConstants::FIRST_NAME:
                        $set[$attributeName] = [Auth::user()->info->first_name];
                        break;
                    case SamlIdpConstants::LAST_NAME:
                        $set[$attributeName] = [Auth::user()->info->last_name];
                        break;
                    default:
                        $set[$attributeName] = is_array($requiredValue) ? $requiredValue : [$requiredValue];
                        break;
                }
            }

            // Set attributes on the assertion
            $this->assertion->setAttributes($set);
        }
        return $this;
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
        switch ($this->getNameIDFormat()) {
            case Constants::NAMEID_EMAIL_ADDRESS:
                $this->setNameIDValue(Auth::user()->email);
                break;
            case Constants::NAMEID_UNSPECIFIED:
                $this->setNameIDValue((string) Auth::user()->id);
                break;

                // And for all other cases that are not supported yet
            default:
                $this->setNameIDValue((string) Auth::user()->id);
                break;
        }
        $this->nameIdElement->setFormat($this->getNameIDFormat());
        $this->nameIdElement->setNameQualifier($this->getNameQualifier());
        $this->nameIdElement->setSPNameQualifier($this->getSPNameQualifier());
        $this->nameIdElement->setValue($this->getNameIDValue());

        // Make assertions
        $this->assertion->setIssueInstant($this->timeStamp);
        $this->assertion->setIssuer($this->issuer);
        $this->assertion->setAuthnInstant($this->timeStamp);
        $this->assertion->setNotBefore($this->notBefore);
        $this->assertion->setNotOnOrAfter($this->notOnOrAfter);
        $this->assertion->setAuthnContextClassRef(Constants::NAMEID_UNSPECIFIED);
        $this->assertion->setNameId($this->nameIdElement);
        $this->assertion->setValidAudiences([$this->getEntityId()]);

        // Check if the service provider wants a Recipient defined or not. i.e. SubjectConfirmation
        if ($this->samlClient) {
            if ($this->serviceProvider->want_recipient_defined) $this->setSubjectConfirmation();
        }
        if ($this->subjectConfirmation->getMethod()) $this->assertion->setSubjectConfirmation([$this->subjectConfirmation]);

        // Set assertions on the response
        $this->response->setAssertions([$this->assertion]);

        // Set Relay state on the response if any
        $this->response->setRelayState($this->getRelayState());

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

    /**
     * Set SubjectConfirmation element on the SAMLResponse message
     * 
     * @param string|null $inResponseToId The ID of the AuthnRequest if present
     * @return void
     */
    private function setSubjectConfirmation(?string $inResponseToId = null)
    {
        // Prepare SubjectConfirmation element
        $this->subjectConfirmation->setMethod(Constants::CM_BEARER);
        $this->subjectConfirmationData->setNotOnOrAfter($this->notOnOrAfter);
        $this->subjectConfirmationData->setRecipient($this->getAssertionConsumerServiceURL());
        $this->subjectConfirmationData->setInResponseTo($inResponseToId);
        $this->subjectConfirmation->setSubjectConfirmationData($this->subjectConfirmationData);
    }
}
