<?php

namespace App\Libraries;

use App\Models\ProcessRecord;
use App\ProcessDataReceived;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1 as GuzzleOauth;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Psr7\Message;

/**
 * This class provides re-usable functions for multiple connectors
 */
class Connector
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

    /**
     * SOAP client.
     *
     * @var \SoapClient
     */
    protected $soap_client;

    /**
     * SOAP Session.
     *
     * @var \SoapSession
     */
    protected $soap_session;

    /**
     * Expected HTTP satus codes
     *
     * @var
     */
    public static $status_codes;

    /**
     * constructor.
     *
     * @param  App\Model\Process  $process
     * @param  string  $library_name
     *
     * @throws \Exception
     */
    public function __construct($process, $library_name)
    {
        // Configure access to our database Process entry
        $this->process = $process;
        $this->library_name = $library_name;
        $this->is_cli = php_sapi_name() == 'cli';
    }

    /**
     * Sets list of HTTP status codes for namespace
     *
     * @return void
     */
    private static function setStatusCodes()
    {
        if (! isset(self::$status_codes)) {
            self::$status_codes = [
                // Success
                200, 201, 202, 204,

                // Permissions
                301, 302, 303, 304, 307,

                // Errors
                400, 401, 403, 404, 405, 406, 412, 415,

                // Internal
                500, 501,
            ];
        }
    }

    /**
     * Gets an oauth token for the integration mapping; if not found returns false
     *
     * @param  FlowMapping  $flow_mapping
     * @return false|array
     */
    public function getOauthTokens(FlowMapping $flow_mapping)
    {
        $result = [];
        // Check both platforms for the appropriate slug; once found return
        for ($i = 0; $i < 2; $i++) {
            $pi = $i == 0
                ? $flow_mapping->platformInstanceOne()->first()
                : $flow_mapping->platformInstanceTwo()->first();
            if (empty($pi)) {
                continue;
            }
            $platform = $pi->platform()->first();
            if (empty($platform)) {
                continue;
            }
            $auth = $pi->oauth()
                ->whereRaw('token is not null')
                ->where('connected', true)
                ->orderBy('id', 'desc')
                ->first();
            if (empty($auth)) {
                continue;
            }
            $result[$platform->slug] = $auth;
        }

        return $result;
    }

    /**
     * Performs an HTTP request
     *
     * @param  string  $url - the complete URL
     * @param  string  $request_type
     * @param  bool|array  $credentials
     *      ['auth'] the auth type,
     *      ['username'],
     *      ['password]
     * @param  bool|json  $post_data
     * @param  bool|array  $headers
     * @param  bool  $return_headers
     * @param  bool  $return_code
     * @return array|json
     */
    public function _executeHttpRequest($url, $request_type = 'GET', $post_data = null, $credentials = false,$headers = false, $return_headers = false, $return_code = false)
    {
        $curl_headers = [
            'Cache-Control: no-cache',
        ];

        if ($headers) {
            foreach ($headers as $key => $value) {
                $curl_headers[] = $key.': '.$value;
            }
        }

        $curl_opt = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $request_type,
        ];

        if ($credentials) {
            switch ($credentials['auth']) {
                case 'Basic':
                    $curl_opt[CURLOPT_USERPWD] = $credentials['username'].':'.$credentials['password'];
                    break;

                case 'Bearer':
                    $curl_headers[] = 'Authorization: Bearer '.$credentials['token'];
                    break;

                default:
                    throw new \Exception('Unrecognized Auth Type specified in `_executeHttpRequest`', 1);
                    break;
            }
        }

        if (in_array(strtolower($request_type), ['post', 'put']) && $post_data) {
            if (! in_array('Content-Type: application/json', $curl_headers)) {
                $curl_headers[] = 'Content-Type: application/json';
            }
            $curl_opt[CURLOPT_POSTFIELDS] = $post_data;
            $curl_opt[CURLOPT_RETURNTRANSFER] = true;
        }

        $curl_opt[CURLOPT_HTTPHEADER] = $curl_headers;

        if ($return_headers) {
            $curl_opt[CURLOPT_HEADER] = 1;
        }
        // Execute GET request
        try {
            $curl = curl_init();

            curl_setopt_array($curl, $curl_opt);

            $response = curl_exec($curl);

            if ($return_headers || $return_code) {
                $info = curl_getinfo($curl);
            }

            $HTTPcode = isset($info['http_code']) ? $info['http_code'] : false;
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                return $return_code ? ['body' => 'cURL Error #:'.$err, 'http_code' => $HTTPcode] : 'cURL Error #:'.$err;
            } else {
                if ($return_headers) {
                    $header = substr($response, 0, $info['header_size']);
                    $body = substr($response, $info['header_size']);
                    $header_rows = [];
                    if (! empty($header)) {
                        $header_rows = $this->createHeaderRows($header);
                    }
                    $payload = json_decode($body);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return $return_code ? ['headers' => $header_rows, 'body' => $body, 'http_code' => $HTTPcode] : ['headers' => $header_rows, 'body' => $body];
                    }

                    return $return_code ? ['headers' => $header_rows, 'body' => $payload, 'http_code' => $HTTPcode] : ['headers' => $header_rows, 'body' => $payload];
                }
                $payload = json_decode($response);
                // To ensure that the response is not converted to `null` if JSON decoding fails
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return $return_code ? ['body' => $response, 'http_code' => $HTTPcode] : $response;
                }

                return $return_code ? ['body' => $payload, 'http_code' => $HTTPcode] : $payload;
            }
        } catch (\Exception $e) {
            return 'Error: '.$e->getMessage();
        }
    }

    /**
     * Parses the raw string output from cURL and created key => value pairs for headers
     *
     * @param  string  $headers
     * @return array
     */
    private function createHeaderRows($headers)
    {
        $header_rows = explode(PHP_EOL, $headers); // Explode on the new lines in the string
        array_shift($header_rows); // remove the status row
        $indexed = [];
        foreach ($header_rows as $header) {
            if (strpos($header, ':') === false) {
                continue;
            }
            $indexed[substr($header, 0, strpos($header, ':'))] = trim(substr($header, strpos($header, ':') + 1));
        }

        return $indexed;
    }

    /**
     * Performs an oAuth HTTP request
     *
     * @param  string  $url
     * @param  GuzzleOauth  $oauth
     * @param  string  $request_type
     * @param  bool|array  $credentials
     *      ['auth'] the auth type,
     *      ['username'],
     *      ['password]
     * @param  bool|json  $post_data
     * @param  array  $headers
     * @return array|json
     *
     * @throws \Exception
     */
    public function _executeoAuthHttpRequest($url, GuzzleOauth $oauth, $request_type = 'GET', $post_data = null, $headers = [])
    {
        // Prepare HTTP headers - set content type
        $options = [
            'headers' => array_merge([
                'Content-Type' => 'application/json',
            ], $headers),
        ];

        $handler = HandlerStack::create();
        $handler->push($oauth);

        if (in_array(strtolower($request_type), ['post', 'put']) && $post_data) {
            $options['body'] = $post_data;
        }

        $client = new Client([
            'base_uri' => $url,
            'handler' => $handler,
            'auth' => 'oauth',
            'verify' => false, // todo remove this!!
        ]);

        $request = $client->request($request_type, $url, $options);

        // Execute the cURL request
        $response = (string) $request->getBody();
        $http_code = $request->getStatusCode();

        // Check and decode the response
        if ($response) {
            $data = false;
            $response = str_replace("\n", ' ', $response); // Remove carriage returns.
            $data = json_decode($response);
            if (empty($data)) {
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception("Error Decoding Response: $response", 500);
                }
            }

            return $data;
        } else {
            throw new \Exception('No response', 400);
        }
    }

    /**
     * splits a data set by common properties
     *
     * @param  array  $data
     * @return array
     *
     * @throws \Exception
     */
    public function splitDataByProperty($data, $split_by, $transaction_id_property)
    {
        $result = [];
        $transaction_id_warnings = 0;
        foreach ($data as $record) {
            if (is_object($record)) {
                $record = (array) $record;
            }
            // Check that the record has the split by property; if not add to warnings list
            if (! isset($record[$split_by])) {
                if (isset($record[$transaction_id_property])) {
                    $warnings[] = $record[$transaction_id_property];
                    $result['unknown'][] = $record;
                } else {
                    // In this case we will not create a file for the transaction
                    $transaction_id_warnings++;
                }
            } else {
                $result[$record[$split_by]][] = $record;
            }
        }

        // Handle the warnings
        if (isset($warnings)) {
            $this->logWarning(null, "These transactions did not have the property '".$split_by."' and could not be split: ".implode(', ', $warnings));
        }
        if ($transaction_id_warnings) {
            $this->logWarning(null, "$transaction_id_warnings transactions/records did not have record/transaction ID properties");
        }

        return $result;
    }

    /**
     * Creates an array for optional parameters that accept arrays
     *
     * @param  string  $config_values
     * @param  bool  $keep_spaces
     * @return array|bool
     */
    public function createOptionalParametersArray($config_values, $keep_spaces = false)
    {
        $properties_raw = $keep_spaces ? $config_values : str_replace(' ', '', $config_values);

        $properties = preg_split('/\,/', $properties_raw);
        if ($properties && is_array($properties)) {
            return $properties;
        } else {
            return false;
        }
    }

    /**
     * Separates options into key-value pairs by the '=' sign
     *
     * @param  array|string  $array
     * @param  string  $parameter
     * @return array|bool
     */
    public function separateKeyValues($array, $parameter)
    {
        if (is_string($array)) {
            $array = preg_split('/,/', $array);
        }

        $result = [];
        foreach ($array as $key => $value) {
            $value_properties = preg_split('/=/', $value);
            if (count($value_properties) != 2) {
                if ($this->is_cli) {
                    echo "Warning: unrecognised key-value pair in $parameter parameter: $value";
                }
                continue;
            }
            $result[$value_properties[0]] = str_replace('|', ',', $value_properties[1]);
        }

        return (! empty($result)) ? $result : false;
    }

    /**
     * Separates options into arrays representing the various possible conditions
     *
     * @param  array|string  $array
     * @param  string  $parameter
     * @return array|bool - the result is in the form [
     *      [property, condition, value]
     * ... ]
     */
    public function separateConditionalValues($array, $parameter)
    {
        if (is_string($array)) {
            $array = preg_split('/,/', $array);
        }

        $result = [];
        foreach ($array as $key => $value) {
            $value_properties = preg_split('/\|/', $value);
            if (count($value_properties) != 3) {
                if ($this->is_cli) {
                    echo "Warning: unrecognised conditions in $parameter parameter: $value";
                }
                continue;
            }

            if (! in_array($value_properties[1], $this->possibleConditions())) {
                if ($this->is_cli) {
                    echo "Unrecognised condition specified in $parameter: $value_properties[1]\n";
                }
                continue;
            }

            $result[] = [$value_properties[0], $value_properties[1], $value_properties[2]];
        }

        return (! empty($result)) ? $result : false;
    }

    /**
     * Returns an array of the possible conditional operators represented as strings
     */
    public function possibleConditions()
    {
        return [
            '>=',
            '<=',
            '==',
            '=',
            '>',
            '<',
            '!=',
        ];
    }

    /**
     * Parses an existing url and adds prefix if required
     *
     * @param  string  $website
     * @param  string|int  $id
     * @return string
     */
    public function parseWebsite($website, $id)
    {
        if (! filter_var($website, FILTER_VALIDATE_URL)) {
            // Find the issue
            if (! stripos(' '.$website, 'http://') || ! stripos(' '.$website, 'https://')) {
                if (! stripos(' '.$website, 'www.')) {
                    $website = 'http://www.'.$website;
                } else {
                    $website = 'http://'.$website;
                }
            } else {
                if (stripos(' '.$website, 'http://')) {
                    $website = str_replace('http://', '', $website);
                }
                if (stripos(' '.$website, 'https://')) {
                    $website = str_replace('https://', '', $website);
                }
                $website = 'http://www.'.$website;
            }
            // Exclude if this did not work
            if (! filter_var($website, FILTER_VALIDATE_URL)) {
                if ($this->is_cli) {
                    echo 'Warning: record no. '.$id.'\'s webaddress could not be interpreted.';
                }
                if ($this->is_cli) {
                    echo "\n";
                }
                $website = null;
            }
        }

        return $website;
    }

    /**
     * Gets the Netsuite saved-search data
     *
     * @param  string  $type
     * @param  \DateTime|string|false  $start_date
     * @param  bool|object  $input
     * @return array|bool
     *
     * @throws \Exception
     */
    public function runNSSearch($search_mapping_name, $start_date, $input = false)
    {
        $netsuite = new NetsuiteREST($this->process);

        // Get inbound data: Netsuite API (REST)
        if (! $input) {
            $input = new \StdClass;
        }

        // Check to see whether there are additional filter specs and construct the filters
        $filters = false;
        if (property_exists($input, 'ids') && ! empty($input->ids)) {
            $filter = [];
            $filter['field'] = 'internalid';
            $filter['join'] = null;
            if (! is_array($input->ids) || count($input->ids) == 1) {
                $filter['operator'] = 'is';
                $filter['value'] = is_array($input->ids) ? $input->ids[0] : $input->ids;
            } elseif (is_array($input->ids)) {
                $filter['operator'] = 'anyof';
                $filter['value'] = $input->ids;
            }
            $filters[] = $filter;
        }
        if (property_exists($input, 'filters') && is_array($input->filters)) {
            if ($filters === false) {
                $filters = [];
            }
            foreach ($input->filters as $filter) {
                $filters[] = $filter;
            }
        }
        if (is_string($start_date)) {
            $start_date = new \DateTime($start_date);
        }
        if (! property_exists($input, 'last_sync_date') && ($start_date instanceof \DateTime)) {
            $input->last_sync_date = $start_date->format('m/d/Y');
        }
        if (property_exists($input, 'last_sync_date')) {
            $filters[] = [
                'field' => 'trandate',
                'join' => null,
                'operator' => 'onorafter',
                'value' => $input->last_sync_date,
            ];
        }

        if ($this->is_cli) {
            echo "Running Netsuite Search...\n";
        }
        $data = $netsuite->runMappingSearch($search_mapping_name, $filters);
        if (! empty($data)) {
            if (isset($data['error']) && $data['error']) {
                throw new \Exception($data['code'].': '.$data['message'], 1);
            } elseif (! is_array($data) && ! is_object($data)) {
                throw new \Exception('Unknown error', 1);
            }

            return $data;
        } else {
            return [];
        }
    }

    /**
     * Compiles data by provided properties
     *
     * @param  array  $data
     * @param  string  $group_result_by
     * @param  array  $details -
     *
     * Strings (or false)
     * ['line_item_property']
     * ['fulfillment_detail_item_property']
     * ['invoice_detail_item_property']
     * ['package_detail_item_property']
     * ['person_place_detail_item_property']
     * ['vendor_detail_item_property']
     * ['shipto_property']
     *
     * Arrays (or false)
     * ['line_item_properties']
     * ['detail_properties']
     * ['invoice_detail_properties']
     * ['package_detail_properties']
     * ['person_place_detail_properties']
     * ['vendor_detail_properties']
     * ['shipto_properties']
     * @param $cast_as
     * @param $exlude_lib TODO: might remove later
     * @param  bool|array  $place_in_array
     * @return array
     *
     * @throws \Exception
     */
    public function compileData($group_result_by, $data, $details, $cast_as = 'array', $exlude_lib = false, $place_in_array = false)
    {
        $result = [];
        $compile_properties = [];
        $detail_properties = [];
        $mainline = false;

        if (isset($details['mainline_property']) && $details['mainline_property']) {
            $mainline = true;
            $mainline_property = $details['mainline_property'];
            $mainline_value = $details['mainline_value'];
            $mainline_properties = isset($details['mainline_properties']) ? $details['mainline_properties'] : [];
        }
        if (isset($details['line_item_property']) && $details['line_item_property']) {
            $compile_properties['line_items'] = $details['line_item_property'];
            $detail_properties['line_items'] = isset($details['line_item_properties']) ? $details['line_item_properties'] : [];
        }
        if (isset($details['fulfillment_detail_item_property']) && $details['fulfillment_detail_item_property']) {
            $compile_properties['@details'] = $details['fulfillment_detail_item_property'];
            $detail_properties['@details'] = isset($details['detail_properties']) ? $details['detail_properties'] : [];
        }
        if (isset($details['invoice_detail_item_property']) && $details['invoice_detail_item_property']) {
            $compile_properties['@invoice'] = $details['invoice_detail_item_property'];
            $detail_properties['@invoice'] = isset($details['invoice_detail_properties']) ? $details['invoice_detail_properties'] : [];
        }
        if (isset($details['package_detail_item_property']) && $details['package_detail_item_property']) {
            $compile_properties['@package'] = $details['package_detail_item_property'];
            $detail_properties['@package'] = isset($details['package_detail_properties']) ? $details['package_detail_properties'] : [];
        }
        if (isset($details['person_place_detail_item_property']) && $details['person_place_detail_item_property']) {
            $compile_properties['@person_place'] = $details['person_place_detail_item_property'];
            $detail_properties['@person_place'] = isset($details['person_place_detail_properties']) ? $details['person_place_detail_properties'] : [];
        }
        if (isset($details['vendor_detail_item_property']) && $details['vendor_detail_item_property']) {
            $compile_properties['@vendor'] = $details['vendor_detail_item_property'];
            $detail_properties['@vendor'] = isset($details['vendor_detail_properties']) ? $details['vendor_detail_properties'] : [];
        }
        if (isset($details['tracking_numbers_property']) && $details['tracking_numbers_property']) {
            $compile_properties['@tracking_numbers'] = $details['tracking_numbers_property'];
            $detail_properties['@tracking_numbers'] = isset($details['tracking_numbers_properties']) ? $details['tracking_numbers_properties'] : [];
        }
        if (isset($details['shipto_property']) && $details['shipto_property']) {
            $compile_properties['@shipto'] = $details['shipto_property'];
            $detail_properties['@shipto'] = isset($details['shipto_properties']) ? $details['shipto_properties'] : [];
        }

        // Todo: Refactor this loop - extract to function
        foreach ($data as $node) {
            $node = (array) $node;
            if (
                $exlude_lib &&
                (
                    (isset($node['custbody_scs_ss_source']) && $node['custbody_scs_ss_source'] != $exlude_lib)
                    || (isset($node['custbody_ss_source']) && $node['custbody_ss_source'] != $exlude_lib)
                )
            ) {
                continue;
            }
            if (! isset($result[$node[$group_result_by]])) {
                foreach ($compile_properties as $tag => $compile_property) {
                    if ($mainline && $tag == 'line_items' && isset($node[$compile_property])) {
                        $this->checkLineItemsCompileProperty($node, $compile_property, $mainline, $mainline_property, $mainline_value);
                    }
                    if (! empty($node[$compile_property])) {
                        // Populate the details using the properties
                        $node[$tag][$node[$compile_property]] = [];
                        if ($detail_properties[$tag]) {
                            foreach ($detail_properties[$tag] as $detail) {
                                $node[$tag][$node[$compile_property]][$detail] = $node[$detail];
                            }
                        }
                        if (! empty($node[$compile_property])) {
                            $node[$tag][$node[$compile_property]]['@incrementor'] = count($node[$tag]);
                            if (! isset($incrementors)) {
                                $incrementors = [];
                            }
                            $incrementors[$node[$group_result_by]][$tag][$node[$compile_property]] = count($node[$tag]);
                        }
                    }
                    $result[$node[$group_result_by]] = $node;
                }
                // Update each tag-item with a copy of the incrementors ledger
                foreach ($result[$node[$group_result_by]] as $key => $value) {
                    if (in_array($key, ['line_items', '@details', '@invoice', '@package', '@person_place', '@vendor', '@tracking_numbers'])) {
                        foreach ($result[$node[$group_result_by]][$key] as $item_key => &$item_array) {
                            $item_array['@incrementors'] = (isset($incrementors[$node[$group_result_by]])
                                && ! empty($incrementors[$node[$group_result_by]])) ? $incrementors[$node[$group_result_by]] : [];
                        }
                    }
                }
                // Unset the incrementors ledger
                // if ( isset($incrementors) )  unset($incrementors);
            } else {
                foreach ($compile_properties as $tag => $compile_property) {
                    if ($mainline && $tag == 'line_items' && isset($node[$compile_property])) {
                        $this->checkLineItemsCompileProperty($node, $compile_property, $mainline, $mainline_property, $mainline_value);
                    }
                    if (! isset($result[$node[$group_result_by]][$tag][$node[$compile_property]])) {
                        if ($detail_properties[$tag]) {
                            foreach ($detail_properties[$tag] as $detail) {
                                if (! empty($node[$compile_property])) {
                                    $result[$node[$group_result_by]][$tag][$node[$compile_property]][$detail] = $node[$detail];
                                    $result[$node[$group_result_by]][$tag][$node[$compile_property]]['@incrementor'] = count($result[$node[$group_result_by]][$tag]);
                                    if (! isset($incrementors)) {
                                        $incrementors = [];
                                    }
                                    $incrementors[$node[$group_result_by]][$tag][$node[$compile_property]] = count($result[$node[$group_result_by]][$tag]);
                                }
                            }
                        }
                    }
                }
                // Update each tag-item with a copy of the incrementors ledger
                foreach ($result[$node[$group_result_by]] as $key => $value) {
                    if (in_array($key, ['line_items', '@details', '@invoice', '@package', '@person_place', '@vendor', '@tracking_numbers'])) {
                        foreach ($result[$node[$group_result_by]][$key] as $item_key => &$item_array) {
                            $item_array['@incrementors'] = (isset($incrementors[$node[$group_result_by]])
                                && ! empty($incrementors[$node[$group_result_by]])) ? $incrementors[$node[$group_result_by]] : [];
                        }
                    }
                }
            }
            // Deal with main line handling - placed here since the result should be indexed and the result is not necessarily in order
            if ($mainline && isset($node[$mainline_property]) && $node[$mainline_property] == $mainline_value) {
                foreach ($mainline_properties as $ml_prop) {
                    // ! add the prefix `main_` because line-items often have the same labels as the main lines
                    $result[$node[$group_result_by]]['main_'.$ml_prop] = $node[$ml_prop];
                }
            }
        }

        // This will place any specified properties from the header of the entity in an array (some APIs require this)
        if ($place_in_array) {
            foreach ($result as &$record) {
                // param 2 is a copy of record so that source data will be available for all depths - param 2 is not mutable
                $this->placeInArray($record, $record, $place_in_array);
            }
        }

        return $cast_as == 'object' ? toObject($result) : $result;
    }

    /**
     * Places elements in array according to inputs
     *
     * @param  array  &$records
     * @param  array  $map
     * @return void
     */
    private function placeInArray(&$record, $source, $map)
    {
        foreach ($map as $key => $prop) {
            if (isset($record[$key])) { // panic!
                throw new \Exception("$key already exists in the record", 1);
            }
            $record[$key] = [];

            // make the array
            if (is_array($prop)) {
                $this->placeInArray($record[$key], $source, $prop);
            } else {
                $set_prop = false;
                if (! empty($prop) && strlen($prop) > 2) {
                    if ($prop[0] == '{' && $prop[strlen($prop) - 1] == '}') {
                        $set_prop = true;
                        $prop = substr(substr($prop, 0, strlen($prop) - 1), 1);
                    }
                }
                if ($set_prop && isset($source[$prop])) {
                    $record[$key] = $source[$prop];
                }

                if (! $set_prop) {
                    $record[$key] = $prop;
                }

                if (is_array($record[$key]) && empty($record[$key])) {
                    $record[$key] = '';
                }
            }
        }
    }

    /**
     * Checks whether the compile property for a line item is a null or quasi-null value
     *
     * @param  array  &$node
     * @return void
     */
    private function checkLineItemsCompileProperty(&$node, $line_item_property, $mainline = false, $mainline_property = '', $mainline_value = '')
    {
        if (
            ! empty($node[$line_item_property]) &&
            ($mainline && isset($node[$mainline_property]) && $node[$mainline_property] == $mainline_value)
        ) {
            $node[$line_item_property] = '';
        } // replace the main-line property with a blank value so that the record is not added to line-items

    }

    /**
     * Converts an array to an object
     *
     * @param  array  $data
     * @return object
     */
    public function convertArrayToObject($data)
    {
        return json_decode(json_encode($data));
    }

    /**
     * Gets an objects property by the 'chain' provided
     *
     * @param  object  $object
     * @param  string  $chain
     * @param  string|bool  $type
     * @param  string|bool  $value
     * @param  bool  $not_empty
     * @return mixed
     *
     * @throws \Exception
     */
    public function getObjectProperty($object, $chain, $type = false, $value = false, $not_empty = false)
    {
        // Split the chain into properties
        $properties = preg_split('/\./', $chain);

        // Iterate through the properties and check for their existence and return the end
        $root = $object;
        foreach ($properties as $property) {
            if (is_array($root)) {
                if (isset($root[$property])) {
                    $root = $root[$property];
                } else {
                    return false;
                }
            } elseif (is_object($root)) {
                if (property_exists($root, $property)) {
                    $root = $root->$property;
                } else {
                    return false;
                }
            }
        }

        if ($type && gettype($root) != $type) {
            return false;
        }
        if ($value && $root != $value) {
            return false;
        }
        if ($not_empty && empty($root)) {
            return false;
        }

        return $root;
    }

    /**
     * Gets the URL and endpoint for a REST API
     *
     * @param  string|bool  $url
     * @param  bool|string  $type
     * @param  array  $params
     * @param  bool  $use_url_configs
     * @param  bool  $include_slash
     * @return string
     *
     * @throws \Exception
     */
    public function getURL($url, $type = false, $params = [], $include_slash = true)
    {
        if ($include_slash) {
            $base_url = substr($url, -1) == '/' ? $url : $url.'/';
        } else {
            $base_url = $url;
        }

        if ($type) {
            $base_url .= $type.'/';
        }

        if (! empty($params)) {
            $request_vars = http_build_query($params);
            $base_url .= (strpos($base_url, '?') === false) ? '?'.$request_vars : '&'.$request_vars;
        }

        return $base_url;
    }

    /**
     * Sets the base_uri var for this instance
     *
     * @param  string  $base_uri
     * @return void
     */
    private function setBaseURI($base_uri)
    {
        $this->base_uri = $base_uri;

    }

    /**
     * Converts a string representation of a number in scientific notation to an integer in normal notation
     *
     * @param  string  $value
     * @param  mixed  $record_id
     * @return string
     */
    public function fromScientific($value, $record_id = null)
    {
        try {
            if (is_numeric($value)) {
                $result = (int) $value;

                return ($result === 0) ? $value : (string) $result;
            }

            return $value;
        } catch (\Exception $e) {
            $this->logWarning(
                $record_id,
                'Could not convert ID into non-scientific notation: '.$e->getMessage().', in file: '.$e->getFile(),
                $e->getLine()
            );

            return $value;
        }
    }

    /**
     * Finds the records that have erred by id (and reference if specified), and sets the export to failed in Netsuite
     * if the total number of retries exceeds or equals the maximum number of retries allowed
     *
     * @param  \App\Models\ProcessRecord  $process_record
     * @param  int  $max_retries
     * @param  string  $ns_type
     * @param  int  $ns_id
     * @param  string  $export_failed_field
     * @return bool
     */
    public function maxRetries($process_record, $ns_type, $ns_id, $max_retries, $export_failed_field = 'custbody_ss_export_failed')
    {

        // Convert process_record to json because of an issue where $process_record->id returns NULL after $process_record->save()
        // The ID is contained in the "existing" object in $process_record
        $process_record_json_copy = json_decode(json_encode(($process_record)));

        if (! isset($process_record_json_copy->existing)) {
            return false;
        }

        $count = ProcessHistory::select('count')
            ->where('process_record_id', $process_record_json_copy->existing->id)
            ->where('transaction_id', $process_record->transaction_id)
            ->where('flow_id', $process_record->flow_id)
            ->where('account_id', $process_record->account_id)
            ->where('destination_result', $process_record->destination_result)
            ->where('last_status', STATUS_ERROR)
            ->orderBy('updated_at', 'desc')
            ->first();

        // Note that the current fail is included in the count. So retries is the count less the first failure.
        if ($count->count - 1 >= $max_retries) {
            if ($this->is_cli) {
                echo "Record $process_record->transaction_id has reached or exceeded its maximum retries\n";
            }
            // Send the fail status to Netsuite
            (new NetsuiteREST($this->process))->setFieldValue($ns_type, $ns_id, $export_failed_field, 'T');

            return true;
        }

        return false;
    }

    /**
     * Recursively mines errors from an Error payload
     *
     * @param  array|object  $err
     * @param  string  $result
     * @return string
     */
    public function mineErrors($err, $result = '', $main_key = '')
    {
        if (! is_array($err) && ! is_object($err)) {
            return ! empty($result) ? $result.'; '.$main_key.(string) $err : $main_key.(string) $err;
        }
        if (is_object($err)) {
            $err = (array) $err;
        }

        foreach ($err as $key => $value) {
            if (is_string($value)) {
                $category = (! empty($key) && is_string($key)) ? $key.': ' : $main_key;
                $result = (! empty($result)) ? $result.'; '.$category.$value : $category.$value;
                unset($err[$key]);
            }
        }

        foreach ($err as $key => $value) {
            $main_key = (! empty($key) && is_string($key)) ? $key.': ' : $main_key;
            $result .= (empty($result) ? '' : '; ').$this->mineErrors($value, '', $main_key);
        }

        return $result;
    }

    /**
     * Creates a new process record for logging
     *
     * @param  string  $reference
     * @param  string  $message
     * @param  int|string|bool  $line
     * @param  bool  $echo_msg
     * @return void
     */
    public function logWarning($reference, $message, $line = false, $echo_msg = true)
    {
        try {
            Log::warning("$reference: $message".($line !== false ? " on line: $line" : ''));
            if ($echo_msg === true) {
                if ($this->is_cli) {
                    echo "$message\n";
                }
            }
        } catch (\Exception $e) {
            Log::warning('Error logging the warning in '.$this->library_name.' Library: '.$e->getMessage().' on line: '.$e->getLine());
        }

    }

    /**
     * Creates a file from the provided payload.
     *
     * @param $json
     * @return void
     */
    public static function createMockJson($json, $append = false, $name = 'mockJson')
    {
        if (! is_string($json)) {
            $json = json_encode($json);
        }
        if ($append) {
            file_put_contents(public_path()."/$name.json", $json.PHP_EOL, FILE_APPEND);

            return;
        }
        file_put_contents(public_path()."/$name.json", $json.PHP_EOL);

    }

    /**
     * Determines whether a record has been retried the maximum allowed number of times
     *
     * @param $flow_id
     * @param $mapping_id
     * @param $max
     * @return bool
     */
    public function retriesMaxed($flow_id, $mapping_id, $transaction_id, $max = 3)
    {
        return ProcessRecord::retriesMaxed($this->process->account_id, $flow_id, $mapping_id, $transaction_id, $max);
    }

    /**
     * Sets the retry on a transaction
     *
     * @param  int  $flow_id
     * @param  int  $mapping_id
     * @return void
     */
    public function setRetry($flow_id, $mapping_id, $transaction_id)
    {
        ProcessRecord::setRetry($this->process->account_id, $flow_id, $mapping_id, $transaction_id);

    }

    /**
     * Gets and prepares integration configurations
     *
     * @param  array  $input_options
     * ['account_id']               int         The ID of the account that this record belongs to.
     * ['flow_id_or_detail'] int|string  The ID of the integration that this record belongs to, or the descritpion thereof.
     * ['type']                     string      The Type of integration mapping.
     * ['mapping_name']             string      The Name of integration mapping.
     * ['mapping_like']             string      The Likeness of the name.
     * ['mapping_id']
     * @param  array  $required - the keys that are required
     * @return array - always returns an array of various PHP types
     *
     * @throws \Exception
     */
    public function prepareMappingConfigs($input_options, $required = [])
    {
        if (! isset($input_options['details']) || $input_options['details'] !== true) {
            $input_options['details'] = true;
        }
        $configurations = FlowMapping::getConfigs($input_options);
        $set_object = is_object($configurations);
        $result = [];
        $exceptions = [];

        foreach ($configurations as $key => $details) {
            try {
                if (! is_array($details) && ! is_object($details)) {
                    $result[$key] = $details;
                } else {
                    if ($set_object && ! is_array($details)) {
                        $details = (array) $details;
                    }

                    $result[$key] = $details;
                }
            } catch (\Exception $e) {
                $exceptions[$key] = $e->getMessage().' [key] '.$key;
                continue;
            }
        }

        // Go through the required fields and make sure there are no errors for them and that they are not missing
        $fatal = [];
        if (! empty($required)) {
            foreach ($required as $r_key) {
                if (! in_array($r_key, array_keys($result))) {
                    if (isset($exceptions[$r_key])) {
                        $fatal[] = $exceptions[$r_key];
                        unset($exceptions[$r_key]);
                    } else {
                        $fatal[] = "$r_key not found";
                    }
                }
            }
        }

        // go through non-fatal config errors and report them
        if ($this->is_cli && ! empty($exceptions)) {
            foreach ($exceptions as $exception) {
                echo $exception."\n";
            }
        }

        if (! empty($fatal)) {
            throw new \Exception('One or more of the required configurations are missing or errant: '.implode('; ', $fatal), 1);
        }

        return $result;
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
    public function restRequest($inputs, $decode_result = true, $logging = false)
    {
        if (! isset($inputs['url'])) {
            throw new \Exception('URL parameter not provided', 1);
        }

        // Set the optional parameters to their defaults if not found
        foreach ([
            'method' => 'GET',
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
        if (! isset($this->base_uri) || $this->base_uri != $inputs['url']) {
            if (isset($this->rest_client)) {
                $this->rest_client = null;
            }

            $this->base_uri = ($inputs['url'][strlen($inputs['url']) - 1] == '/') ? substr($inputs['url'], 0, strlen($inputs['url'])) : $inputs['url'];
            $client_params = [
                'base_uri' => $this->base_uri,
                'timeout' => $inputs['timeout'],
            ];
            $client_params['verify'] = $inputs['verify'];
            if (isset($inputs['auth']) && ! empty($inputs['auth'])) {
                $client_params['form_params'] = $inputs['auth'];

                // $handler = HandlerStack::create();
                // $handler->push($inputs['handler']);
                // $client_params['handler'] = $handler;
            }

            $this->rest_client = new Client($client_params);
        }

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
     * Logs the URL and headers for a rest request
     *
     * @param  array  $inputs
     * @return void
     */
    private function logRestRequest($inputs)
    {
        Log::debug("Request URI: {$this->base_uri}, Request inputs: ".json_encode($inputs));
    }

    /**
     * Executes a REST request (statically)
     *
     * @param $inputs
     * ['url']
     * ['method']
     * ['endpoint']
     * ['post_data']
     * ['credentials']
     * ['headers']
     * ['parameters']
     * ['timeout']
     * @param  bool  $decode_result - whether to use json_decode on the result of the request
     * @return array
     * ['success']
     * ['http_code']
     * ['body']
     * ['error_message']
     *
     * @throws \Exception
     */
    public static function executeStaticRestRequest($inputs, $decode_result = true)
    {
        if (! isset($inputs['url'])) {
            throw new \Exception('URL parameter not provided', 1);
        }

        // Set the optinal parameters to their defaults if not found
        foreach ([
            'method' => 'GET',
            'endpoint' => '',
            'post_data' => null,
            'credentials' => false,
            'headers' => [],
            'parameters' => [],
            'timeout' => 30,
        ] as $key => $value) {
            if (! isset($inputs[$key])) {
                $inputs[$key] = $value;
            }
        }

        // Instantiate a GuzzleHttp/Client object for each new base URL encountered
        $rest_client = new Client([
            'base_uri' => $inputs['url'],
            'timeout' => $inputs['timeout'],
        ]);

        $guzzle_options = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ];

        if ($inputs['credentials']) {
            self::setStaticRequestCredentials($guzzle_options, $inputs['credentials']);
        }

        try {
            if (in_array($inputs['method'], ['POST', 'PUT'])) {
                if (is_array($inputs['post_data'])) {
                    $guzzle_options['json'] = $inputs['post_data'];
                } else {
                    $guzzle_options['body'] = $inputs['post_data'];
                }
            }

            if (in_array($inputs['method'], ['GET'])) {
                $guzzle_options['query'] = $inputs['parameters'];
            }

            // Set headers
            if (isset($inputs['headers']) && ! empty($inputs['headers'])) {
                $guzzle_options['headers'] = isset($guzzle_options['headers']) ? array_merge($guzzle_options['headers'], $inputs['headers']) : $inputs['headers'];
            }

            // Set debugging
            if (isset($inputs['debug'])) {
                $guzzle_options['debug'] = $inputs['debug'];
            }

            $response = $rest_client->request($inputs['method'], $inputs['endpoint'], $guzzle_options);
            unset($rest_client);

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

            throw new \Exception('An unknown error occured', 500);
        } catch (\Exception $e) {
            Log::error('An error occurred in `Connector::executeStaticRestRequest`: '.$e->getMessage());
            throw new \Exception('An unknown error occured', 500);
        }
    }

    /**
     * Returns array of acceptable status codes
     *
     * @return array
     */
    public static function getStatusCodes()
    {
        if (isset(self::$status_codes)) {
            return (array) self::$status_codes;
        }

        self::setStatusCodes();

        return (array) self::$status_codes;
    }

    /**
     * Static function to set the credentials to be used in a Guzzle request
     *
     * @param  array  &$options
     * @param  array  $credentials
     * @return void
     */
    private static function setStaticRequestCredentials(&$options, $credentials)
    {
        switch (strtolower($credentials['auth'])) {
            case 'basic':
                $options['headers']['Authorization'] = 'Basic '.$credentials['username'].':'.$credentials['password'];
                break;

            case 'bearer':
                $options['headers']['Authorization'] = 'Bearer '.$credentials['token'];
                break;

            case 'header_token':
                if (isset($credentials['headers']) && is_array($credentials['headers'])) {
                    foreach ($credentials['headers'] as $header => $token) {
                        $options['headers'][$header] = $token;
                    }
                } else {
                    $options['headers'][$credentials['header']] = $credentials['token'];
                }
                break;

            default:
                throw new \Exception('Unknown auth type specified: '.$credentials['auth'], 1);
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

    // !this mostly applies to the old Jquery UI and will be deprecated
    // /**
    //  * Prepares a Mapping Configuration based on the descriptive data
    //  *
    //  * @param string $key
    //  * @param array $details
    //  *
    //  * @return mixed
    //  * @throws \Exception
    //  */
    // private function prepareMappingConfig($key, $details)
    // {
    //     switch ($details['type']) {
    //         case 'integer':
    //             if (is_int($details['value']))
    //                 return $details['value'];

    //             // if string attempt conversion to pure int
    //             if (is_string($details['value']) && preg_match('/^\d+$/', $details['value'])) {
    //                 return (int) $details['value'];
    //             }
    //             throw new \Exception("$key: expected an integer. Got: " . $details['value']);
    //         case 'float':
    //             if (!is_numeric($details['value']))
    //                 throw new \Exception("$key: expected a float value. Got: " . $details['value']);
    //             return (float) $details['value'];
    //         case 'checkbox':
    //             return !empty($details['value']) ? true : false;
    //         case 'date':
    //             $date = (new \DateTime($details['value']));
    //             if ($this->config_date_format)
    //                 return $date->format($this->config_date_format);
    //             return $date;
    //         case 'multi_select':
    //             // create an array for this type
    //             if (empty($details['value']))
    //                 return null;
    //             $array = json_decode($details['value']);
    //             if (json_last_error() !== JSON_ERROR_NONE)
    //                 throw new \Exception("An error occurred while retrieving the options for the config $key", 1);
    //             return (array) $array;
    //         default:
    //             // handle as text field by default
    //             return $details['value'];
    //     }
    // }

    /**
     * Sets the variable for config date formats
     *
     * @param  string  $format
     * @return void
     */
    public function setConfigDateFormat($format)
    {
        $this->config_date_format = $format;

    }

    /**
     * Returns the last run date of a sync. If no result is found, a DateTime object of 1970-01-01 is returned.
     *
     * @param  string  $type
     * @param  int  $flow_id
     * @param  string|\DateTime  $live_date
     * @param  bool|int  $mapping_id
     * @param  bool  $return_datetime
     * @param  bool  $return_id
     * @return \DateTime|string
     */
    public function getLastSync($type, $flow_id, $live_date, $mapping_id = false, $return_datetime = true, $return_id = false, $orderBy = 'id')
    {
        if ($orderBy == 'id') {
            // There are large performance benefits to running the query in this way if we can order by ID
            $row = ProcessRecord::select('id')
                ->where('account_id', $this->process->account_id)
                ->where('flow_id', $flow_id)
                ->when($mapping_id, function ($query) use ($mapping_id) {
                    return $query->where('mapping_id', $mapping_id);
                })
                ->where('status', STATUS_SUCCESS)
                ->where('transaction_type', $type)
                ->orderBy($orderBy, 'desc')
                ->first();

            if ($row) {
                $process = ProcessRecord::where('id', $row->id)->first();
            }
        } else {
            $process = ProcessRecord::where('account_id', $this->process->account_id)
                ->where('flow_id', $flow_id)
                ->when($mapping_id, function ($query) use ($mapping_id) {
                    return $query->where('mapping_id', $mapping_id);
                })
                ->where('status', STATUS_SUCCESS)
                ->where('transaction_type', $type)
                ->orderBy($orderBy, 'desc')
                ->first();
        }

        if ($return_id) {
            return $process ? $process->transaction_id : 1;
        }

        if ($return_datetime) {
            $tz = isset($this->timezone) && ($this->timezone instanceof \DateTimeZone); // Adjust the date returned by TZ if necessary
            if (isset($process) && $process) {
                return $tz ? new \DateTime($process->transaction_date, $this->timezone) : new \DateTime($process->transaction_date);
            }

            if (! isset($date) && ! empty($live_date)) { // ! Live date is assumed to be timezone-adjusted
                $date = ($live_date instanceof \DateTime) ? $live_date : new \DateTime($live_date);
            }

            return isset($date) ? $date : new \DateTime();
        }

        return $process ? $process->transaction_date : $live_date;
    }

    /**
     * Gets the Netsuite saved-search data
     *
     * @param  string  $type
     * @param  string  $search_mapping_name
     * @param  \DateTime|string  $start_date
     * @param  array  $ids
     * @return array|bool
     *
     * @throws \Exception
     */
    public function runmappingSearch($type, $search_mapping_name, $start_date = false, $ids = [])
    {
        try {
            // Retrieve data from Netsuite
            if ($this->is_cli) {
                echo "Running mapping search for: $type \n";
            }

            $input = new \StdClass;
            if ($start_date !== false && $start_date instanceof \DateTime) {
                $input->last_sync_date = $start_date->format('m/d/Y');
            }
            $input->ids = $ids;
            $data = $this->runNSSearch($search_mapping_name, $start_date, $input);
            if (! empty($data)) {
                return $data;
            } else {
                return [];
            }
        } catch (\Exception $e) {
            $this->logWarning(null, 'Something went wrong with the Netsuite to '.$this->library_name.' sync in `runmappingSearch`. Code: '.$e->getCode().' | Message: '.$e->getMessage(), $e->getLine());
            throw $e;
        }
    }

    /**
     * Executes a SOAP request
     *
     * @param  string  $endpoint
     * @param  array|\SoapVar  $filters
     * @param  bool  $xml
     * @return array|object
     *
     * @throws \Exception
     */
    public function executeSoapRequest($endpoint, $filters, $xml = false, $report_errors = false)
    {
        try {
            if (! empty($this->soap_session)) {
                $result = $this->soap_client->call($this->session, $endpoint, [$filters]);
            } else {
                $result = $this->soap_client->{$endpoint}($filters);
            }

            if (is_object($result) && ! empty((array) $result)) {
                return $xml ? ['result' => $result, 'xml' => $this->soap_client->__getLastRequest()] : $result;
            }

            if (is_array($result) && count($result) > 0) {
                return $xml ? ['result' => $result, 'xml' => $this->soap_client->__getLastRequest()] : $result;
            }

            return $xml ? ['result' => $result, 'xml' => ''] : [];
        } catch (\SoapFault $fault) {
            $message = 'Something went wrong with the SOAP request. CODE: '.@$fault->fault_code.'; MESSAGE: '.@$fault->faultstring;
            if ($report_errors) {
                return $xml ? ['result' => $fault, 'xml' => $this->soap_client->__getLastRequest()] : $message;
            }
            throw new \Exception($message, 1);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Closes the SOAP client connection.
     *
     * @return void
     */
    public function endSoapSession()
    {
        if ($this->soap_session) {
            if (method_exists($this->soap_client, 'endSession')) {
                $this->soap_client->endSession($this->soap_session);
            }
        }
        $this->soap_session = null;

    }

    /**
     * Begins a SOAP session
     *
     * @param $uname
     * @param $api_key
     * @return void
     */
    public function startSoapSession($uname, $api_key)
    {
        if (isset($this->soap_client)) {
            unset($this->soap_client);
        }
        $this->soap_session = $this->soap_client->login($uname, $api_key);

    }

    /**
     * Sets a custom SOAP client for this library
     *
     * @param  string  $url
     * @param  string  $namespace
     * @return void
     */
    public function setCustomSoapClientOptions($url, $options, $namespace = '')
    {
        try {
            if (isset($this->soap_client)) {
                unset($this->soap_client);
            }
            $this->soap_client = new CustomSoapClientOptions($url, $options, $namespace);
        } catch (\Exception $e) {
            // Make the error more expressive
            throw new \Exception('An error occurred while trying to set the SOAP Client: '.$e->getMessage(), 1);
        }

    }

    /**
     * Sets a custom SOAP client for this library
     *
     * @param  string  $url
     * @param  string  $namespace
     * @return void
     */
    public function setCustomSoapClient($url, $namespace = '')
    {
        try {
            if (isset($this->soap_client)) {
                unset($this->soap_client);
            }
            $this->soap_client = new CustomSoapClient($url, ['trace' => 1], $namespace);
        } catch (\Exception $e) {
            // Make the error more expressive
            throw new \Exception('An error occurred while trying to set the SOAP Client: '.$e->getMessage(), 1);
        }

    }

    /**
     * Sets a custom SOAP client for this library
     *
     * @param  string  $url
     * @param  string  $namespace
     * @param  array  $replacements
     * @return void
     */
    public function setCustomSoapClientWithoutNSRefs($url, $namespace = '', $replacements = false)
    {
        try {
            if (isset($this->soap_client)) {
                unset($this->soap_client);
            }
            $this->soap_client = new CustomSoapClientWithoutNSRefs($url, ['trace' => 1], $namespace, $replacements);
        } catch (\Exception $e) {
            // Make the error more expressive
            throw new \Exception('An error occurred while trying to set the SOAP Client: '.$e->getMessage(), 1);
        }

    }

    /**
     * Recursively converts an array into a collection of SOAP elements
     *
     * @param  array  &$inputs
     * @param  string  $ns
     * @return void
     */
    public function toSoapObject(&$inputs, $ns = null)
    {
        // dd($inputs);
        foreach ($inputs as $key => $value) {
            // ! Note: arrays must be the only element within the parent because the parent is a list of identical items
            if (is_array($value)) { // Arrays represent list elements that need to be neted with the same element name
                $new_inputs = [];
                foreach ($value as $node) {
                    if (is_object($node)) { // Assume here that this object is complete and does not need to be parsed recursively
                        $this->toSoapObject($node, $ns);
                        $new_inputs[] = new \SoapVar(
                            $node,
                            SOAP_ENC_OBJECT,
                            null,
                            null,
                            $key,
                            $ns
                        );
                    } else {
                        $new_inputs[] = new \SoapVar($node, XSD_STRING, null, 'http://www.w3.org/2001/XMLSchema', $key, $ns);
                    }
                }
                $inputs = $new_inputs;
            } elseif (is_object($value)) {
                $this->toSoapObject($inputs->$key, $ns);
                $inputs->$key = new \SoapVar(
                    $inputs->$key,
                    SOAP_ENC_OBJECT,
                    null,
                    null,
                    $key,
                    $ns
                );
            } else {
                $inputs->$key = new \SoapVar($value, XSD_STRING, null, 'http://www.w3.org/2001/XMLSchema', $key, $ns);
            }
        }
    }

    /**
     * Sets a custom SOAP client for this library
     *
     * @param  string  $url
     * @param  string  $namespace
     * @param  array  $replacements
     * @return void
     */
    public function setCustomSoapClientReplace($url, $replacements = false)
    {
        try {
            if (! $this->soap_client) {
                $this->soap_client = new CustomSoapClientReplace($url, ['trace' => 1], $replacements);
            }
        } catch (\Exception $e) {
            // Make the error more expressive
            throw new \Exception('An error occurred while trying to set the SOAP Client: '.$e->getMessage(), 1);
        }

    }

    /**
     * Sets the SOAP client for this library
     *
     * @param  string  $url
     * @return void
     */
    public function setSoapClient($url)
    {
        try {
            if (! $this->soap_client) {
                $this->soap_client = new \SoapClient($url, ['trace' => 1]);
            }
        } catch (\Exception $e) {
            // Make the error more expressive
            throw new \Exception('An error occurred while trying to set the SOAP Client: '.$e->getMessage(), 1);
        }

    }

    /**
     * Sets the SOAP headers
     *
     * @param  array  $headers
     * @param  string  $url
     * @param  string  $endpoint
     * @return void
     *
     * @throws \Exception
     */
    public function setSoapHeaders($headers, $url = false, $endpoint = false)
    {
        $soap_headers = [];
        foreach ($headers as $header) {
            if (isset($header['header_body']) && isset($header['name']) && isset($header['namespace'])) { // support for complex soap headers
                $soap_headers[] = new \SOAPHeader($header['namespace'], $header['name'], toObject($header['header_body']));
            } else {
                if (! $endpoint) {
                    throw new \Exception('Endpoint not provided in `setSoapHeaders`', 1);
                }

                if (! $url) {
                    throw new \Exception('URL not provided in `setSoapHeaders`', 1);
                }

                $soap_headers[] = new \SOAPHeader($url, $endpoint, $headers);
            }
        }

        $this->soap_client->__setSoapHeaders($soap_headers);

    }

    /**
     * Sets the data received var for the library specified
     *
     * @param  App\Libraries  $lib
     * @param  string  $status
     * @param  array|object  $content
     * @param  bool  $global
     * @return void
     */
    public function saveDataReceived($lib, $type, $status, $content, $global = true, string $dataReceivedProp = 'process_data_received'): void
    {
        if (! $global) {
            $processDataReceived = new ProcessDataReceived;
            $processDataReceived->account_id = $lib->process->account_id;
            $processDataReceived->process_id = $lib->process->id;
            $processDataReceived->type = $type;
            $processDataReceived->status = $status;
            $processDataReceived->content = json_encode($content);
            $processDataReceived->save();

            return;
        }
        $lib->{$dataReceivedProp} = new ProcessDataReceived;
        $lib->{$dataReceivedProp}->account_id = $lib->process->account_id;
        $lib->{$dataReceivedProp}->process_id = $lib->process->id;
        $lib->{$dataReceivedProp}->type = $type;
        $lib->{$dataReceivedProp}->status = $status;
        $lib->{$dataReceivedProp}->content = json_encode($content);
        $lib->{$dataReceivedProp}->save();
    }

    /**
     * Sets any configs that are required but not necessarily specified
     *
     * @param  App\Libraries  $lib
     * @param  array  $boilerplate
     * @param  bool  $set_actual_name
     * @return void
     */
    public function setBoilerPlateConfigs($lib, $boilerplate, $set_actual_name = false)
    {
        foreach ($boilerplate as $key => $value) {
            if ($set_actual_name) {
                if (! isset($lib->{strtolower($key)})) {
                    $lib->{strtolower($key)} = $value;
                }
            } else {
                if (! isset($lib->configs[$key])) {
                    $lib->configs[$key] = $value;
                }
            }
        }

    }

    /**
     * Sets the timezone to be used when getting the last synced date
     *
     * @param string|\DateTimeZone|bool
     * @return void
     */
    public function setTimeZone($timezone)
    {
        if (! $timezone) {
            if (isset($this->timezone)) {
                unset($this->timezone);
            }

            return;
        }
        $this->timezone = ($timezone instanceof \DateTimeZone) ? $timezone : new \DateTimeZone($timezone);

    }

    /**
     * Returns a list of files that are pending processing.
     *
     * @param  bool|string  $mock
     * @return mixed
     */
    public function getPendingFiles($extension, $mock = false)
    {
        if ($extension[0] != '.') {
            $extension = '.'.$extension;
        }

        return ProcessFile::when(! $mock, function ($q) {
            return $q->where('status', 'Downloaded from FTP server');
        })->when($mock, function ($q) use ($mock) {
            return $q->where('status', $mock);
        })->where('account_id', $this->process->account_id)
            ->where('file_name', 'like', '%'.$extension)
            ->get();
    }

    /**
     * Gets a local file from the specified path
     *
     * @param  string  $path
     * @param  string  $filename
     * @return App\Models\ProcessFile|null
     */
    public function setLocalFile($path, $filename, $status = 'Local File', $return_file = true)
    {
        if (file_exists($path)) {
            $file = file_get_contents($path);
            // $encoded = base64_encode($file);
            $mock_file = new ProcessFile;
            $mock_file->account_id = $this->process->account_id;
            $mock_file->process_id = $this->process->id;
            $mock_file->status = $status;
            $mock_file->content = $file;
            $mock_file->file_name = $filename;
            $mock_file->save();

            return $return_file ? $file : null;
        } else {
            $this->logWarning(null, "Unable to retrieve local file `$path` - file does not exist");

            return null;
        }
    }

    /* Checks whether an HTTP code is in the success (2xx) range
     *
     * @param int $code
     *
     * @return bool
     */
    public function successCode($code)
    {
        if (empty($code)) {
            return false;
        }
        $code = (int) $code;
        if ($code >= 200 && $code < 300) {
            return true;
        }

        return false;
    }

    /**
     * Parses an XML file and creates a JSON
     *
     * @param  string  $file
     * XML file content
     * @param  string  $namespace
     * @param  array  $manualReplacements
     * @return object
     *
     * @throws \Exception
     */
    public function parseXML(string $file, string $namespace = '', array $manualReplacements = [])
    {
        if (! empty($manualReplacements)) {
            foreach ($manualReplacements as $replace => $with) {
                $file = str_replace($replace, $with, $file);
            }
        }

        $xml = empty($namespace) ?
            new \SimpleXMLElement($file, 0, false) : new \SimpleXMLElement($file, 0, false, $namespace, true);

        // Do this to convert `simpleXML` objects to stdClass objects
        $json = json_encode($xml);

        // Remove `@` symbol from JSON
        $json = str_replace('@attributes', 'attributes', $json);

        return json_decode($json);
    }

    /**
     * Generates raw XML from an array
     *
     * @param  array  $record
     * @param  string  $parent
     * @param  string  $namespace
     * @return string
     */
    public function makeXml(array $record, string $parent, string $namespace = '')
    {
        // These are tested XML-friendly hashes
        $encodedNamespace = $namespace ? 'ee623eca' : '';
        $colonEncode = '61f15bb6';

        $xml = new \SimpleXMLElement('<'.$encodedNamespace.$parent.'/>');
        foreach ($record as $key => $value) { // Itterate through the array and pass to our custom fucntion
            $this->recurseXml($xml, $key, $value, $colonEncode, $encodedNamespace);
        }
        $resultString = $xml->asXML();
        $resultString = str_replace($encodedNamespace, $namespace.':', $resultString);

        return str_replace($colonEncode, ':', $resultString);
    }

    /**
     * Recursively decodes array to xml nodes
     *
     * @param  \SimpleXMLElement  $xml
     * @param  string  $key
     * @param  mixed  $value
     * @param  string  $namespace
     * @return void
     */
    private function recurseXml(\SimpleXMLElement $xml, string $key, $value, string $colonEncode, string $namespace = '')
    {
        if ($key == '@attribute' && is_array($value)) {
            foreach ($value as $key => $value) {
                $sanitisedKey = str_replace(':', $colonEncode, $key);
                $sanitisedValue = str_replace(':', $colonEncode, $value);
                $xml->addAttribute($sanitisedKey, $sanitisedValue);
            }
        } elseif (! is_array($value)) {
            $xml->addChild($namespace.$key, $value);
        } else {
            $nested = $xml->addChild($namespace.$key, null);
            foreach ($value as $key2 => $value2) {
                $this->recurseXml($nested, $key2, $value2, $colonEncode, $namespace);
            }
        }

    }
}

class CustomSoapClient extends \SoapClient
{
    public function __construct($url, $options, $namespace = '')
    {
        $this->namespace = $namespace;
        $this->url = $url;
        parent::__construct($url, $options);
    }

    public function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        $action_url = strpos($action, '.com') !== false ? substr($action, 0, (strpos($action, '.com/') + 5)) : substr($action, 0, strrpos($action, '/')).'/';
        $request = preg_replace('/<ns1:(\w+)/', '<$1 xmlns="'.$action_url.'"', $request, 1);
        $request = preg_replace('/xmlns:ns1/', 'xmlns', $request);
        $request = preg_replace('/<ns1:(\w+)/', '<$1', $request);
        $request = str_replace(['/ns1:', 'xmlns:'.$this->namespace.'="'.$action_url.'"'], ['/', ''], $request);
        $request = str_replace('SOAP-ENV', 'soap', $request);

        $call = parent::__doRequest($request, $location, $action, $version);
        $this->__last_request = $request;

        return $call;
    }
}

class CustomSoapClientWithoutNSRefs extends \SoapClient
{
    public function __construct($url, $options, $namespace = '', $special_replace = false)
    {
        $this->namespace = $namespace;
        $this->url = $url;
        $this->special_replace = $special_replace;
        parent::__construct($url, $options);
    }

    public function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        $action_url = strpos($action, '.com') !== false ? substr($action, 0, (strpos($action, '.com/') + 5)) : substr($action, 0, strrpos($action, '/')).'/';

        // First, run any special replacements
        if ($this->special_replace && ! empty($this->special_replace)) {
            foreach ($this->special_replace as $preg => $replacement) {
                $request = preg_replace($preg, $replacement, $request);
            }
        }
        $request = preg_replace('/@action_url/', $action_url, $request);
        // $request = preg_replace('/xmlns:ns1=".*"/', '', $request);
        $request = preg_replace('/<ns1:(\w+)/', '<$1 xmlns="'.$action_url.'"', $request, 1);
        $request = preg_replace('/xmlns:ns1/', 'xmlns', $request);
        $request = preg_replace('/<ns1:(\w+)/', '<$1', $request);
        $request = str_replace(['/ns1:', 'xmlns:'.$this->namespace.'="'.$action_url.'"'], ['/', ''], $request);
        $request = str_replace('SOAP-ENV:', 'soap:', $request);
        $request = str_replace(':SOAP-ENV', ':soap', $request);

        $call = parent::__doRequest($request, $location, $action, $version);
        $this->__last_request = $request;

        return $call;
    }
}
class CustomSoapClientReplace extends \SoapClient
{
    public function __construct($url, $options, $special_replace = false)
    {
        $this->special_replace = $special_replace;
        parent::__construct($url, $options);
    }

    public function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        $action_url = strpos($action, '.com') !== false ? substr($action, 0, (strpos($action, '.com/') + 5)) : substr($action, 0, strrpos($action, '/')).'/';

        // First, run any special replacements
        if ($this->special_replace && ! empty($this->special_replace)) {
            foreach ($this->special_replace as $preg => $replacement) {
                $request = preg_replace($preg, $replacement, $request);
            }
        }
        $request = preg_replace('/@action_url/', $action_url, $request);

        $call = parent::__doRequest($request, $location, $action, $version);
        $this->__last_request = $request;

        return $call;
    }
}

class CustomSoapClientOptions extends \SoapClient
{
    public function __construct($url, $options, $namespace = '')
    {
        $this->namespace = $namespace;
        $this->url = $url;
        parent::__construct($url, $options);
    }

    public function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        $action_url = strpos($action, '.com') !== false ? substr($action, 0, (strpos($action, '.com/') + 5)) : substr($action, 0, strrpos($action, ':'));
        $request = preg_replace('/<ns1:(\w+)/', '<$1 xmlns="'.$action_url.'"', $request, 1);
        $request = preg_replace('/xmlns:ns1/', 'xmlns', $request);
        $request = preg_replace('/<ns1:(\w+)/', '<$1', $request);
        $request = str_replace(['/ns1:', 'xmlns:'.$this->namespace.'="'.$action_url.'"'], ['/', ''], $request);
        $request = str_replace('SOAP-ENV', 'soap', $request);
        $request = str_replace('<SOAP-ENC:Struct>', '', $request);
        $request = str_replace('</SOAP-ENC:Struct>', '', $request);
        $call = parent::__doRequest($request, $location, $action, $version);
        $this->__last_request = $request;

        return $call;
    }
}
