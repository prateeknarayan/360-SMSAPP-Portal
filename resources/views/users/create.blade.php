@extends('layouts/contentLayoutMaster')

@section('title', 'Add New User')

@section('vendor-style')
  <!-- vendor css files -->
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/charts/apexcharts.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/extensions/toastr.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
@endsection
@section('page-style')
  <!-- Page css files -->
  <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/charts/chart-apex.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/extensions/ext-component-toastr.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('css/base/pages/app-invoice-list.css')) }}">
  @endsection

@section('content')
<div class="row">
  <div class="col-12">
   
@if ($errors->any())
    <div class="alert alert-danger">
        There were some problems with your input.<br><br>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


<form action="{{ route('users.store') }}" method="POST">
    @csrf
  
     <div class="row">
         <div class="col-md-8">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="sendemail" name="sendemail">
              <label class="form-check-label" for="sendemail">
                Send Welcome Email
              </label>
            </div>
        </div>
        <div class="col-md-8">
            <div class="form-group">
                <strong>Name</strong>
                <input type="text" name="name" class="form-control" placeholder="Name">
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Role:</strong>
                {!! Form::select('roles[]', $roles,[], array('class' => 'form-control','multiple')) !!}
            </div>
        </div>

    <!-- 
        <div class="col-md-8">
            <div class="form-group">
                <strong>User Role</strong>
                <select name="type" class="form-control">
                    <option value="superadmin">Super Admin</option>
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                    <option value="executive">Executive</option>
                </select>
            </div>
        </div> -->
        <div class="col-md-8">
            <div class="form-group">
                <strong>Email</strong>
                 <input type="email" name="email" class="form-control" placeholder="Email">
            </div>
        </div>
        <div class="col-md-8">
            <div class="form-group">
                <strong>Password</strong>
                 <input type="password" name="password" class="form-control" placeholder="Password">
            </div>
        </div>
        <div class="col-md-8">
            <div class="form-group">
                <strong>Confirm Password</strong>
                 <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password">
            </div>
        </div>
        <div class="col-md-8 text-center margin-tb">
            <div class="form-group mt-3">
                <button type="submit" class="btn btn-primary margin-tb">Submit</button>
            </div>
        </div>
    </div>
   
</form>

</div>
</div>
@endsection

@section('vendor-script')
  <!-- vendor files -->
  <script src="{{ asset(mix('vendors/js/charts/apexcharts.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/extensions/toastr.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/extensions/moment.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.buttons.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.bootstrap5.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap5.js')) }}"></script>
@endsection
@section('page-script')
  <!-- Page js files -->
  <script src="{{ asset(mix('js/scripts/pages/dashboard-analytics.js')) }}"></script>
  <script src="{{ asset(mix('js/scripts/pages/app-invoice-list.js')) }}"></script>
@endsection
