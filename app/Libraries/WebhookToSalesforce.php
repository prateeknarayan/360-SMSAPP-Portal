<?php

namespace App\Libraries;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProcessRecordEmail;
use App\Models\Process;
use App\Models\ProcessRecord;
use App\Models\DataReceived;

class WebhookToSalesforce
{
    /**
     * @var string AUTH_ENDPOINT
     */
    private const AUTH_ENDPOINT = "/services/oauth2/token";
   
    public function __construct()
    {

    }


  
    /**
     * Handles webhook requests
     *
     * @param  Process  $process
     * @param  FlowMapping  $mapping
     * @param  ProcessDataReceived  $processDataReceived
     */
    public static function handleWebhookRequest(ProcessRecord $processRecord)
    {
        // $this->authorise(); 

        try {
            $senddata = array('status'=> 'Success', 'result' => time(),'data_received' => '{
              "id": 10,
              "title": "HP Pavilion 15-DK1056WM",
              "description": "HP Pavilion 15-DK1056WM Gaming...",
              "price": 1099,
              "discountPercentage": 6.18,
              "rating": 4.43,
              "stock": 89,
              "brand": "HP Pavilion",
              "category": "laptops",
              "thumbnail": "https://dummyjson.com/image/i/products/10/thumbnail.jpeg",
            }');
            if($senddata['status'] == 'Success'){
                $processRecord->status = 'Success';
                $processRecord->destination_result = $senddata['result'];
                $processRecord->save();

                $platformname = $processRecord['process']['platform']->name;
                $pid = $processRecord['process']->platform_id;
                $process = new Process();
                $process->platform_id = $pid;
                $process->processed = 1;
                $process->status = sprintf('Completing %s webhook handler', $platformname);
                $process->save();


                //save data received

                $data = new DataReceived();
                $data->process_record_id = $processRecord->id;
                $data->data_received = $senddata['data_received'];
                $data->save();
                
            }else{
                $processRecord->status = 'Error';
                $processRecord->destination_result = $senddata['result'];
                $processRecord->save();
                //send error Email
                Mail::to('support@tts.com')->send(new ProcessRecordEmail($processRecord));
            }
        } catch (\Exception $e) {
            echo $e->getMessage()."\nLine No:".$e->getLine()."\nFile:".$e->getFile();
        }
    }

    /**
     * Sets auth for the API
     *
     * @param boolean $mock
     *
     * @return void
     * @throws \Exception
     */
    // private function authorise(): void
    // {

        // if ($this->refresh_tokens) {
        //     if ($this->is_cli)
        //         echo "Resetting refresh token...\n";
        //     $this->configs['BEARER_TOKEN'] = $this->getBearerFromBasic();
        //     return;
        // }
//$bearerToken = $this->getBearer();
     //   if ($bearerToken = $this->getBearer()) {
          //  $this->configs['BEARER_TOKEN'] = $bearerToken;
        // } else if ($refreshedBearerToken = $this->getRefreshToken()) {
        //     $this->configs['BEARER_TOKEN'] = $refreshedBearerToken;
        // } else {
        //     throw new \Exception("Auth failed - try resetting the tokens using the 'refresh-tokens' option if permitted to do so", 1);
        // }
    //}

    /**
     * Gets the bearer token if found in cache, else returns false
     *
     * @return string 
     */
    // private function getBearer(): ?string
    // {

    //     $processRecord['platform'];

    //     $tokenObject = $this->salesforceRequest('auth/refresh', array(
    //         'refresh_token' => !empty($token) ? $token : $this->configs['REFRESH_TOKEN']
    //     ), array(), 'POST');

    //     if (!$this->successCode($tokenObject)) {
    //         if ($this->unauthed((array) $tokenObject) === true) {
    //             return;
    //         }
    //         throw new \Exception("Error while trying to retrieve the refreshed token: " . @$tokenObject['error_message'], 1);
    //     }

    //     if (!$token = getObjectProperty($tokenObject['body'], 'access_token', 'string', false, true))
    //         throw new \Exception("Could not find access token in refreshed Bearer Token", 1);

    //     $this->setNewBearer($tokenObject['body']);

    //     return $token;
    // }



}
