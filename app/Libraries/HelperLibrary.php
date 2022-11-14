<?php 
namespace App\Libraries;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class HelperLibrary
{
	// Variable to determine whether to format config dates as a string or return a \DateTime object
    private $config_date_format = false;

    /**
     * The HOST URL currently in use
     *
     * @var string
    */
    protected $rest_client;

    /**
     * The currect base URI being used in a rest request
     *
     * @var
     */
    protected $base_uri;


	public function __construct()
    {

    }


    public function sendGetRequest($data){
        try {
		    $crypted = Crypt::encryptString($data);
		} catch (DecryptException $e) {
		    $crypted = '';
		}
		return $crypted;
    }



    public function sendPostRequest($data){
		try {
		    $decrypted = Crypt::decryptString($data);
		} catch (DecryptException $e) {
		    $decrypted = '';
		}
		return $decrypted;
    }

    /**
     * Executes a REST request
     *
     * @param $inputs
     * ['url']
     * ['method']
     * ['endpoint']
     * ['post_data']
     * ['form_params']
     * ['credentials']
     * ['headers']
     * ['parameters']
     * ['timeout']
     * ['auth']
     * ['handler']
     * ['timeout']
     * @param  bool  $decode_result - whether to use json_decode on the result of the request
     * @param  bool  $logging
     * @return array
     * ['success']
     * ['http_code']
     * ['body']
     * ['error_message']
     *
     * @throws \Exception
     */
    public function guzzleSendGetRequest($inputs, $decode_result = true, $logging = false){
    	if (! isset($inputs['url'])) {
            throw new \Exception('URL parameter not provided', 1);
        }

        // Set the optional parameters to their defaults if not found
        foreach ([
            'method' => 'GET',
            'endpoint' => '',
            'verify' => true,
            'credentials' => false,
            'form_params' => null,
            'headers' => [],
            'parameters' => [],
            'timeout' => isset($inputs['timeout']) && ! empty($inputs['timeout']) ? $inputs['timeout'] : 30,
        ] as $key => $value) {
            if (! isset($inputs[$key])) {
                $inputs[$key] = $value;
            }
        }

        // Instantiate a GuzzleHttp/Client object for each new base URL encountered
        $this->base_uri = ($inputs['url'][strlen($inputs['url']) - 1] == '/') ? substr($inputs['url'], 0, strlen($inputs['url'])) : $inputs['url'];
        $client_params = [
            'base_uri' => $this->base_uri,
            'timeout' => $inputs['timeout'],
        ];
        $client_params['verify'] = $inputs['verify'];
        if (isset($inputs['auth']) && ! empty($inputs['auth']) && isset($inputs['handler']) && ! empty($inputs['handler'])) {
            $client_params['auth'] = $inputs['auth'];
            $handler = HandlerStack::create();
            $handler->push($inputs['handler']);
            $client_params['handler'] = $handler;
        }

        $this->rest_client = new Client($client_params);


        if (! isset($inputs['headers']['Content-Type']) || empty($inputs['headers']['Content-Type'])) {
            $inputs['headers']['Content-Type'] = empty($inputs['form_params']) ? 'application/json' : 'application/x-www-form-urlencoded';
        }

        if ($inputs['credentials']) {
            $this->setRestRequestCredentials($guzzle_options, $inputs);
        }

        try {
            if (is_array($inputs['form_params'])) {
                $guzzle_options['form_params'] = $inputs['form_params']; // for url encoded posts
            } 

            if (! empty($inputs['parameters'])) {
                $guzzle_options['query'] = $inputs['parameters'];
            }

            // Set headers
            if (isset($inputs['headers']) && ! empty($inputs['headers']) && is_array($inputs['headers'])) {
                $guzzle_options['headers'] = isset($guzzle_options['headers']) ? array_merge($guzzle_options['headers'], $inputs['headers']) : $inputs['headers'];
            }

            // Set debugging
            if (isset($inputs['debug'])) {
                $guzzle_options['debug'] = $inputs['debug'];
            }

            if ($logging) {
                $this->logRestRequest($guzzle_options);
            }
            $response = $this->rest_client->request($inputs['method'], $inputs['endpoint'], $guzzle_options);

            return [
                'code' => $response->getStatusCode(),
                'body' => $decode_result ? json_decode($response->getBody()->getContents()) : $response->getBody()->getContents(),
                'error_message' => false,
                'success' => true,
            ];
        } catch (RequestException $re) { // Handle errors in function and return standardised array
            if ($re->hasResponse()) {
                $response = $re->getResponse();

                return [
                    'code' => $response->getStatusCode(),
                    'body' => $response->getBody()->getContents(),
                    'error_message' => Message::toString($response),
                    'success' => false,
                ];
            }
            if (method_exists($re, 'getMessage') && method_exists($re, 'getCode')) {
                throw new \Exception($re->getMessage(), $re->getCode());
            }
            throw new \Exception('An unknown error occured', 1);
        }
    }


    public function guzzleSendPostRequest($inputs, $decode_result = true, $logging = false){
    		if (! isset($inputs['url'])) {
            throw new \Exception('URL parameter not provided', 1);
        }

        // Set the optional parameters to their defaults if not found
        foreach ([
            'method' => 'POST',
            'endpoint' => '',
            'verify' => true,
            'post_data' => null,
            'form_params' => null,
            'credentials' => false,
            'headers' => [],
            'parameters' => [],
            'timeout' => isset($inputs['timeout']) && ! empty($inputs['timeout']) ? $inputs['timeout'] : 30,
        ] as $key => $value) {
            if (! isset($inputs[$key])) {
                $inputs[$key] = $value;
            }
        }

        // Instantiate a GuzzleHttp/Client object for each new base URL encountered
          
        $this->base_uri = ($inputs['url'][strlen($inputs['url']) - 1] == '/') ? substr($inputs['url'], 0, strlen($inputs['url'])) : $inputs['url'];
        $client_params = [
            'base_uri' => $this->base_uri,
            'timeout' => $inputs['timeout'],
        ];
        $client_params['verify'] = $inputs['verify'];
        if (isset($inputs['auth']) && ! empty($inputs['auth']) && isset($inputs['handler']) && ! empty($inputs['handler'])) {
            $client_params['auth'] = $inputs['auth'];
            $handler = HandlerStack::create();
            $handler->push($inputs['handler']);
            $client_params['handler'] = $handler;
        }

        $this->rest_client = new Client($client_params);


        if (! isset($inputs['headers']['Content-Type']) || empty($inputs['headers']['Content-Type'])) {
            $inputs['headers']['Content-Type'] = empty($inputs['form_params']) ? 'application/json' : 'application/x-www-form-urlencoded';
        }

        if ($inputs['credentials']) {
            $this->setRestRequestCredentials($guzzle_options, $inputs);
        }

        try {
            if (in_array($inputs['method'], ['POST', 'PUT', 'PATCH'])) {
                if (is_array($inputs['post_data'])) {
                    $guzzle_options['json'] = $inputs['post_data'];
                } elseif (is_array($inputs['form_params'])) {
                    $guzzle_options['form_params'] = $inputs['form_params']; // for url encoded posts
                } else {
                    $guzzle_options['body'] = is_object($inputs['post_data']) ? json_encode($inputs['post_data']) : $inputs['post_data'];
                }
            }

            if (! empty($inputs['parameters'])) {
                $guzzle_options['query'] = $inputs['parameters'];
            }

            // Set headers
            if (isset($inputs['headers']) && ! empty($inputs['headers']) && is_array($inputs['headers'])) {
                $guzzle_options['headers'] = isset($guzzle_options['headers']) ? array_merge($guzzle_options['headers'], $inputs['headers']) : $inputs['headers'];
            }

            // Set debugging
            if (isset($inputs['debug'])) {
                $guzzle_options['debug'] = $inputs['debug'];
            }

            if ($logging) {
                $this->logRestRequest($guzzle_options);
            }
            $response = $this->rest_client->request($inputs['method'], $inputs['endpoint'], $guzzle_options);

            return [
                'code' => $response->getStatusCode(),
                'body' => $decode_result ? json_decode($response->getBody()->getContents()) : $response->getBody()->getContents(),
                'error_message' => false,
                'success' => true,
            ];
        } catch (RequestException $re) { // Handle errors in function and return standardised array
            if ($re->hasResponse()) {
                $response = $re->getResponse();

                return [
                    'code' => $response->getStatusCode(),
                    'body' => $response->getBody()->getContents(),
                    'error_message' => Message::toString($response),
                    'success' => false,
                ];
            }
            if (method_exists($re, 'getMessage') && method_exists($re, 'getCode')) {
                throw new \Exception($re->getMessage(), $re->getCode());
            }
            throw new \Exception('An unknown error occured', 1);
        }
    }


    /**
     * Sets the appropriate headers to be used in a rest request for the type of auth
     *
     * @param  array  &$options
     * @param  array  $inputs
     * @return void
     *
     * @throws \Exception
     */
    private function setRestRequestCredentials(&$options, $inputs)
    {
        switch (strtolower($inputs['credentials']['auth'])) {
            case 'basic':
                $options['headers']['Authorization'] = 'Basic '.base64_encode($inputs['credentials']['username'].':'.$inputs['credentials']['password']);
                break;

            case 'bearer':
                $options['headers']['Authorization'] = 'Bearer '.$inputs['credentials']['token'];
                break;

            case 'header_token':
                if (isset($inputs['credentials']['headers']) && is_array($inputs['credentials']['headers'])) {
                    foreach ($inputs['credentials']['headers'] as $header => $token) {
                        $options['headers'][$header] = $token;
                    }
                } else {
                    $options['headers'][$inputs['credentials']['header']] = $inputs['credentials']['token'];
                }
                break;

            default:
                throw new \Exception('Unknown auth type specified: '.$inputs['credentials']['auth'], 1);
        }

    }

    /**
     * Logs the URL and headers for a rest request
     *
     * @param  array  $inputs
     * @return void
     */
    private function logRestRequest($inputs)
    {
        Log::debug("Request URI: {$this->base_uri}, Request inputs: ".json_encode($inputs));
    }
}