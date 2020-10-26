<?php

namespace ZiaKhan\SamlIdp\Http\Controllers;

use ZiaKhan\SamlIdp\Models\ServiceProvider;
use ZiaKhan\SamlIdp\Models\UserSamlClient;
use ZiaKhan\SamlIdp\Models\SsoPost;
use ZiaKhan\SamlIdp\Providers\Provider;
use ZiaKhan\SamlIdp\SamlIdpConstants;
use ZiaKhan\SamlIdp\Container;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use SAML2\AuthnRequest;
use SAML2\Compat\ContainerSingleton;
use SAML2\HTTPRedirect;

class SamlClientController extends Controller
{
    /**
     * Decide how to prepare a SAMLResponse message.
     * In case of IDP-initiated flow, the driver would be a UserSamlClient
     * In case of SP-initiated flow, the driver would be a general AuthnRequest
     * 
     * @var UserSamlClient|AuthnRequest|null
     */
    protected $responseDriver = null;

    /**
     * Store a new SAML client for an authenticated user.
     * 
     * @param Request $request
     * @return
     */
    public function store(Request $request)
    {
        // First, validate the user's input based on the service provider.
        // Make sure a valid service provider has been specified.
        $validator = Validator::make(
            $request->all(),
            [
                'service_provider' => 'required|numeric|exists:service_providers,id',
            ],
            [
                'service_provider.required' => 'You must specify a service provider.',
                'service_provider.numeric' => 'The specified service provider is invalid.',
                'service_provider.exists' => 'The specified service provider is invalid.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'Input_Data_Validation_Failed',
                'errors' => $validator->errors()->all()
            ]);
        }

        // Identify the specified service provider and perform additional input validation if needed.
        $sp = ServiceProvider::find($request->get('service_provider'));

        // Validate required user input based on the specified service provider
        $validator = Validator::make(
            $request->all(),
            SamlIdpConstants::VALIDATION[$sp->name]['RULES'],
            SamlIdpConstants::VALIDATION[$sp->name]['MESSAGES']
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'Input_Data_Validation_Failed',
                'errors' => $validator->errors()->all()
            ]);
        }

        // Prepare storage data (array) for the specific service provider.
        $store = $this->processUserSamlClientStorage($sp, $request);

        // Store a record for the new SAML client
        try {
            /**
             * Because 'service_provider_id' and 'user_id' are required for all.
             */
            UserSamlClient::create(array_merge(
                [
                    'user_id' => Auth::user()->id,
                    'service_provider_id' => $sp->id
                ],
                $store
            ));

            return response()->json([
                'status' => 'Saml_Client_Addition_Successful'
            ]);
        } catch (\Exception $e) {
            Log::error("There was an error while trying to add a new saml client.", [
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'Saml_Client_Addition_Failure',
                'message' => 'A technical error occurred while trying to create a new federated identity for your account. Please try again later.'
            ]);
        }
    }

    /**
     * Get a list of all SAML client applications.
     *
     * @param Request $request
     * @return Response
     */
    public function getSamlClients(Request $request)
    {
        return response()->json([
            'status' => 'Saml_Clients_Retrieved',
            'payload' => [
                'clients' => $this->parseSamlClients()
            ]
        ]);
    }

    /**
     * Delete a saml client specified by ID
     * 
     * @param Request $request
     * @return Response
     */
    public function delete(Request $request)
    {
        $samlClient = $this->checkSamlClient($request->client_id);
        if (!($samlClient instanceof UserSamlClient)) {
            return $samlClient;
        }

        // Now check if there are any active sharing for this client app. If so, warn the user before deleting the account.
        // if ($this->isUserClientShared($samlClient->id)) {
        //     // The user client is currently shared with other user/users.
        //     return response()->json(UserClientResponseType::ClientAppDeletionFailure, 'This account is currently being shared with other Meveto users. To delete this account, first you need to stop all shared instances for it.');
        // }

        try {
            // Delete saml client
            $samlClient->delete();

            return response()->json([
                'status' => 'Saml_Client_Deletion_Successful',
                'payload' => [
                    'clients' => $this->parseSamlClients()
                ]
            ]);
        } catch (Exception $e) {
            Log::error("A user SAML client with ID `{$samlClient->id}` could not be DELETED.", [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'Saml_Client_Deletion_Failure',
                'message' => 'This SSO account could not be deleted at the moment.'
            ]);
        }
    }

    /**
     * Disable SSO login for a saml client
     *
     * @param Request $request
     * @return Response
     */
    public function disable(Request $request)
    {
        $samlClient = $this->checkSamlClient($request->client_id);
        if (!($samlClient instanceof UserSamlClient)) {
            return $samlClient;
        }

        try {
            $samlClient->revoked = true;
            // Make sure the update is saved
            $samlClient->save();

            return response()->json(['status' => 'Saml_Client_Update_Successful']);
        } catch (Exception $e) {
            Log::error("A user SAML client with ID `{$samlClient->id}` could not be DISABLED.", [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'Saml_Client_Update_Failure',
                'message' => 'This SSO account could not be disabled at the moment.'
            ]);
        }
    }

    /**
     * This method restores a user's client by the ID specified from revoked to normal
     *
     * @param Request $request
     * @return Response
     */
    public function enable(Request $request)
    {
        $samlClient = $this->checkSamlClient($request->client_id);
        if (!($samlClient instanceof UserSamlClient)) {
            return $samlClient;
        }

        try {
            $samlClient->revoked = false;
            // Make sure the update is saved
            $samlClient->save();

            return response()->json(['status' => 'Saml_Client_Update_Successful']);
        } catch (Exception $e) {
            Log::error("A user SAML client with ID `{$samlClient->id}` could not be ENABLED.", [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'Saml_Client_Update_Failure',
                'message' => 'This SSO account could not be enabled at the moment.'
            ]);
        }
    }

    /**
     * Perform a Single Sing On.
     * Authenticate a user to a request SAML service provider.
     * 
     * @param Request $request
     * @return Response
     */
    public function sso(Request $request)
    {
        if ($request->has('saml_client')) {
            // Next ensure that the saml client exists and is owned by the currently authenticated user
            $samlClient = UserSamlClient::find($request->get('saml_client'));

            if (!$samlClient or $samlClient->user_id !== Auth::user()->id) {
                return response()->json([
                    'status' => 'Invalid_Saml_Client',
                    'message' => 'The specified SAML service provider account is invalid.'
                ]);
            }

            // We also need to check if the saml client has been revoked by the user or not
            if ($samlClient->revoked) {
                return response()->json([
                    'status' => 'Saml_Client_Revoked',
                    'message' => 'You have disabled Single Sign-On for this application.'
                ]);
            }

            $this->responseDriver = $samlClient;
        }

        try {
            // If response driver is still NULL, try to receive a SAMLRequest message if present.
            if (!$this->responseDriver) {
                ContainerSingleton::setContainer(new Container);
                $binding = new HTTPRedirect;
                $this->responseDriver = $binding->receive();
            }

            // Instantiate service provider
            $serviceProvider = new Provider($this->responseDriver);

            // Prepare the response
            $serviceProvider->processSamlClient()->setSubjectMetadata()->prepareResponse();

            // Get the container
            $container = $serviceProvider->processXMLDocument()->getContainer();

            // Update instance of the last logged in time
            if ($this->responseDriver instanceof UserSamlClient) {
                $samlClient->last_logged_in = Carbon::now();
                $samlClient->save();
            }

            // Store the HTTP-Post SAMLResponse because the frontend can not handle it directly
            $responseId = Str::random(128);
            SsoPost::create([
                'id' => $responseId,
                'destination' => $serviceProvider->getDestination(),
                'post' => json_encode($container->getData())
            ]);

            return response()->json([
                'status' => 'Saml_SSO_Successful',
                'payload' => [
                    'binding' => 'post',
                    'token' => $responseId
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("There was an error while trying to SSO to a saml client.", [
                'message' => $e->getMessage(),
                'trace' => $e->getTrace()
            ]);
            return response()->json([
                'status' => 'Saml_SSO_Failure',
                'message' => "Opps. there was a problem while trying to process your single sign on request. Please try again later."
            ]);
        }
    }

    /**
     * Parse saml clients for a response
     * 
     * @return array
     */
    private function parseSamlClients(): array
    {
        $clients = [];

        // Get all saml clients that belong to the currently authenticated user.
        $samlSsoList = UserSamlClient::where('user_id', Auth::user()->id)->get();

        foreach ($samlSsoList as $samlSso) {
            $clients[] = [
                'app_id' => $samlSso->id,
                'app_name' => Str::limit($samlSso->serviceProvider->name, 25),
                'app_name_full' => $samlSso->serviceProvider->name,
                // 'app_web_address' => Str::limit($samlSso->serviceProvider->web_address, 25),
                // 'app_web_address_full' => $samlSso->serviceProvider->web_address,
                'last_logged_in_date' => $samlSso->last_logged_in ? $samlSso->last_logged_in->format('D, d M, Y') : null,
                'last_logged_in_time' => $samlSso->last_logged_in ? $samlSso->last_logged_in->format('H:i:s') : null,
                // 'app_login_url' => $samlSso->serviceProvider->login,
                'app_status' => ($samlSso->revoked) ? 0 : 1,
                // 'app_shareable' => ($samlSso->serviceProvider->account_sharing) ? 1 : 0,
            ];
        }

        return $clients;
    }

    /**
     * Check that a saml client specified by ID exist and is owned by the currently
     * authenticated user
     * 
     * @param string $id ID of the saml client
     * @return UserSamlClient|Response UserSamlClient or Error response
     */
    private function checkSamlClient(string $id)
    {
        // Get the user client by ID
        $samlClient = UserSamlClient::find($id);

        // Make sure saml client was found and the currently authenticated user owns it.
        if (!$samlClient || ($samlClient->user_id !== Auth::user()->id)) {
            return response()->json([
                'status' => 'Invalid_Saml_Client',
                'message' => 'Invalid or no saml SSO application has been specified.'
            ]);
        }

        return $samlClient;
    }

    /**
     * Based on the service provider, process data in the request to store a UserSamlClient instance.
     * 
     * @param ServiceProvider $serviceProvider
     * @param Request $request
     * @return array|null The array that should go directly into UserSamlClient::create()
     */
    private function processUserSamlClientStorage(ServiceProvider $serviceProvider, Request $request): ?array
    {
        switch ($serviceProvider->name) {
            case SamlIdpConstants::AWS:
                $metadata = [
                    "https://aws.amazon.com/SAML/Attributes/Role" => [],
                    "https://aws.amazon.com/SAML/Attributes/RoleSessionName" => [$request->get('role_session_name')],
                    "https://aws.amazon.com/SAML/Attributes/SessionDuration" => [$request->get('role_session_time') ?? 3600]
                ];
                foreach ($request->get('roles') as $role) {
                    $metadata['https://aws.amazon.com/SAML/Attributes/Role'][] = "arn:aws:iam::{$request->get('account')}:role/{$role},arn:aws:iam::{$request->get('account')}:saml-provider/{$request->get('provider')}";
                }
                return [
                    'subject_metadata' => json_encode($metadata)
                ];
                break;
            case SamlIdpConstants::ATLASSIAN:
                return [
                    'entity_id' => "https://auth.atlassian.com/saml/{$request->get('org_id')}",
                    'acs_url' => "https://auth.atlassian.com/login/callback?connection=saml-{$request->get('org_id')}",
                    'relay_state' => "https://start.atlassian.com",
                ];
                break;
            case SamlIdpConstants::CLOUDFLARE:
                return [
                    'entity_id' => "https://{$request->get('domain')}.cloudflareaccess.com/cdn-cgi/access/callback",
                    'acs_url' => "https://{$request->get('domain')}.cloudflareaccess.com/cdn-cgi/access/callback"
                ];
                break;
            case SamlIdpConstants::SLACK:
                return [
                    'entity_id' => "https://slack.com",
                    'acs_url' => "https://{$request->get('domain')}.slack.com/sso/saml",
                    'name_qualifier' => "{$request->get('domain')}.slack.com",
                    'spname_qualifier' => "https://slack.com"
                ];
                break;
            case SamlIdpConstants::FASTLY:
                return [
                    'entity_id' => "https://api.fastly.com/saml/{$request->get('token')}",
                    'acs_url' => "https://manage.fastly.com/saml/consume"
                ];
                break;
            case SamlIdpConstants::HUBSPOT:
                break;
            case SamlIdpConstants::GITHUB:
                return [
                    'entity_id' => "https://github.com/orgs/{$request->get('domain')}",
                    'acs_url' => "https://github.com/orgs/{$request->get('domain')}/saml/consume"
                ];
                break;
            case SamlIdpConstants::SALESFORCE:
                return [
                    'entity_id' => "https://{$request->get('domain')}.my.salesforce.com",
                    'acs_url' => "https://{$request->get('domain')}.my.salesforce.com"
                ];
                break;
            case SamlIdpConstants::DROPBOX:
                return [];
                break;
        }

        return null;
    }
}
