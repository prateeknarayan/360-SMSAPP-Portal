<?php

namespace App\Http\Controllers\superuser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportedPlatform;
use App\Models\AuthenticationType;
use App\Models\Channel;
use App\Models\RequestType;
use Rs\Json\Pointer;
use Rs\Json\Pointer\InvalidJsonException;
use Rs\Json\Pointer\NonexistentValueReferencedException;
use Illuminate\Support\Facades\Redirect;


class SupportedPlatformController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('superuser.supportedplatforms.index');
    }

    public function platformsList(Request $request)
    {
        $platforms = SupportedPlatform::orderBy('id','DESC')->get();
        $data = array();
        foreach($platforms as $platform){

            array_push($data, array('id'=> $platform->id, 'name'=> $platform->name,'code' => $platform->code));
        }
        $finaldata['data'] = $data;
        
        echo json_encode($finaldata);
        die();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $authtypes = AuthenticationType::where('status' , '1')->get();
        $channels = Channel::where('status' , '1')->get();
        $requesttypes = RequestType::get();
        $json = '{"foo":1,"bar":{"baz":2},"qux":[3,4,5],"m~n":8,"a/b":0,"e^f":3}';
        $jsonPointer = new Pointer($json);

        return view('superuser.supportedplatforms.create',compact('authtypes','channels','requesttypes','jsonPointer'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
     {
        $this->validate($request, [
            'name' => 'required',
            'code' => 'required',
            // 'request_type_id' => 'required',
        ]);
        $input = $request->all();

        $platform = new SupportedPlatform;
        $platform->name  = $request->name;
        $platform->code = $request->code;
        //$platform->request_type_id = $request->request_type_id;
        $platform->save();

        $channelsarray =  array();
        $channel_configs = $request->channels;
        foreach($request->channel as $singlechannel){
            array_push($channelsarray, array('channel_id'=> $singlechannel, 'configs' => $channel_configs[$singlechannel]));
        }


        //attach auth type

        $authtypearray = array();
          array_push($authtypearray,array('authentication_type_id'=> $request->auth_type, 'configs' => json_encode($request->configs)));

        $platform->channels()->attach($channelsarray);
        $platform->authTypes()->attach($authtypearray);

        return Redirect::to('su/supported-platforms')->with('success','Platform created successfully');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // $platform = SupportedPlatform::find($id);
        // $authtypes = AuthenticationType::where('status' , '1')->get();
        // $channels = Channel::where('status' , '1')->get();
        // $requesttypes = RequestType::get();
        // $json = '{"foo":1,"bar":{"baz":2},"qux":[3,4,5],"m~n":8,"a/b":0,"e^f":3}';
        // $jsonPointer = new Pointer($json);

        // return view('superuser.supportedplatforms.edit',compact('authtypes','channels','requesttypes','platform');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    /**
     * Delete numbers in Bulk
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteAll(Request $request){
        $numbers = $request->numbers;
        foreach($numbers as $number){
            $num = SupportedPlatform::find($number);
            $num->delete();
        }
    }
}
