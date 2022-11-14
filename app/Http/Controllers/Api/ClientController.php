<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Libraries\HelperLibrary;
use App\Libraries\Twilio;
use App\Models\Client;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    
    public function storeClient(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_name' => 'required',
            'org_id' => 'required',
            'org_type' => 'required',
            'oauth_refresh_token' => 'required',
            'client_id' => 'required',
            'client_secret' => 'required',
            'name_space_sf' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'validation error'], 403);
        }else{
            $helper = new HelperLibrary;

            $client = new Client;
            $client->client_name  = $request->client_name;
            $client->org_id = $request->org_id;
            $client->org_type = $request->org_type;
            $client->sid = $helper->sendGetRequest($request->sid);
            $client->token = $helper->sendGetRequest($request->token); 
            $client->oauth_refresh_token = $helper->sendGetRequest($request->oauth_refresh_token); 
            $client->allow_security_flag = $request->allow_security_flag;
            $client->allow_AI_flag = $request->allow_AI_flag;
            $client->client_id = $helper->sendGetRequest($request->client_id); 
            $client->client_secret =  $helper->sendGetRequest($request->client_secret); 
            $client->name_space_sf = $helper->sendGetRequest($request->name_space_sf); 
            $client->client_email = $request->client_email;
            $client->is_allow_email = $request->is_allow_email;
            $client->is_email_503_allow = $request->is_email_503_allow;
            $client->is_allow_short_url = $request->is_allow_short_url;
            $client->short_url_access_token = $helper->sendGetRequest($request->short_url_access_token); 
            $client->short_url_created_at = $request->name_space_sf; 
            $client->short_url_updated_at = $request->name_space_sf; 
            $client->status = $request->status;
            $client->save();

            return response()->json(['error' => 'Client added Successfully'], 200);
        }
        
    }


    public function getClient(Client $client){
        $helper = new HelperLibrary;

        $data = array('client_name' => $client->client_name,
                    'org_id' => $client->org_id,
                    'org_type' => $client->org_type,
                    'sid' => $helper->sendPostRequest($client->sid),
                    'token' => $helper->sendPostRequest($client->token),
                    'oauth_refresh_token' => $helper->sendPostRequest($client->oauth_refresh_token),
                    'allow_security_flag' => $client->allow_security_flag,
                    'allow_AI_flag' => $client->allow_AI_flag,
                    'client_id' => $helper->sendPostRequest($client->client_id),
                    'client_secret' => $helper->sendPostRequest($client->client_secret),
                    'name_space_sf' => $helper->sendPostRequest($client->name_space_sf),
                    'client_email' => $client->client_email,
                    'is_allow_email' => $client->is_allow_email,
                    'is_email_503_allow' => $client->is_email_503_allow,
                    'is_allow_short_url' => $client->is_allow_short_url,
                    'short_url_access_token' => $helper->sendPostRequest($client->short_url_access_token),
                    'short_url_created_at' => $client->short_url_created_at,
                    'short_url_updated_at' => $client->short_url_updated_at
        );

        return response()->json(['success' => 'Client Details','data' => $data], 200);

    }

    public function twilioRequest(Request $request)
    {
        $this->validate($request, [
            'Body' => 'required',
            'To' => 'required',
            'From' => 'required',
            'NumMedia' => 'required',
            'messageText' => 'required',
            'MediaUrl0' => 'required',
            'MediaContentType0' => 'required'
        ]);

        $data = $request->all();
        $twilio = new Twilio;
        
        $twilio = $twilio->handleWebhookRequest($data);
    
        // return Redirect::to('clients')->with('success','Client created successfully');
    }

    
}
