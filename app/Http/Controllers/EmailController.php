<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use App\Mail\ErrorReport;
use Illuminate\Support\Facades\Redirect;

class EmailController extends Controller
{
    //

    public function index()
    {
        $data = array();
        $data['output'] = '[{"message":"Session expired or invalid","errorCode":"INVALID_SESSION_ID"}]';
        $payload = '{"From":"+918439778305","To":"13214246735","Body":"T2","aiKey":"","provider_code":"360_SYN14","MessageSid":"Xbj58gUhvJH5VHU9aSTYc4","file_url":"http:\/\/testing.360degreeapps.com\/smsapp\/syniverse\/Xbj58gUhvJH5VHU9aSTYc4_inbound.txt","MediaContentType":"image\/jpeg","MediaUrl":"","docname":"WhatsApp_image_1666076956916","instance_url":"https:\/\/unilever--CGStaging.my.salesforce.com","access_token":"00D1w0000008aWQ!ARgAQKGWdRa39II9F2YNeZVtryusDYXaKmyUhOmdMmmi90EovnDf2Uqgpdvc.8KKWgP06L4p0D_8WftoM8E28m5fp3ugPot8","name_space_sf":"tdc_tsw"}';

        $data['payload'] =  json_decode($payload);
        try
        {
          Mail::to('support@example.com')->send(new ErrorReport($data));
          return Redirect::to('superadmin/home')->with('success','Great! Successfully send in your mail');
        }
        catch(Exception $e)
        {
          return Redirect::to('superadmin/home')->with('error','Sorry! Please try again latter');

        }
    }
}
