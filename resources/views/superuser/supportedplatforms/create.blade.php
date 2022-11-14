@extends('layouts/contentLayoutMaster')

@section('title', 'Add Supported Platform')
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
          <h4 class="card-title">Add New</h4>
        </div>
        <div class="card-body">
          <form action="{{url('su/supported-platforms')}}" method="POST" class="invoice-repeater" id="addplatforms">
          	@csrf
          	<div class="col-md-12 col-12">
                <div class="mb-1">
                  <label class="form-label" for="name">Name</label>
                  <input
                    type="text"
                    class="form-control"
                    id="name"
                    name="name"
                    aria-describedby="name"
                    placeholder="Add Name"
                    required
                  />
                </div>
              </div>

              <div class="col-md-12 col-12">
                <div class="mb-1">
                  <label class="form-label" for="code">Code</label>
                  <input
                    type="text"
                    class="form-control"
                    id="code"
                    name="code"
                    aria-describedby="code"
                    placeholder="Add Code"
                    required
                  />
                </div>
              </div>

              
              <div class="col-md-12 col-12">
                <div class="mb-1">
                  <label class="form-label" for="auth_type">Authentication Type</label>
                  <select class="select2 form-select" id="auth_type" name="auth_type" required>
                    <option value="">Select</option>
                      @foreach($authtypes as $atype)
                        <option value="{{$atype->id}}">{{$atype->name}}</option>
                      @endforeach()
                  </select>
                </div>
              </div>
              <div id="authfields">


              </div>


              <div class="col-md-12 col-12">
                <div class="mb-1">
                  <label class="form-label" for="channel">Channel</label>
                  <select class="select2 form-select" id="channel" multiple  name="channel[]" required>
                      @foreach($channels as $channel)
                        <option value="{{$channel->id}}">{{$channel->name}}</option>
                      @endforeach()
                  </select>
                </div>
              </div>  
              <div id="channelseditor">

              </div>
            <div class="row">
              <div class="col-12">
                <button type="submit" class="btn btn-success">
                  <span>Submit</span>
                </button>
              </div>
            </div>
          </form>
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
    var keys = [];
    var vars = {};
    function initializeeditor(editorid){
      const container = document.getElementById(editorid)
      const options = { mode: 'code',search: 'true',}
       vars[editorid+'-field'] = new JSONEditor(container, options);
      keys.push(editorid);
    }
    
    console.log(keys);

    $( "#addplatforms" ).submit(function( event ) {
      event.preventDefault();
      $.each(vars, function(key, value) {
        console.log(key);
          var json = value.get();
          $('#'+key).val(JSON.stringify(json, null, 2));
      });

      this.submit();

    });


    // set json
    // function setJSON () {
    //     var json = {
    //         "Array": [1, 2, 3],
    //         "Boolean": true,
    //         "Null": null,
    //         "Number": 123,
    //         "Object": {"a": "b", "c": "d"},
    //         "String": "Hello World"
    //     };
    //     editor.set(json);
    // }

    // // get json
    // function getJSON() {
    //     var json = editor.get();
    //     alert(JSON.stringify(json, null, 2));
    // }


    $("#auth_type").change(function()
    {
      var id=$(this).val();
      if(id != ''){

      $.ajax
      ({
      type: "GET",
      url: "{{url('/su/getdata/authentication')}}/"+id+"",
      cache: false,
      success: function(data)
      {
        var authfields = '<h4 class="card-title">Auth Fields</h4>';
        $.each(JSON.parse(data), function(index, itemData) {
          authfields += '<div class="mb-1">\
                 <label class="form-label" for="'+itemData.name+'">'+itemData.name+'</label>';
                  if(itemData.type == 'textbox'){
                      authfields += '<input type="text" class="form-control" name="configs['+itemData.name+']" required/>'
                  }else{
                    var options = itemData.additional.split(',');
                    console.log(options);
                    authfields += '<select class="form-control" name="configs['+itemData.name+']" required>';
                    $.each(options, function(optionindex, optionData) {
                        authfields += '<option value="'+optionData+'">'+optionData+'</option>';
                    });
                    authfields += '</select>'
                  }

                authfields += '</div>';
        });
        $('#authfields').html(authfields);
      } 
      });
      }else{
        $('#authfields').html('');
      }

    });

    $('#channel').on('select2:select', function (e) {
      var data = e.params.data;
      var channelfields = '<div class="singlechannel"><div class="col-md-12 col-12">\
                <div class="mb-1">\
                <h3>Mapping JSON for : '+data.text+'</h3>\
                  </div>\
              </div>';
              
      channelfields += '<div class="col-md-12 col-12" id="editor-'+data.id+'">\
                <div class="mb-1">\
                  <label class="form-label">JSON</label>\
                  <div class="jsoneditor" style="width: 100%; height: 400px;" id="channel-'+data.id+'"></div>\
                  <input type="hidden" id="channel-'+data.id+'-field" name="channels['+data.id+']" required/>\
                </div>\
              </div></div>';

      $('#channelseditor').append(channelfields);
      initializeeditor("channel-"+data.id+"");

      //scroll to mapping json
      const destination = $('#editor-'+data.id+'');
        $('html,body').animate({
            scrollTop: destination.offset().top
        },'slow');

    });

    $('#channel').on('select2:unselect', function (e) {
      var data = e.params.data;
      $('#editor-'+data.id+'').remove();

    });
  </script>
@endsection
