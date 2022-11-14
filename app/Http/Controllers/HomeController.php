<?php
   
namespace App\Http\Controllers;
  
use Illuminate\Http\Request;
   
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
   
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    } 

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function superadminHome()
    {
        return view('superadminHome');
    }
   
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function adminHome()
    {
        return view('adminHome');
    }
   
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function managerHome()
    {
        return view('managerHome');
    }

    public function emailtemplate()
    {
        $output = '[{"message":"Session expired or invalid","errorCode":"INVALID_SESSION_ID"}]';
        $payload = '{"From":"+918439778305","To":"13214246735","Body":"T2","aiKey":"","provider_code":"360_SYN14","MessageSid":"Xbj58gUhvJH5VHU9aSTYc4","file_url":"http:\/\/testing.360degreeapps.com\/smsapp\/syniverse\/Xbj58gUhvJH5VHU9aSTYc4_inbound.txt","MediaContentType":"image\/jpeg","MediaUrl":"","docname":"WhatsApp_image_1666076956916","instance_url":"https:\/\/unilever--CGStaging.my.salesforce.com","access_token":"00D1w0000008aWQ!ARgAQKGWdRa39II9F2YNeZVtryusDYXaKmyUhOmdMmmi90EovnDf2Uqgpdvc.8KKWgP06L4p0D_8WftoM8E28m5fp3ugPot8","name_space_sf":"tdc_tsw"}';
        return view('report',compact('output','payload'));
    }
}