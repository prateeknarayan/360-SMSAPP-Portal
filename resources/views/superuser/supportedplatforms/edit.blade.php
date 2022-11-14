@extends('layouts/contentLayoutMaster')

@section('title', 'Add Supported Platform')
@section('vendor-style')
  <!-- vendor css files -->
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
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
          <form action="{{url('su/supported-platforms')}}" method="POST" class="invoice-repeater">
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
                  <label class="form-label" for="request_type_id">Request Type</label>
                  <select class="select2 form-select" id="request_type_id" name="request_type_id" required>
                      @foreach($requesttypes as $type)
                        <option value="{{$type->id}}">{{$type->name}}</option>
                      @endforeach()
                  </select>
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
  <script type="text/javascript">
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
      var channelfields = '<div class="col-md-12 col-12" id="editor-'+data.id+'">\
                <div class="mb-1">\
                  <label class="form-label">'+data.text+'</label>\
                  <textarea rows="7" class="form-control" id="channel-'+data.id+'" name="channels['+data.id+']" required/></textarea>\
                </div>\
              </div>';
      $('#channelseditor').append(channelfields);

    });

    $('#channel').on('select2:unselect', function (e) {
      var data = e.params.data;
      $('#editor-'+data.id+'').remove();

    });
  </script>
@endsection
