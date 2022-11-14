@extends('layouts/contentLayoutMaster')

@section('title', 'Import Clients')
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
          <!-- <h4 class="card-title">Import Clients</h4> -->
        </div>
        <div class="card-body">
          @if (count($errors) > 0)
          <div class="alert alert-danger">
              <strong>Whoops!</strong> There were some problems with your input.<br><br>
              <ul>
              @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
              @endforeach
              </ul>
          </div>
      @endif
          <form action="{{ route('file-import') }}" method="POST" enctype="multipart/form-data">
          	@csrf
          	<div class="col-md-12 col-12">
                <div class="mb-1">
                  <label class="form-label" for="file">Import File</label>
                  <input type="file" name="file" class="form-control"  id="customFile" required>
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

@endsection
