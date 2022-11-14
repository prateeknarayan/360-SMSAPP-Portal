@extends('layouts/contentLayoutMaster')

@section('title', 'Add Number')
@section('vendor-style')
  <!-- vendor css files -->
  <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
@endsection
@section('content')
<section class="form-control-repeater">
  <div class="row">
    <!-- Invoice repeater -->
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Add Number for Client: {{$client->client_name}}</h4>
        </div>
        <div class="card-body">
          @if(session('error'))
            <div class="alert alert-danger">
                <ul>
                  <li>{{session('error')}}</li>
                </ul>
            </div>
          @endif
          <form action="{{url('clients/numbers/store')}}" method="POST" class=" invoice-repeater">
            <input type="hidden" name="clientid" value="{{$client->id}}">
            @csrf
              <div data-repeater-list="authfields">
                <div data-repeater-item>
                  <div class="row d-flex align-items-end">
                    <div class="col-md-6 col-12">
                      <div class="mb-1">
                        <label class="form-label" for="number">Number</label>
                        <input
                          type="text"
                          class="form-control"
                          name="number"
                          aria-describedby="number"
                          placeholder=""
                          
                        />
                      </div>
                  </div>
                  <div class="col-md-6 col-12">
                      <div class="mb-1">
                          <label class="form-label" for="number_sid">Number SId</label>
                          <input
                          type="text"
                          class="form-control"
                          name="number_sid"
                          aria-describedby="number_sid"
                          placeholder=""
                        />
                      </div>
                  </div>
                  <div class="col-md-12 col-12">
                      <div class="mb-1">
                          <label class="form-label" for="number_token">Number Token</label>
                          <input
                          type="text"
                          class="form-control"
                          name="number_token"
                          aria-describedby="number_token"
                          placeholder="Enter options in case of Selectbox with comma separated"
                        />
                      </div>
                  </div>
                </div>
                <hr />
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <button type="button" class="btn btn-icon btn-primary" data-repeater-create>
                  <i data-feather="plus" class="me-25"></i>
                  <span>Add New</span>
                </button>
              </div>
            </div>
            <div class="row mt-5">
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
  <script src="{{ asset(mix('vendors/js/forms/repeater/jquery.repeater.min.js')) }}"></script>
@endsection
@section('page-script')
  <!-- Page js files -->
  <script src="{{ asset(mix('js/scripts/forms/form-repeater.js')) }}"></script>
  <script src="{{ asset(mix('js/scripts/forms/form-validation.js')) }}"></script>
@endsection
