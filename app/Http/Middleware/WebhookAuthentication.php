<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Models\SupportedPlatform;
use Route;
class WebhookAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
    */
    public function handle(Request $request, Closure $next)
    {
        
        $platform = $request->route("platform");
        if (empty($platform)) {
            throw new HttpException(400, 'platform not found');
        }else{

            $check_platform = SupportedPlatform::with('authTypes')->with('channels')->where('code',$platform)->first();
            if($check_platform){
                $platform =  $check_platform;
                if(count($platform['authTypes']) > 0){
                    $auth_fields = json_decode($platform['authTypes'][0]->auth_fields);
                    $auth_values = json_decode($platform['authTypes'][0]->pivot->configs);

                    $variables = array();

                    $authtype = $platform['authTypes'][0]->code;

                    switch ($authtype) {
                    case 'basic_auth':
                        if ((!isset($auth_values->username) || empty($auth_values->username))|| (!isset($auth_values->password) || empty($auth_values->password)))
                            
                            throw new \HttpException(402, "Username and/or Password for Shiphero Token not set");

                            $req_username = $request->header("PHP_AUTH_USER");
                            $req_password = $request->header("PHP_AUTH_PW");

                            if(($req_username != $auth_values->username) && ($req_password != $auth_values->password)){
                                throw new \HttpException(401, "unauthorized");
                            }
                            
                        break;
                    case 'bearer_token':
                        $header = $request->header("Authorization");
                        if (empty($header)) {
                            throw new HttpException(401, "unauthorized");
                        }
                        $header = str_replace("Bearer ", "", $header);
                        if ($header != @$auth_values->token) {
                            throw new HttpException(401,"unauthorized");
                        }
                        break;
                    case 'api_key':
                        echo "i equals 2";
                        break;
                    }
                }
            }else{
                throw new HttpException(400, 'platform not supported');
            }
        }
         return $next($request);
    }
}
