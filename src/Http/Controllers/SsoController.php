<?php

namespace ZiaKhan\SamlIdp\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use ZiaKhan\SamlIdp\Modals\SsoPost;

class SsoController extends Controller
{
    /**
     * Process a SAMLResponse token from SPA frontend (Meveto dashboard)
     * 
     * @param Request $request
     * @param string $token
     * @return Response
     */
    public function httpPost(Request $request, string $token)
    {
        // Grab record by the provided token
        $sso = SsoPost::where('id', $token)->first();

        // If the token is invalid
        if (!$sso) {
            return response('Bad Request.', 400);
        }

        $destination = $sso->destination;
        $data = json_decode($sso->post, true);
        try {
            // Delete the SSO record as it's not longer needed.
            $sso->delete();
            return response(view('samlidp::sso-post')->with("destination", $destination)->with("data", $data));
        } catch (\Exception $e) {
            Log::error("There was an error while trying to process SsoController@httpPost", [
                'message' => $e->getMessage()
            ]);
            return response("There was an issue with processing your request at the moment.", 500);
        }
    }
}
