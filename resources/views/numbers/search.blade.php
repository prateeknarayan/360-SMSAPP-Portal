@extends('layouts/contentLayoutMaster')

@section('title', 'Search Numbers')
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
             
          @if ($errors->any())
              <div class="alert alert-danger">
                  <ul>
                      @foreach ($errors->all() as $error)
                          <li>{{ $error }}</li>
                      @endforeach
                  </ul>
              </div>
          @endif
          <form action="{{ url('search') }}">
            <div class="row">
          	<div class="col-md-10 col-12 mb-1">
              <fieldset>
                <div class="input-group">
                  <button
                    type="button"
                    class="btn btn-outline-primary dropdown-toggle"
                    data-bs-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                    >
                    <span id="searchby">Search by</span>
                  </button>
                  <div class="dropdown-menu">
                    <a class="dropdown-item searchby" href="#" data-searchby="number">Number</a>
                    <a class="dropdown-item searchby" href="#" data-searchby="org_id">OGI ID</a>
                    <a class="dropdown-item searchby" href="#" data-searchby="keyword">Keyword</a>
                  </div>
                  <input type="text" class="form-control" placeholder="Enter search" name="search" />
                  <input type="hidden" id="search_by" value="" name="searchby" />

                </div>
              </fieldset>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary me-1 waves-effect waves-float waves-light">Search</button>
            </div>
            </div>
          </form>
        </div>
      </div>

      <!--- Search Results -->
      @if(isset($results))
      <div class="row">
        <h4 class="card-title">Search Results By : {{$searchby}} for {{$search}}</h4> 
        @foreach($results as $result)
          <div class="row border-bottom mb-4">
     
                 <div class="row match-height">
          <div class="col-lg-6 col-md-6 col-6">
            <div class="card card-developer-meetup shadow-sm rounded">
              <div class="card-body">
                <div class="card-header">
                  <h4>Token Details</h4>
                </div>
                <div class="card-body">
                  <div class="content-body mb-2">
                    <h6 class="mb-0">Oauth Refresh Token</h6>
                    <small>{{$result->oauth_refresh_token}}</small>
                  </div>
                  <div class="content-body mb-2">
                    <h6 class="mb-0">Org Id</h6>
                    <small>{{$result->org_id}}</small>
                  </div>
                  <div class="content-body mb-2">
                    <h6 class="mb-0">Org Type</h6>
                    <small>{{$result->org_type}}</small>
                  </div>
                  <div class="content-body mb-2">
                    <h6 class="mb-0">Allow AI Feature : <small>{{$result->allow_AI_flag}}</small>
                    </h6>
                  </div>
                  <div class="content-body mb-2">
                  <h6 class="mb-0">Number & Keyword Details : <a href="{{url('clients',$result->id)}}/numbers">Numbers List ({{count($result['numbers'])}})</a></h6>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6 col-md-6 col-6">
              <div class="row equal">
                <div class="col-md-12">
                    <div class="card card-developer-meetup">
                    <div class="card-body">
                      <div class="card-header">
                        <h4>Email Details</h4>
                      </div>
                      <div class="card-body">
                        <div class="content-body mb-1">
                          <h6 class="mb-0">Client Email</h6>
                          <small>{{$result->client_email}}</small>
                        </div>
                        <div class="content-body mb-1">
                          <h6 class="mb-0">Allow Email Notifications : <small>{{$result->is_allow_email}}</small></h6>
                          
                        </div>
                        <div class="content-body mb-1">
                          <h6 class="mb-0">Allow SF Down Email Notifications : <small>{{$result->is_email_503_allow}}</small></h6>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="col-md-12">
                    <div class="card card-developer-meetup">
                    <div class="card-body">
                      <div class="card-header">
                        <h4>Link Shortening Details</h4>
                      </div>
                      <div class="card-body">
                        <div class="content-body mb-1">
                          <h6 class="mb-0">Allow Link Shortning Feature : <small>{{$result->is_allow_short_url}}</small></h6>
                          
                        </div>
                        <div class="content-body mb-1">
                          <h6 class="mb-0">Link Shortning Access Token</h6>
                          <small>{{$result->short_url_access_token}}</small>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                </div>
              </div>
          </div>
          </div>
  
      </div>
        @endforeach
        <div class="row mb-4">
         {{ $results->appends(request()->input())->links()}}
       </div>
      </div>
      @endif
      <!-- Search Results -->
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
    // $( ".searchby" ).click(function() {
    $(".searchby").on("click", function(){
      var value = $(this).text();
      $('#searchby').html(value);
      var searchby = $(this).attr('data-searchby');
      $('#search_by').val(searchby);
    });
  </script>
@endsection
