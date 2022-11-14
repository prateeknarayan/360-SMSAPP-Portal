<?php

namespace App\Libraries;
use App\Libraries\Salesforce;
use Illuminate\Support\Facades\Mail;

class Twilio
{
   
    protected $baseurl;

    public function __construct()
    {
        $this->baseurl = 'https://live-backup-smsapp.staging360degreecloud.com/V2/twilio';
    }

    /**
     * Handles twilio requests
     *
     * @param  Process  $process
     * @param  FlowMapping  $mapping
     * @param  ProcessDataReceived  $processDataReceived
     */
    public function handleWebhookRequest($postData)
    {
        $body = isset($postData["Body"]) ? $postData["Body"] : ""; 
        $to = isset($postData["To"]) ? $postData["To"] : "";
        $to = preg_replace("/[^0-9]/", "", $to);
        $from = isset($postData["From"]) ? $postData["From"] : "";
        $NumMedia = isset($postData["NumMedia"]) ? $postData["NumMedia"] : "";
        $messageText = isset($postData["messageText"]) ? $postData["messageText"] : "";
        $mediaurl = isset($postData["MediaUrl0"]) && !empty($postData["MediaUrl0"]) ? $postData["MediaUrl0"] : "";
        $MediaContentType = isset($postData["MediaContentType0"]) && !empty($postData["MediaContentType0"]) ? $postData["MediaContentType0"] : "";
        $Latitude = isset($postData["Latitude"]) ? $postData["Latitude"] : "";
        $Longitude = isset($postData["Longitude"]) ? $postData["Longitude"] : "";
        $ProfileName = isset($postData["ProfileName"]) ? $postData["ProfileName"] : "";
        $OtherRecipients0 = isset($postData["OtherRecipients0"]) ? $postData["OtherRecipients0"] : "";
        $OtherRecipients1 = isset($postData["OtherRecipients1"]) ? $postData["OtherRecipients1"] : "";
        $OtherRecipients2 = isset($postData["OtherRecipients2"]) ? $postData["OtherRecipients2"] : "";
        $OtherRecipients3 = isset($postData["OtherRecipients3"]) ? $postData["OtherRecipients3"] : "";
        $OtherRecipients4 = isset($postData["OtherRecipients4"]) ? $postData["OtherRecipients4"] : "";
        
        // Authentication start
        $all_headers = getallheaders();
        $parameter = $postData;
        ksort($parameter);
        $keyvaluestring = "";
        foreach ($parameter as $x => $x_value) {
            $keyvaluestring.= $x . $x_value;
        }

        $baseurl = $this->baseurl;
        $url = $baseurl . $keyvaluestring;
        $to = isset($postData["To"]) ? $postData["To"] : "";
        $to = preg_replace("/[^0-9]/", "", $to);
        //Fecthing record for the authentication purpose
        
        $salesforce = new SalesForce();
        $orgNumberDetails = $salesforce->fetchOrgAndNumberDetails($to);
    // echo "<pre>"; print_r($orgNumberDetails);die;
        if (!empty($orgNumberDetails)) {
            $authToken = $orgNumberDetails->token;
            $hmac = hash_hmac("sha1", $url, $authToken, true);
            $createdTwillioSignature = base64_encode($hmac);
      

            if ($all_headers["X-Twilio-Signature"] == $createdTwillioSignature) {
                if(!empty($postData["To"])){
                       $media_url = "";
                       $filename = "";
                       $aiKey = "false";
                       if(!empty($NumMedia) && $NumMedia > 0){
                            $txtname = "AllProviderImg/".$postData['MessageSid']."_twilio".date("dmyhis").".txt";
                            for ($a=0; $a < $postData['NumMedia']; $a++) {
                            $mfile = $postData['MediaUrl'.$a];
                            $mfile = str_replace('https://','@', $mfile);
                            $mfile = 'https://'.$orgNumberDetails["sid"].':'.$orgNumberDetails["token"].$mfile;
                            $sample_url = $salesforce->get_redirect_target($mfile);
                            $s3_url = $salesforce->get_redirect_target($sample_url);
                            $MediaContentType = $postData['MediaContentType'.$a];
                            $count = $postData['NumMedia'] - $a;
                            $file_url = $salesforce->getImageFileUrl($txtname,$MediaContentType,$count,$s3_url);
                        }
                       }else{
                           $file_url = ""; 
                       }
                       $file_url = ""; 
                    $tokenStatus = $salesforce->checkSalesforceAuth($to,$orgNumberDetails,$from,$body,$file_url);
                    $token=json_decode($tokenStatus);
                    if(!empty($token) && array_key_exists("access_token", $token)){
                      
                       $data = ["To" => "+" .trim($to),
                                "From" =>trim($from),
                                "Body" => trim($body),
                                "MessageSid" => trim($postData["MessageSid"]),
                                "NumMedia" => $NumMedia,
                                "MediaUrl" => $media_url,
                                "MediaContentType" => $MediaContentType,
                                "docname" => $filename,
                                "file_url" => $file_url,
                                "latitute" => trim($Latitude), 
                                "longitude" => trim($Longitude),
                                "aiKey" => trim($aiKey),
                                "ProfileName" => $ProfileName,
                                "provider_code" => "360_TW01",
                                "OtherRecipients0" => trim($OtherRecipients0),
                                "OtherRecipients1" => trim($OtherRecipients1),
                                "OtherRecipients2" => trim($OtherRecipients2),
                                "OtherRecipients3" => trim($OtherRecipients3),
                                "OtherRecipients4" => trim($OtherRecipients4)
                               ];
                             
                       $salesforce->sendTosalesforce($token,$data,$orgNumberDetails,$from,$body,$file_url);
                    }else{
                        http_response_code(400);
                        header('Content-Type: application/json');
                        echo json_encode(["status" => "false", "error"=>"invalid_grant","error_description" => "expired access/refresh token"]);
                    }
                }else{
                    http_response_code(400);
                    header('Content-Type: application/json');
                    echo json_encode(["status" => "false", "error"=>"invalid_body_request","error_description" => "Invalid Body"]);
                }
            }else{
                $array["headers"] = $all_headers;
                $array["parameters"] = $parameter;
                $array["result"] = "signature not matched";
                $array["signature_created"] = $createdTwillioSignature;
                $array["signature_getbyheader"] = $all_headers["X-Twilio-Signature"];
                $txtlogname = "logTwilliosignature/" . date("Y-m-d-h-i-s") . "parameterTwilio.txt";
                $req_dump = print_r($array, true);
                $fp = @fopen($txtlogname, "a");
                @fwrite($fp, $req_dump);
                @fclose($fp);
                echo "Twilio Signature Not Matched";
            }
        }else{
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(["status" => "false", "error"=>"invalid_number_token","error_description" => "Number Token Not Exist"]);  
        }
    }

}