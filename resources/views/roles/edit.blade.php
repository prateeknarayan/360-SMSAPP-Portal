@extends('layouts/contentLayoutMaster')

@section('title', 'Roles')

@section('vendor-style')
  <!-- Vendor css files -->
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap5.min.css')) }}">
@endsection
@section('page-style')
  <!-- Page css files -->
  <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
@endsection

@section('content')
<!-- Role cards -->
<div class="row">
  <div class="col-md-12">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
       <div class="text-center mb-4">
          <h1 class="role-title">Edit Role</h1>
          <p>Edit role permissions</p>
        </div>
        <!-- Add role form -->
        {!! Form::model($role, ['method' => 'PATCH','route' => ['roles.update', $role->id]]) !!}
          <div class="col-12">
            <label class="form-label" for="modalRoleName">Role Name</label>
            {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
          </div>
          <div class="col-12">
            <h4 class="mt-2 pt-50">Role Permissions</h4>
            <!-- Permission table -->
            <div class="table-responsive">
              <table class="table table-flush-spacing">
                <tbody>
                  <tr>
                    <td class="text-nowrap fw-bolder">
                      Administrator Access
                      <span data-bs-toggle="tooltip" data-bs-placement="top" title="Allows a full access to the system">
                        <i data-feather="info"></i>
                      </span>
                    </td>
                    <td>
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAll" />
                        <label class="form-check-label" for="selectAll"> Select All </label>
                      </div>
                    </td>
                  </tr>
    
                  
                  <tr>
                    <td class="text-nowrap fw-bolder">User/Roles Management</td>

                    <td>
                      <div class="d-flex">
                        @foreach($permission as $value)
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" id="userManagementCreate" />
                          {{ Form::checkbox('permission[]', $value->id, in_array($value->id, $rolePermissions) ? true : false, array('class' => 'form-check-input')) }}
                          <label class="form-check-label"> {{ $value->name }} </label>
                        </div>
                        @endforeach
                      </div>
                    </td>
                  </tr>
                
                </tbody>
              </table>
            </div>
            <!-- Permission table -->
          </div>
          <div class="col-12 text-center mt-2">
            <button type="submit" class="btn btn-primary me-1">Submit</button>
            <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal" aria-label="Close">
              Discard
            </button>
          </div>
        </form>
        <!--/ Add role form -->
    </div>
</div>
</div>
  </div>
</div>
<!--/ Role cards -->

@include('content/_partials/_modals/modal-add-role')
@endsection

@section('vendor-script')
  <!-- Vendor js files -->
  <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.bootstrap5.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap5.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.buttons.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/buttons.bootstrap5.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.checkboxes.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/forms/validation/jquery.validate.min.js')) }}"></script>
@endsection
@section('page-script')
  <!-- Page js files -->
  <script src="{{ asset(mix('js/scripts/pages/modal-add-role.js')) }}"></script>
  <script src="{{ asset(mix('js/scripts/pages/app-access-roles.js')) }}"></script>
@endsection
