
@extends('layouts/contentLayoutMaster')

@section('title', 'Channels')

@section('vendor-style')
  {{-- vendor css files --}}
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap5.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/rowGroup.bootstrap5.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/pickers/flatpickr/flatpickr.min.css')) }}">
@endsection

@section('content')
<!-- Basic table -->
<section id="basic-datatable">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <table class="datatables-channels table">
          <thead>
            <tr>
              <th></th>
              <th></th>
              <th>id</th>
              <th>Name</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
  <!-- Modal to add new record -->
  <div class="modal new-channel-modal modal-slide-in fade" id="modals-slide-in">
    <div class="modal-dialog sidebar-sm">
      <form class="add-new-channel modal-content pt-0" method="POST" action="{{url('su/channels')}}">
        @csrf
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
        <div class="modal-header mb-1">
          <h5 class="modal-title" id="exampleModalLabel">New Channel</h5>
        </div>
        <div class="modal-body flex-grow-1">
          <div class="mb-1">
            <label class="form-label" for="name">Name</label>
            <input
              type="text"
              class="form-control dt-full-name"
              id="name"
              name="name"
              required
            />
          </div>
          <button type="submit" class="btn btn-primary data-submit me-1">Submit</button>
          <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</section>
<!--/ Basic table -->

<!-- Modal -->
<div class="modal modal-slide-in fade" id="empModal">
   <div class="modal-dialog sidebar-sm">
      <!-- Modal content-->
       <form class="edit-channel modal-content pt-0" action="" method="POST" id="editForm">
        @csrf
        @method('PUT')
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
        <div class="modal-header mb-1">
          <h5 class="modal-title" id="exampleModalLabel">Edit Channel</h5>
        </div>
        <div class="modal-body flex-grow-1">
          <div class="mb-1">
            <label class="form-label" for="name">Name</label>
            <input
              type="text"
              class="form-control dt-full-name"
              id="channelname"
              name="name"
              required
            />
          </div>
             <div class="mb-1">Status</label>
              <div class="form-group">
                <div class="form-check form-check-inline">
                  <input
                    class="form-check-input"
                    type="radio"
                    name="status"
                    value="1"
                    id="status-1"
                  />
                  <label class="form-check-label" for="status-1">Active</label>
                </div>
                <div class="form-check form-check-inline">
                  <input
                    class="form-check-input"
                    type="radio"
                    name="status"
                    value="0"
                    id="status-0"
                  />
                  <label class="form-check-label" for="status-0">Inactive</label>
                </div>
              </div>
            </div>
          <button type="submit" class="btn btn-primary data-submit me-1">Update</button>
          <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
   </div>
</div>
@endsection


@section('vendor-script')
  {{-- vendor files --}}
  <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.bootstrap5.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap5.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.checkboxes.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.buttons.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/jszip.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/pdfmake.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/vfs_fonts.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/buttons.html5.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/buttons.print.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.rowGroup.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/pickers/flatpickr/flatpickr.min.js')) }}"></script>
@endsection
@section('page-script')
{{-- Page js files --}}
<script src="{{ asset(mix('js/scripts/tables/table-datatables-basic.js')) }}"></script>
<script type='text/javascript'>
 $(document).ready(function(){
  $('.datatables-channels').on('click','.viewdetails',function(){
    var cid = $(this).attr('data-id');
    var cname = $(this).attr('data-name');
    var cstatus = $(this).attr('data-status');
    if(cid > 0){
       // AJAX request
       var url = "{{ url('su/channels') }}/"+cid+"";
       $('#editForm').attr('action', url);
       $('#channelname').val(cname);
       $("#status-"+cstatus+"").prop("checked", true);
       // Display Modal
       $('#empModal').modal('show'); 
    }
  });
 });
 </script>
@endsection
