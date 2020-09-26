<?php

namespace ZiaKhan\SamlIdp\Http\Controllers;

use ZiaKhan\SamlIdp\Modals\ServiceProvider;
use ZiaKhan\SamlIdp\Modals\UserSamlClient;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use ZiaKhan\SamlIdp\Modals\SsoPost;

class SamlClientController extends Controller
{
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

        // TODO based on the ID of the SP, grab an array for validation from a config or a json file.

        // Assuming AWS, the following additional validation is needed.
        $validator = Validator::make(
            $request->all(),
            [
                'provider' => 'required|string|min:3', // The name of the IDP provider set at AWS
                'account' => 'required|numeric|min:11', // The AWS Account number
                'role_session_name' => 'required|string|min:3', // Name of the login session for AWS
                'role_session_time' => 'nullable|numeric|min:900|max:43200', // The time the login session is valid for in seconds.
                'roles' => 'required|array|min:1', // An array for defining AWS roles.
                'roles.*' => 'required|string|distinct|min:3', // Each role
            ],
            [
                'provider.required' => 'Specify name of the SAML Identity Provider that you set at Amazon Web Services management console.',
                'provider.string' => 'Name of the SAML Identity Provider is invalid.',
                'provider.min' => 'Name of the SAML Identity Provider must be at least :min characters long.',
                'account.required' => 'Specify your Amazon Web Services account number.',
                'account.numeric' => 'Amazon Web Services account is invalid.',
                'account.min' => 'Amazon Web Services account must be at least :min characters long.',
                'role_session_name.required' => 'Specify a session name for the role that you want to login to.',
                'role_session_name.string' => 'The specified role session name is invalid.',
                'role_session_name.min' => 'The role session name must be at least :min characters long.',
                'role_session_time.min' => 'The role session time must be between 900 and 43200 seconds.',
                'role_session_time.max' => 'The role session time must be between 900 and 43200 seconds.',
                'roles.required' => 'Specify Amazon Web Services roles that you would like to assume.',
                'roles.array' => 'The specified roles are invalid.',
                'roles.min' => 'Specify at least 1 Amazon Web Services role.',
                'roles.*.required' => 'Specified Amazon Web Services role can not be empty.',
                'roles.*.string' => 'An invalid Amazon Web Services role has been specified.',
                'roles.*.distinct' => 'Each Amazon Web Services role must be specified only once.',
                'roles.*.min' => 'Make your Amazon Web Services roles at least :min characters long.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'Input_Data_Validation_Failed',
                'errors' => $validator->errors()->all()
            ]);
        }

        // Prepare user related metadata for the specific service provider.

        // * Assuming only AWS
        $metadata = [
            "https://aws.amazon.com/SAML/Attributes/Role" => [],
            "https://aws.amazon.com/SAML/Attributes/RoleSessionName" => [$request->get('role_session_name')],
            "https://aws.amazon.com/SAML/Attributes/SessionDuration" => [$request->get('role_session_time') ?? 3600]
        ];
        foreach ($request->get('roles') as $role) {
            $metadata['https://aws.amazon.com/SAML/Attributes/Role'][] = "arn:aws:iam::{$request->get('account')}:role/{$role},arn:aws:iam::{$request->get('account')}:saml-provider/{$request->get('provider')}";
        }

        // Store a record for the new SAML client
        try {
            UserSamlClient::create([
                'user_id' => Auth::user()->id,
                'service_provider_id' => $request->get('service_provider'),
                'subject_metadata' => json_encode($metadata),
            ]);

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
        // Check for the requested saml client (Service Provider Instance)
        $validator = Validator::make(
            $request->all(),
            [
                'saml_client' => 'required|numeric',
            ],
            [
                'saml_client.required' => 'Specify a service provider account you wish to authenticate to.',
                'saml_client.numeric' => 'The specified service provider account is invalid.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'Input_Data_Validation_Failed',
                'errors' => $validator->errors()->all()
            ]);
        }

        // Next ensure that the saml client exists and is owned by the currently authenticated user
        $samlClient = UserSamlClient::find($request->get('saml_client'));

        if (!$samlClient or $samlClient->user_id !== Auth::user()->id) {
            return response()->json([
                'status' => 'Invalid_Saml_Client',
                'message' => 'The specified SAML service provider account is invalid.'
            ]);
        }

        try {
            $serviceProvider = new $samlClient->serviceProvider->namespace ?? \ZiaKhan\SamlIdp\Providers\Provider::class;
            $serviceProvider->setNameIDValue(Auth::user()->id);
            $container = $serviceProvider->prepareResponse()->setSubjectMetadata(json_decode($samlClient->subject_metadata, true))->processXMLDocument()->getContainer();

            // Update instance of the last logged in time
            $samlClient->last_logged_in = Carbon::now();
            $samlClient->save();

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
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'Saml_SSO_Failure',
                'message' => 'A technical error occurred while trying to use single sign on for your requested service provider. Please try again later.'
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
}
