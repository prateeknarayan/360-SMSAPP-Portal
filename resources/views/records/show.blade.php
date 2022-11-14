@extends('layouts/contentLayoutMaster')

@section('title', 'Process Record')
@section('vendor-style')
  <!-- vendor css files -->
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
  <link href="{{ asset('jsoneditor/dist/jsoneditor.min.css')}}" rel="stylesheet" type="text/css">
@endsection
@section('content')
<section class="form-control-repeater">
  <div class="row">
    <!-- Invoice repeater -->
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Record payload</h4>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
                <h5>Process Record</h5>
                <div class="data-received-view">
                  <div class="row">
                    <div class="col-6 col-md-4-6"> Record ID: </div> 
                    <div class="col-6 col-md-6">{{$record->id}}</div>
                  </div>
                  <div class="row">
                    <div class="col-6 col-lg-6"> Record Date: </div> 
                    <div class="col-6 col-lg-6">{{$record->created_at->format('Y-m-d')}}</div>
                  </div>
                  <div class="row">
                    <div class="col-6 col-lg-6"> Status: </div> 
                    <div class="col-6 col-lg-6">{{$record->status}}</div>
                  </div>
                  <div class="row">
                    <div class="col-6 col-lg-6"> Record Destination Result: </div> 
                    <div class="col-6 col-lg-6">{{$record->destination_result}}</div>
                  </div>
                  <div class="row">
                    <div class="col-6 col-lg-6"> Data Received: </div> 
                  </div>
                  <div class="row">
                    <div class="col-12 col-lg-12">
                      <div id="jsoneditor1" style="width: 100%; height: 400px;"></div>
                    </div>
                  </div>
                </div>
            </div>
            <div class="col-md-6">
                <h5>Json Payload</h5>
                <div id="jsoneditor" style="width: 100%; height: 400px;"></div>
            </div>
          </div>
      </div>
    </div>
    <!-- /Invoice repeater -->
  </div>
</section>
@endsection

@section('vendor-script')
  <!-- vendor files -->
  <script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
@endsection
@section('page-script')
  <!-- Page js files -->
  <script src="{{ asset(mix('js/scripts/forms/form-select2.js')) }}"></script>
  <script src="{{ asset('jsoneditor/dist/jsoneditor.min.js')}}"></script>

  <script type="text/javascript">
     // create the editor
    const container = document.getElementById("jsoneditor")
        const options = { mode: 'code',search: 'true',}
        const editor = new JSONEditor(container, options)
        setJSON();
   
    // set json
    function setJSON () {
        var json = <?php echo html_entity_decode($record->json_data); ?>;
        editor.set(json);
    }

    const container1 = document.getElementById("jsoneditor1")
        const options1 = { mode: 'code',search: 'true',}
        const editor1 = new JSONEditor(container1, options1)
        setJSON1();
   

    // set json
    function setJSON1 () {
        var json = <?php echo html_entity_decode($record['datareceived']->data_received); ?>;
        editor1.set(json);
    }


    // // get json
    // function getJSON() {
    //     var json = editor.get();
    //     alert(JSON.stringify(json, null, 2));
    // }

  </script>
@endsection
