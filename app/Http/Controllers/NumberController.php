<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Number;
use Illuminate\Support\Facades\Redirect;

class NumberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $clients = Client::get();
        return view('numbers.index',compact('clients'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function numbersList()
    {
        $numbers = Number::get();
        $data = array();
        foreach($numbers as $singlenumber){
            array_push($data, array('id'=> $singlenumber->id,'clientid' => $singlenumber->client_id, 'clientname' => $singlenumber['client']->client_name, 'number'=> $singlenumber->number,'number_sid' => $singlenumber->number_sid, 'number_token' => $singlenumber->number_token));
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
        //
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
            'client_id' => 'required',
            'number' => 'required',
        ]);

        $singlenumber = new Number;
        $singlenumber->client_id = $request->client_id;
        $singlenumber->number = $request->number;
        $singlenumber->number_sid = $request->number_sid;
        $singlenumber->number_token = $request->number_token;
        $singlenumber->save();

        return Redirect::to('/numbers')->with('success','Numbers added successfully');
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
        //
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function search(Request $request)
    {
        if(isset($request->searchby) && isset($request->search)){
            $searchby = $request->searchby;
            $search = $request->search;
            switch ($searchby) {
              case "number":
                $results = Client::with('numbers')->whereHas('numbers', function($q) use($search){
                    $q->where('number', $search);
                })->paginate(1);

                break;
              case "org_id":
                $results = Client::with('numbers')->where('org_id',$search)->paginate(1);
                break;
              case "keyword":
                $results = array();
                break;
            }
            return view('numbers.search',compact('results','searchby', 'search'));
        }else{
            return view('numbers.search');
        }
        
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function searchResults(Request $request)
    {
        $this->validate($request, [
            'search' => 'required',
            'searchby' => 'required',
        ]);

        $searchby = $request->searchby;
        $search = $request->search;
        switch ($searchby) {
          case "number":
            $results = Client::with('numbers')->whereHas('numbers', function($q) use($search){
                $q->where('number', $search);
            })->paginate(1);

            break;
          case "org_id":
            $results = Client::with('numbers')->where('org_id',$search)->get();
            break;
          case "keyword":
            $results = array();
            break;
        }

        return view('numbers.search',compact('results','searchby', 'search'));
    }



    /**
     * Delete numbers in Bulk
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteAll(Request $request){
        $numbers = $request->numbers;
        foreach($numbers as $number){
            $num = Number::find($number);
            $num->delete();
        }

    }
}
