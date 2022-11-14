<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Number;
use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ClientsImport;
use App\Libraries\HelperLibrary;
use App\Libraries\Twilio;

use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('clients.index');
    }


    public function clientsList(Request $request)
    {
        $clients = Client::orderBy('id','DESC')->get();
        $data = array();
        foreach($clients as $client){
            array_push($data, array('id'=> $client->id, 'client_name'=> $client->client_name,'org_id' => $client->org_id, 'org_type' => $client->org_type, 'allow_security_flag' => $client->allow_security_flag, 'numbers' => $client->numbers->count(),'status' => $client->status));
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
        return view('clients.create');
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
            'client_name' => 'required',
            'org_id' => 'required',
            'org_type' => 'required',
            'oauth_refresh_token' => 'required',
            'client_id' => 'required',
            'client_secret' => 'required',
            'name_space_sf' => 'required'
        ]);

        $input = $request->all();
        $test = new HelperLibrary;
        $test1 = $test->sendGetRequest($request->client_name);

        
        $client = Client::create($input);
        return Redirect::to('clients')->with('success','Client created successfully');
    }

    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function clientNumbers($id)
    {
        $client = Client::find($id);
        return view('clients.numbers',compact('client'));
    }

    public function clientNumbersList($id)
    {
        $client = Client::with('numbers')->find($id);
        $data = array();
        foreach($client['numbers'] as $singlenumber){
            array_push($data, array('id'=> $singlenumber->id, 'number'=> $singlenumber->number,'number_sid' => $singlenumber->number_sid, 'number_token' => $singlenumber->number_token));
        }
        $finaldata['data'] = $data;
        
        echo json_encode($finaldata);
        die();

    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function numberCreate($id)
    {
        $client = Client::find($id);
        return view('clients.numbercreate',compact('client'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function numberStore(Request $request)
    {
        $clientid = $request->clientid;
        
        $validator = Validator::make($request->all(), [
            'authfields.*.number' => 'required'
        ]);
        if ($validator->fails()) {
            return Redirect::to('/clients/'.$clientid.'/numbers/create')->with('error','Number field is required');
        }else{
            $clientid = $request->clientid;
            $numbers = $request->authfields;

            foreach($numbers as $data){
                $singlenumber = new Number;
                $singlenumber->client_id = $clientid;
                $singlenumber->number = $data['number'];
                $singlenumber->number_sid = $data['number_sid'];
                $singlenumber->number_token = $data['number_token'];
                $singlenumber->save();
            }
            return Redirect::to('/clients/'.$clientid.'/numbers')->with('success','Client numbers added successfully');
        }
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
        $client = Client::find($id);
        return view('clients.edit',compact('client'));
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
        $this->validate($request, [
            'client_name' => 'required',
            'org_id' => 'required',
            'org_type' => 'required',
            'oauth_refresh_token' => 'required',
            'client_id' => 'required',
            'client_secret' => 'required',
            'name_space_sf' => 'required'
        ]);
        $client = Client::find($id);
        $client->save();

        return Redirect::to('clients')->with('success','Client updated successfully');
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function fileImportExport()
    {
       return view('clients.fileimport');
    }
   
    /**
    * @return \Illuminate\Support\Collection
    */
    public function fileImport(Request $request) 
    {
        Excel::import(new ClientsImport, $request->file('file')->store('temp'));
        return back();
    }

    /**
     * Delete numbers in Bulk
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteAll(Request $request){
        $numbers = $request->numbers;
        $numbers = array('1');
        foreach($numbers as $number){
            $num = Client::find($number);
            if(count($num['numbers']) > 0){
                $num->numbers()->delete();
            }
            $num->delete();
        }
    }
}
