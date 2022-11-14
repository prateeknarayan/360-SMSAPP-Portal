<?php

namespace App\Http\Controllers\superuser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuthenticationType;
use Illuminate\Support\Facades\Redirect;


class AuthenticationTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('superuser.authtypes.index');
    }

    public function authList(Request $request)
    {
        $authtypes = AuthenticationType::orderBy('id','DESC')->get();
        $data = array();
        foreach($authtypes as $authtype){

            array_push($data, array('id'=> $authtype->id, 'name'=> $authtype->name,'status' => $authtype->status));
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
        return view('superuser.authtypes.create');
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
            'authfields' => 'required',
        ]);

       $configs = json_encode($request->authfields);
    
        $authtype = new AuthenticationType;
        $authtype->name = $request->name;
        $authtype->auth_fields = $configs;
        $authtype->save();
        return Redirect::to('su/authentication-types')->with('success','Auth Type created successfully');

        // return redirect()->back()->with('success','Auth Type created successfully');
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
        $authtype = AuthenticationType::find($id);
        return view('superuser.authtypes.edit',compact('authtype'));
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

    public function getList(Request $request, $id)
    {
        $authtype = AuthenticationType::find($id);
        echo $authtype->auth_fields;
        die();
    }

    /**
     * Delete numbers in Bulk
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteAll(Request $request){
        $numbers = $request->numbers;
        foreach($numbers as $number){
            $num = AuthenticationType::find($number);
            $num->delete();
        }
    }
}
