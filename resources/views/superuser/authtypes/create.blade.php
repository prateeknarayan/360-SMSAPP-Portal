@extends('layouts/contentLayoutMaster')

@section('title', 'Add Authentication Type')

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
          <form action="{{url('su/authentication-types')}}" method="POST" class="invoice-repeater">
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

              	<h4 class="card-title">Add Fields</h4>
            	<div data-repeater-list="authfields">
              	<div data-repeater-item>
	                <div class="row d-flex align-items-end">
	                  <div class="col-md-4 col-12">
	                    <div class="mb-1">
	                      <label class="form-label" for="name">Field Name</label>
	                      <input
	                        type="text"
	                        class="form-control"
	                        name="name"
	                        aria-describedby="name"
	                        placeholder=""
	                        required
	                      />
	                    </div>
	                </div>
	                <div class="col-md-4 col-12">
	                    <div class="mb-1">
	                      	<label class="form-label" for="type">Field Type</label>
	                      	<select class="form-control" name="type">
	                      		<option value="textbox">Textbox</option>
	                      		<option value="selectbox">Select</option>
	                  		</select>
	                    </div>
	                </div>
	                <div class="col-md-4 col-12">
	                    <div class="mb-1">
	                      	<label class="form-label" for="additional">Additional Values</label>
	                      	<input
	                        type="text"
	                        class="form-control"
	                        name="additional"
	                        aria-describedby="additional"
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
                <button class="btn btn-icon btn-primary" data-repeater-create>
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
@endsection
