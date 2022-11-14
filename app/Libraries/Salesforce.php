<?php

namespace App\Libraries;
use Illuminate\Support\Facades\Mail;
use App\Models\Client;
use App\Models\ProcessRecord;
use App\Models\ProcessDataReceived;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Salesforce
{
    /**
     * @var string
     */
    private string $authType;

    /**
     * Reference to the database process record holding the original source data.
     *
     * @var
     */
    public ProcessDataReceived $process;

    /**
     * Reference to the database process record holding the original source data.
     *
     * @var
     */
    public ProcessRecord $record;



    public $configs = [];


    /**
     * @var Connector
     */
    public Connector $connector;

    /**
     * @var string AUTH_ENDPOINT
     */
    private const AUTH_ENDPOINT = '/services/oauth2/token';

    /**
     * @var string
     */
    private string $tokenCacheKey;

    /**
     * @var object
     */
    private object $accessToken;

    public function __construct($process, $record, $configs)
    {
        $this->process = $process;
        $this->configs = $configs;
        $this->record = $record;
        $this->connector = new Connector($this->process, 'SalesForce');
        $currentchannel = $process['process']['platform']['channels']->where('id',$this->process->channel_id);
        $this->setAuthType();
        $this->tokenCacheKey ='SalesForceToken_'.(string) $this->record->id.'_';
    }


    public function syncRecords()
    {
        $this->authorise();
       // $this->loopSearch($startDate, $ids);
    }

    /**
     * Authorises API calls
     *
     * TODO Add support for using a token generated through the OAuth 2.0 process
     *
     * @param  bool  $reset
     * Whether to reset any cached tokens
     * @return void
     */
    private function authorise($reset = false): void
    {
        // if (! $reset && $this->sourceTokenFromCache() === true) {
        //     return;
        // }
        // if ($this->is_cli) {
        //     echo "refreshing token...\n";
        // }
        $this->getNewToken();
    }

    /**
     * Get cached token
     * returns false if not found, true if found and set
     *
     * @return bool
     */
    private function sourceTokenFromCache(): bool
    {
        if (! Cache::has($this->tokenCacheKey)) {
            return false;
        }
        try {
            $this->accessToken = json_decode(decrypt(Cache::get($this->tokenCacheKey)));

            return true;
        } catch (\Exception $e) {
            Log::error($e);
            if ($this->is_cli) {
                echo "Could not decode access token from Cache, setting a new one...\n";
            }
        }
        return false;
    }

    /**
     * Gets a new token form SalesForce
     *
     * @return void
     *
     * @throws \Exception
     */
    private function getNewToken(): void
    {
        try {
            $this->tokenRequest();
        } catch (\Exception $e) {
            Log::error($e);
            throw $e;
        }
    }

    /**
     * Requests a new Token for user-based auth
     *
     * @return void
     */
    private function tokenRequest(): void
    {
        $result = $this->salesForceRequest($this->getAuthRequestInputs(), true);
        if($result['code'] == 200){
            $this->record->status = 'Success';
            $this->record->destination_result = 'Connected Successfully';
            $this->record->save();
        }else{
            $this->record->status = 'Error';
            $this->record->destination_result = 'Authentication Failed';
            $this->record->save();
        }
        if (! is_object($result['body'])) {
            arrayToObject($result['body']);
        }
        if (Cache::has($this->tokenCacheKey)) {
            Cache::forget($this->tokenCacheKey);
        }
        Cache::put($this->tokenCacheKey, encrypt(json_encode($result['body'])));
        $this->accessToken = $result['body'];
    }

    /**
     * Gets the inputs for an auth request for user auth
     *
     * @return array
     */
    private function getAuthRequestInputs(): array
    {
        return [
            'url' => $this->url(self::AUTH_ENDPOINT),
            'method' => 'GET',
            'parameters' => [
                'grant_type' => 'refresh_token',
                'client_id' => $this->configs['client_id'],
                'client_secret' => $this->configs['client_secret'],
                'refresh_token' => $this->configs['oauth_refresh_token'],
            ],
        ];
    }

    /**
     * Generates a URL from the base API URL and a provided endpoint
     *
     * @param  string  $endpoint
     * @return string
     */
    private function url(string $endpoint): string
    {
        if (strlen($endpoint) > 0 && $endpoint[0] != '/') {
            $endpoint = "/{$endpoint}";
        }
        return "{$this->configs['URL']}{$endpoint}";
    }

    /**
     * Sets the auth type to be used in this flow
     *
     * @return void
     */
    private function setAuthType(): void
    {
        if (! empty(@$this->configs['auth_type'])) {
            $this->authType = $this->configs['auth_type'];
            return;
        } elseif (! empty(@$this->configs['salesforce_auth_type'])) {
            $this->authType = $this->configs['salesforce_auth_type'];
            return;
        }
        $this->authType = 'user';
    }

    /**
     * Makes a request to the SalesForce API
     *
     * @param  array  $inputs
     * @param  bool  $authRequest
     * @return array
     *
     * @throws SalesForceAPIException
     * @throws \Throwable
     */
    private function salesForceRequest(array $inputs, bool $authRequest = false): array
    {
        if (! $authRequest) {
            $this->authInputs($inputs);
        }
        try {
            $payload = $this->connector->restRequest($inputs);
            
            $this->checkAPIResponse($payload);
            

            return $payload;
        } catch (\Throwable $th) {
            return $this->handleAPIError($th, $inputs);
        }
    }


    /**
     * Handles API error
     *
     * @param  \Throwable  $e
     * @param  array  $inputs
     * @return array
     *
     * @throws SalesForceAPIException
     * @throws SessionExpiredException
     * @throws \Exception
     * @throws \Throwable
     */
    private function handleAPIError(\Throwable $e, array $inputs): ?array
    {
        if ($e instanceof SessionExpiredException) {
            if ($this->is_cli) {
                echo "{$e->getMessage()}\n";
            }
            $this->authorise(true);

            return $this->salesForceRequest($inputs);
        } elseif ($e instanceof SalesForceAPIException) {
            Log::error($e->loggable());
            throw $e;
        }
        Log::error($e);
        throw $e;
    }


    /**
     * Checks whether a session expired error has been received
     *
     * @param  int  $code
     * @param  array  $payload
     * @return bool
     */
    private function sessionExpired(int $code, array $payload): bool
    {
        if ($code != 401) {
            return false;
        }
        $body = $payload['body'];
        if (is_object($body) || is_array($body)) {
            $body = json_encode($body);
        }
        $body = (string) $body;

        return strpos($body, 'INVALID_SESSION_ID') !== false;
    }

    /**
     * Checks an api response, if errant, throws an error
     *
     * @param  array  $payload
     * @return void
     *
     * @throws SessionExpiredException
     * @throws SalesForceAPIException
     * @throws \Exception
    */
    
    private function checkAPIResponse(array $payload): void
    {
        if (! isset($payload['code'])) {
            throw new \Exception("Code not found in payload:\n".json_encode($payload), 1);
        }
        $code = (int) $payload['code'];
        if ($this->sessionExpired($code, $payload) === true) {
            $this->record->status = 'Error';
            $this->record->destination_result = 'Session Expired';
            $this->record->save();
            throw new SessionExpiredException();
        }
        if ($code < 200 || $code >= 300) {
            $this->record->status = 'Error';
            $this->record->destination_result = 'Authentication Failed';
            $this->record->save();
        }
    }

    /**
     * Applies the relevant Authorization to request inputs
     *
     * @param  array  &$inputs
     * @return void
     */
    private function authInputs(array &$inputs): void
    {
        switch ($this->authType) {
            case 'user':
                $this->userAuthRequestInputs($inputs);

                return;

            default:
                // TODO
                return;
        }
    }

    /**
     * Returns the inputs for a request with the auth inputs populated
     *
     * @param  array  &$inputs
     * @return array
     */
    private function userAuthRequestInputs(array &$inputs)
    {
        $inputs['credentials'] = [];
        $inputs['credentials']['auth'] = 'bearer';
        $inputs['credentials']['token'] = $this->accessToken->access_token;
    }



    public function fetchOrgAndNumberDetails($toNumber){
       // Need to check the scerios of same two number and empty data 
        $result = Client::with('numbers')->whereHas('numbers', function($q) use ($toNumber) {
                $q->where('number','+'.trim($toNumber).'')->orWhere('number',''.trim($toNumber).'');
            })->first();
        return $result;
        
    }
    


    
    // public function sendTosalesforce($token,$data,$orgNumberDetails,$from,$body,$file_url){
    //    // echo "<pre>";print_r($token); die;
    //     $data = array_filter($data, 'strlen');
    //     $append = http_build_query($data);
    //     $request_type="POST";
    //     $request_url=$token->instance_url."/services/apexrest/".$token->name_space_sf."/IncomingSMS?".$append;
    //     $request_data="";
    //     $request_header=array("Authorization: Bearer $token->access_token");
    
    //     $curlresponse = $this->curlRequest($request_type,$request_url,$request_data,$request_header);
    //     if($curlresponse == "Incoming SMS is received"){
    //         echo $curlresponse;
    //     }else{
    //        http_response_code(400);
    //        header('Content-Type: application/json');
    //        echo json_encode(["status" => "false", "error"=>"Something Went Wrong Please Try Again Later","error_description" => $curlresponse]);
    //        $this->incoming_error_send_mail($orgAndNumberDetails,$from,$body,$file_url,$curlresponse); 
    //     }
    // }
    
    public function sendTosalesforceV2($token,$data,$orgNumberDetails,$from,$body,$file_url){
       // echo "<pre>";print_r($token); die;

        $data = array_filter($data, 'strlen');
        /* Encryption Start */
        $simple_string = json_encode($data);  // Store the cipher method
        $ciphering = "AES-128-CBC"; // Use OpenSSl Encryption method
        $iv_length = openssl_cipher_iv_length($ciphering);  // Non-NULL Initialization Vector for encryption
        $options = 0;
        $encryption_iv = "360SMS-Secret-IV"; // Store the encryption key
        $encryption_key = "360SMS-Secrt-key"; // Use openssl_encrypt() function to encrypt the data
        $post_data = openssl_encrypt($simple_string, $ciphering, $encryption_key, $options, $encryption_iv);

         /* Encryption End */
         
        $request_type="POST";
        $request_url=$token->instance_url."/services/apexrest/".$token->name_space_sf."/IncomingSMS";
        $request_header=array("Authorization: Bearer $token->access_token", "Content-Type: text/plain");
    
        $curlresponse = $this->curlRequest($request_type,$request_url,$post_data,$request_header);
        if($curlresponse == "Incoming SMS is received"){
            echo $curlresponse;
        }else{
           http_response_code(400);
           header('Content-Type: application/json');
           echo json_encode(["status" => "false", "error"=>"Something Went Wrong Please Try Again Later","error_description" => $curlresponse]);
           $this->incoming_error_send_mail($orgAndNumberDetails,$from,$body,$file_url,$curlresponse);  
        }
    }
    
    public function getImageFileUrl($txtname,$MediaContentType,$count,$s3_url){
        $fileInString   = @base64_encode( @file_get_contents($s3_url));
        $myfile         = @fopen($txtname, "a");
        @fwrite($myfile, $MediaContentType);
        @fclose($myfile);

        $myfile         = @fopen($txtname, "a");
        @fwrite($myfile, '<-ftype->');
        @fclose($myfile);

        $myfile         = @fopen($txtname, "a");
        @fwrite($myfile, $fileInString);
        @fclose($myfile);
        if($a < ($count - 1) )
        {
            $myfile         = @fopen($txtname, "a");
            @fwrite($myfile, '<-splitbyme->');
            @fclose($myfile);
        }
        $final = $count - 1;
        if($final == 0){
            $file_url="https://live-backup-smsapp.staging360degreecloud.com/V2/".$txtname;
            return $file_url;
        }
    }
    
    public function get_redirect_target($url){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers = curl_exec($ch);
        curl_close($ch);
        if (preg_match('/^Location: (.+)$/im', $headers, $matches))
            return trim($matches[1]);
        return $url;
    }
    
    public function incoming_error_send_mail($orgAndNumberDetails,$from,$body,$file_url,$curl_response){
            $emailsubject  = "Incoming Failed : " .$orgAndNumberDetails['clientname'].", ".$orgAndNumberDetails['org_id'].", ".$orgAndNumberDetails['number'];
            $emailmessage .="<html><body>";
            $emailmessage .="<p>Incoming SMS failed to save in client’s org due to the below error:</p>";
            $emailmessage .="</br>";
            $emailmessage .="<p><b>Error Message :  ".$curl_response."</b></p>";
            $emailmessage .="</br>";
            $emailmessage .="<p>Below is the failed Message details:</p>";
            $emailmessage .="</br>";
            $emailmessage .="<p><b>To : " .$orgAndNumberDetails['number']." </b></p>";
            $emailmessage .="</br>";
            $emailmessage .="<p><b>From : " .$from." </b></p>";
            $emailmessage .="</br>";
            $emailmessage .="<p><b>Body : ".$body."</b></p>";
            if(!empty($file_url)){
            $emailmessage .="</br>";
            $emailmessage .="<p><b>File Url : ".$file_url."</b></p>";  
            }
            $emailmessage .="</br>";
            $emailmessage .="</br>";
            $emailmessage .="</br>";
            $emailmessage .="<p style='margin-top:40px;'>Thanks,</p>";
            $emailmessage .="<p style='margin-top:0px;'>360 Server Team</p>";
            $emailmessage .="</br>";
            $emailmessage .="</body></html>";
            $mail = new MyMail();
            $result_mail = $mail->sendMail('test@test.hu', 'youremail@yourdomain.com', $emailsubject, $emailmessage);
            exit();
    }
    
}

class SalesForceAPIException extends \Exception
{
    /**
     * @var array
     */
    private array $payload;

    /**
     * @var string
     */
    private string $sfMessage = 'Unknown Error';

    /**
     * @var string
     */
    private string $rawPayload;

    /**
     * @var int
     */
    private int $sfCode;

    /**
     * Constructor
     *
     * @param  array  $payload
     * @return void
     */
    public function __construct(array $payload)
    {
        $this->rawPayload = json_encode($payload);
        $this->payload = $payload;
        $this->sfCode = @$payload['code'] !== null && is_numeric($payload['code'])
            ? (int) $payload['code']
            : 500;
        $this->getSFMessage();
        parent::__construct($this->sfMessage, $this->sfCode);
    }

    /**
     * Creates an appropriate message
     *
     * @return void
     */
    private function getSFMessage(): void
    {
        if (! isset($this->payload['body'])) {
            return;
        }
        $message = $this->createMessageString();
        if (! empty($message)) {
            $this->sfMessage = $message;

            return;
        }
    }

    /**
     * Parses and returns the payload's body
     *
     * @return mixed
     */
    private function payloadBody()
    {
        if (is_string($this->payload['body'])) {
            $payload = json_decode($this->payload['body']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->sfMessage = $this->payload['body'];

                return null;
            }

            return $payload;
        }
        $payload = $this->payload['body'];

        return $payload;
    }

    /**
     * Creates a message string
     *
     * @return string
     */
    private function createMessageString(): ?string
    {
        $message = '';
        if (null !== $payload = $this->payloadBody()) {
            if (is_array($payload)) {
                foreach ($payload as $property) {
                    $this->updateMessage($message, $property);
                }
            } else {
                $this->updateMessage($message, $payload);
            }
        }

        return $message;
    }

    /**
     * Updates a message
     *
     * @param  string  &$message
     * @param  mixed  $property
     * @return void
     */
    private function updateMessage(string &$message, $property): void
    {
        if ($this->objectOrStringInput($property) === true) {
            $update = $this->extractError($property);
            if (! empty($update)) {
                $message .= $update;
            } else {
                $message .= json_encode($property);
            }
        } else {
            $message .= "{$property}\n";
        }
    }

    private function objectOrStringInput(&$input): bool
{
    if (is_array($input)) {
        $input = json_encode($input);

        return false;
    } elseif (is_object($input)) {
        return true;
    } elseif (! is_string($input)) {
        $input = (string) $input;

        return false;
    }
    $result = json_decode($input);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $input = (string) $input;

        return false;
    }
    $input = $result;

    return true;
}
private function getObjectProperty($object, $chain, $type = false, $value = false, $not_empty = false, $numeric = false, $returnNull = false)
{
    // Split the chain into properties
    $properties = preg_split('~(?<!\\\\)\.~', $chain);

    // Iterate through the properties and check for their existence and return the end
    $root = $object;
    foreach ($properties as $property) {
        if (strpos($property, '\.') !== false) {
            $property = str_replace('\.', '.', $property);
        }
        if (is_array($root)) {
            if (isset($root[$property])) {
                $root = $root[$property];
            } else {
                return $returnNull ? null : false;
            }
        } elseif (is_object($root)) {
            if (property_exists($root, $property)) {
                $root = $root->$property;
            } else {
                return $returnNull ? null : false;
            }
        }
    }

    if ($type && gettype($root) != $type) {
        return $returnNull ? null : false;
    }
    if ($value && $root != $value) {
        return $returnNull ? null : false;
    }
    if ($not_empty && empty($root)) {
        return $returnNull ? null : false;
    }

    if ($numeric && ! is_numeric($root)) {
        return $returnNull ? null : false;
    }

    return $root;
}
    /**
     * Extracts an error's details
     *
     * @param  object  $payload
     * @return string
     */
    private function extractError(object $payload): string
    {
        $message = '';

        foreach (['errorCode', 'message', 'error', 'error_description', 'fields'] as $prop) {
            if (false !== $detail = $this->getObjectProperty($payload, $prop)) {
                if (is_array($detail) || is_object($detail)) {
                    $detail = json_encode($detail);
                } else {
                    $detail = (string) $detail;
                }
                $detail = trim($detail);
                $message .= "{$detail}\n";
            }
        }

        return $message;
    }

    /**
     * Returns a loggable exception
     *
     * @return \Exception
     */
    public function loggable(): \Exception
    {
        $e = new \Exception(
            "SalesForce API Error\n{$this->sfMessage}\n{$this->rawPayload}",
            $this->sfCode
        );

        return $e;
    }
}

class SessionExpiredException extends \Exception
{
    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct('session expired', 401);
    }
}