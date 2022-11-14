<?php

namespace App\Http\Controllers\superuser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Channel;
class ChannelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('superuser.channels.index');
    }

    public function channelList(Request $request)
    {
        $channels = Channel::orderBy('id','DESC')->get();
        $data = array();
        foreach($channels as $channel){

            array_push($data, array('id'=> $channel->id, 'name'=> $channel->name,'status' => $channel->status));
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
            'name' => 'required'
        ]);
        $input = $request->all();

        $user = Channel::create($input);

        return redirect()->back()->with('success','Channel created successfully');
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
        $this->validate($request, [
            'name' => 'required'
        ]);
        $input = $request->all();

        $channel = Channel::find($id);
        $channel->name = $request->name;
        $channel->status = $request->status;
        $channel->save();


        return redirect()->back()->with('success','Channel created successfully');
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
            $num = Channel::find($number);
            $num->delete();
        }
    }
}
