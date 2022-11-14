<?php

namespace App\Http\Controllers;

// use App\Jobs\WebhookJob;
use App\Libraries\Salesforce;
use App\Models\Process;
use App\Models\ProcessRecord;
use App\Models\Channel;
use App\Models\ProcessLog;
use App\Models\SupportedPlatform;
use App\Models\ProcessDataReceived;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Helpers\Helper;

class WebhookController extends Controller
{
    //
    /**
     * Constructs this class
     *
     * @return void
     */
    public $platformname = '';

    public $channel_id = '';

    public $configs = [];
    
    public function __construct()
    {
       $this->middleware('auth.webhook');
    }

    /**
     * Handles a webhook request
     *
     * @param  FlowMapping  $mapping
     * @param  string  $platform
     * @param  Request  $request
     * @return void
     *
     * @throws WebhookAuthException
    */

    public function webhook(Request $request, $platform)
    {
        $this->platformname = $platform;
        $check_platform = SupportedPlatform::with('channels')->where('code',$platform)->first();

        if($check_platform){
            $payload = Helper::getRequestData($request);
            $pid = $check_platform->id;
            $process = $this->createProcess($pid);

            try {

                //identify which channel data is received
                $channels = $check_platform['channels'];
                $channel = $this->identifyChannel($payload,$channels);
                $this->channel_id = $channel->id;

                $pdr = $this->createPDR($process, $payload);
                $toNumber = 0;
                if(isset($_POST['to'])){
                    $toNumber = $_POST['To'];
                }
                if(isset($_POST['source'])){
                    $toNumber = $_POST['source'];
                }

                //decode data
                $channel = json_decode($channel->pivot->configs);
                $channel = Helper::objectToArray($channel);
                $decoded_data = $this->decoder($payload,$channel);

                //create mockup json file
                $this->createMockJson($decoded_data);

                $record = $this->createProcessRecord($pdr);

                $client = Client::whereHas('numbers', function($q) use ($toNumber) {
                            $q->where('number', $toNumber);
                        })->first();

                if($client){
                    //send data to salesforce library
                    $this->configs['client_id'] = $client->client_id;
                    $this->configs['client_secret'] = $client->client_secret;
                    $this->configs['oauth_refresh_token'] = $client->oauth_refresh_token;
                    
                    if($client->org_type == 'production'){
                        $this->configs['URL'] = 'https://login.salesforce.com';
                    }else{
                        $this->configs['URL'] = 'https://login.test.salesforce.com';
                    }

                    //connect with salesforce library
                    $salesforce = new Salesforce($pdr, $record, $this->configs);
                    $salesforce->syncRecords();
                }else{

                    //store record with error
                    $record->status = 'Error';
                    $record->destination_result = 'Sorry, number not found';
                    $record->save();

                }


                //send data to salesforce
               
            } catch (Throwable $th) {
                Log::error($th);
            }

            return response()->json(
                ['success' => true]
            );
        }else{
            return response()->json(['error' => 'platform not supported'], 401);
        }
   
    }

    public function identifyChannel($payload,$channels){
        $foundchannel = array();
        foreach($channels as $channel){
            $channelarray = json_decode($channel->pivot->configs);
            $channelarray = Helper::objectToArray($channelarray);
            if(!(array_diff_key($payload,$channelarray))){
                $foundchannel = $channel;
            }
        }
        return $foundchannel;
    }


    public function decoder($payload,$channel){
        $decoded_array = array();
        foreach($channel as $key=>$value){
            $decoded_array[$key] = $payload[trim($value, '{}')];
        }
        return $decoded_array;
        // echo "<pre>";
        // print_r($decoded_array);
        // die();
    }

    /**
     * Creates a file from the provided payload.
     *
     * @param $json
     * @return void
     */
    public function createMockJson($json, $append = false)
    {

        if (! is_string($json)) {
            $json = json_encode($json);
        }
        if ($append) {
            file_put_contents(public_path().'/mockSalesforceJson.json', $json.PHP_EOL, FILE_APPEND);

            return;
        }
        file_put_contents(public_path().'/mockSalesforceJson.json', $json.PHP_EOL);

    }


    public function getPayload($request, $check_platform){

        $requesttype = $check_platform['requestType']->name;
        switch ($requesttype) {
        case 'Form-Data':
            $payload = $request->all();
            break;

        case 'JSON':
            $payload = $request->getContent();
        break;
        case 'XML':
            $payload = $request->getContent();
            break;
        case 'Text':
            $payload = $request->getContent();
            break;
        }
        return $payload;
    }

    /**
     * Creates a new process to be used in the job
     *
     * @param  FlowMapping  $mapping
     * @param  string  $platform
     * @return Process
     */
    private function createProcess(string $pid): Process
    {
        $process = new Process();
        $process->platform_id = $pid;
        $process->processed = 1;
        $process->status = sprintf('Initialising %s webhook handler', $this->platformname);
        $process->save();

        return $process;
    }

    /**
     * Creates a process data received object from the incoming request
     *
     * @param  Process  $process
     * @param  FlowMapping  $mapping
     * @param  Request  $request
     * @return ProcessDataReceived
     */
    private function createPDR(Process $process,  $payload): ProcessDataReceived
    {
        $jsondata = json_encode($payload);
        $pdr = new ProcessDataReceived();
        $pdr->process_id = $process->id;
        $pdr->channel_id = $this->channel_id;
        $pdr->content = $jsondata;
        $pdr->save();
        return $pdr;
    }


    /**
     * Creates a process record object from the incoming request
     *
     * @param  Process  $process
     * @param  FlowMapping  $mapping
     * @param  Request  $request
     * @return ProcessDataReceived
     */
    private function createProcessRecord(ProcessDataReceived $pdr): ProcessRecord
    {
        $record = new ProcessRecord();
        $record->process_id = $pdr->process_id;
        $record->supported_platform_id = $pdr['process']['platform']->id;
        $record->channel_id = $this->channel_id;
        $record->json_data = $pdr->content;
        $record->status = 'Pending';
        $record->save();

        return $record;
    }
}
