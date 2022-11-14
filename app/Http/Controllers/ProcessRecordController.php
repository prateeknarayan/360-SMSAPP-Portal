<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProcessRecord;
use Illuminate\Support\Facades\Redirect;

class ProcessRecordController extends Controller
{
    public function index()
    {
        return view('records.index');
    }

    public function recordslList(Request $request)
    {
        $records = ProcessRecord::orderBy('id','DESC')->get();
        $data = array();
        foreach($records as $record){
            array_push($data, array('id'=> $record->id, 'platform'=> $record['process']['platform']->name,'status' => $record->status, 'destination_result' => $record->destination_result, 'created_at' => $record->created_at->format('Y-m-d')));
        }
        $finaldata['data'] = $data;

        echo json_encode($finaldata);
        die();
    }

    public function singleRecord(ProcessRecord $record)
    {
        return view('records.show',compact('record'));
    }
}
