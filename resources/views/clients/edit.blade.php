@extends('layouts/contentLayoutMaster')

@section('title', 'Edit Client')
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
      
        <form action="{{ url('clients',$client->id) }}" method="POST">
        @csrf
        @method('PUT')
          	@csrf
          	<div class="col-md-12 col-12">
                <div class="mb-1">
                  <label class="form-label" for="client_name">Client Name</label>
                  <input
                    type="text"
                    class="form-control"
                    id="client_name"
                    name="client_name"
                    aria-describedby="client_name"
                    placeholder="Client Name"
                    value="{{$client->client_name}}"
                    required
                  />
                </div>
              </div>
              <div class="row">
              <div class="col-md-6 col-6">
                <div class="mb-1">
                  <label class="form-label" for="org_id">Org Id</label>
                  <input
                    type="text"
                    class="form-control"
                    id="org_id"
                    name="org_id"
                    aria-describedby="org_id"
                    placeholder="Org Id"
                    value="{{$client->org_id}}"
                    required
                  />
                </div>
              </div>

              <div class="col-md-6 col-6">
                <div class="mb-1">
                  <label class="form-label" for="org_type">Org Type</label>
                  <input
                    type="text"
                    class="form-control"
                    id="org_type"
                    name="org_type"
                    aria-describedby="org_type"
                    placeholder="Org Type"
                    value="{{$client->org_type}}"
                    required
                  />
                </div>
              </div>
            </div>
            <div class="row">

              <div class="col-md-6 col-6">
                <div class="mb-1">
                  <label class="form-label" for="sid">SId</label>
                  <input
                    type="text"
                    class="form-control"
                    id="sid"
                    name="sid"
                    aria-describedby="sid"
                    placeholder="Add SId"
                    value="{{$client->sid}}"
                  />
                </div>
              </div>

              <div class="col-md-6 col-6">
                <div class="mb-1">
                  <label class="form-label" for="token">Token</label>
                  <input
                    type="text"
                    class="form-control"
                    id="token"
                    name="token"
                    aria-describedby="token"
                    placeholder="Add Token"
                    value="{{$client->token}}"
                  />
                </div>
              </div>
            </div>

              <div class="col-md-12 col-12">
                <div class="mb-1">
                  <label class="form-label" for="oauth_refresh_token">Oauth Refresh Token</label>
                  <input
                    type="text"
                    class="form-control"
                    id="oauth_refresh_token"
                    name="oauth_refresh_token"
                    aria-describedby="oauth_refresh_token"
                    placeholder="Add Oauth Refresh Token"
                    value="{{$client->oauth_refresh_token}}"
                  />
                </div>
              </div>
              <div class="row">
              <div class="col-md-6 col-6">
                <label class="form-label">Allow Security Flag</label>
                  <div class="form-group mb-1">
                    <div class="form-check form-check-inline">
                      <input
                        class="form-check-input"
                        type="radio"
                        name="allow_security_flag"
                        id="allow_security_flag1"
                        value="yes"
                        
                      />
                      <label class="form-check-label" for="allow_security_flag1">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input
                        class="form-check-input"
                        type="radio"
                        name="allow_security_flag"
                        id="allow_security_flag2"
                        value="no"
                      />
                      <label class="form-check-label" for="allow_security_flag2">No</label>
                    </div>
                  </div>
              </div>

              <div class="col-md-6 col-6">
                <label class="form-label">Allow AI Flag</label>
                  <div class="form-group mb-1">
                    <div class="form-check form-check-inline">
                      <input
                        class="form-check-input"
                        type="radio"
                        name="allow_AI_flag"
                        id="allow_AI_flag1"
                        value="yes"
                        checked
                      />
                      <label class="form-check-label" for="allow_AI_flag1">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input
                        class="form-check-input"
                        type="radio"
                        name="allow_AI_flag"
                        id="allow_AI_flag2"
                        value="no"
                      />
                      <label class="form-check-label" for="allow_AI_flag2">No</label>
                    </div>
                  </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 col-6">
                <div class="mb-1">
                  <label class="form-label" for="client_id">Client Id</label>
                  <input
                    type="text"
                    class="form-control"
                    id="client_id"
                    name="client_id"
                    aria-describedby="client_id"
                    placeholder="Add Client Id"
                    value="{{$client->client_id}}"
                    required
                  />
                </div>
              </div>

              <div class="col-md-6 col-6">
                <div class="mb-1">
                  <label class="form-label" for="client_secret">Client Secret</label>
                  <input
                    type="text"
                    class="form-control"
                    id="client_secret"
                    name="client_secret"
                    aria-describedby="client_secret"
                    placeholder="Add Client Secret"
                    value="{{$client->client_secret}}"
                    required
                  />
                </div>
              </div>
            </div>

              <div class="col-md-12 col-12">
                <div class="mb-1">
                  <label class="form-label" for="client_secret">Namespace Sf</label>
                  <input
                    type="text"
                    class="form-control"
                    id="name_space_sf"
                    name="name_space_sf"
                    aria-describedby="name_space_sf"
                    value="{{$client->name_space_sf}}"
                    placeholder="Add Namespace"
                    required
                  />
                </div>
              </div>
              <div class="col-md-12 col-12">
                <div class="mb-1">
                  <label class="form-label" for="client_secret">Client Email</label>
                  <textarea
                    type="text"
                    class="form-control"
                    id="client_email"
                    name="client_email"
                    value="{{$client->client_email}}"
                  ></textarea>
                </div>
              </div>
              <div class="row">
              <div class="col-md-4 col-4">
                <label class="form-label" for="is_allow_email">Is allow email?</label>
                  <div class="form-group">
                    <div class="form-check form-check-inline">
                      <input
                        class="form-check-input"
                        type="radio"
                        name="is_allow_email"
                        id="is_allow_email1"
                        value="yes"
                        checked
                      />
                      <label class="form-check-label" for="is_allow_email1">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input
                        class="form-check-input"
                        type="radio"
                        name="is_allow_email"
                        id="is_allow_email2"
                        value="no"
                      />
                      <label class="form-check-label" for="is_allow_email2">No</label>
                    </div>
                  </div>
              </div>

              <div class="col-md-4 col-4">
                  <label class="form-label" for="is_email_503_allow">Is email 503 allow?</label>
                  <div class="form-group">
                    <div class="form-check form-check-inline">
                      <input
                        class="form-check-input"
                        type="radio"
                        name="is_email_503_allow"
                        id="is_email_503_allow1"
                        value="yes"
                        checked
                      />
                      <label class="form-check-label" for="is_email_503_allow1">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input
                        class="form-check-input"
                        type="radio"
                        name="is_email_503_allow"
                        id="is_email_503_allow2"
                        value="no"
                      />
                      <label class="form-check-label" for="is_email_503_allow2">No</label>
                    </div>
                  </div>
              </div>
              
              <div class="col-md-4 col-4">
                  <label class="form-label" for="is_allow_short_url">Is allow short url</label>
                  <div class="form-group">
                    <div class="form-check form-check-inline">
                      <input
                        class="form-check-input"
                        type="radio"
                        name="is_allow_short_url"
                        id="is_allow_short_url1"
                        value="yes"
                        checked
                      />
                      <label class="form-check-label" for="is_allow_short_url1">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input
                        class="form-check-input"
                        type="radio"
                        name="is_allow_short_url"
                        id="is_allow_short_url2"
                        value="no"
                      />
                      <label class="form-check-label" for="is_allow_short_url2">No</label>
                    </div>
                  </div>
              </div>
            </div>

              <div class="col-md-12 col-12">
                <div class="mb-1">
                  <label class="form-label" for="client_secret">Short url access token</label><br/>
                  <input
                    type="text"
                    class="form-control"
                    id="short_url_access_token"
                    name="short_url_access_token"
                    aria-describedby="short_url_access_token"
                    placeholder="Add Short url access token"
                    value="{{$client->short_url_access_token}}"
                    required
                  />
                </div>
              </div>
            <div class="row">
              <div class="col-md-6 col-6">
                <div class="mb-1">
                  <label class="form-label" for="short_url_created_at">Short Url Created Date</label>
                  <input
                    type="text"
                    class="form-control"
                    id="short_url_created_at"
                    name="short_url_created_at"
                    aria-describedby="name_space_sf"
                    placeholder="Add Short Url Created Date"
                    value="{{$client->short_url_created_at}}"
                    required
                  />
                </div>
              </div>

              <div class="col-md-6 col-6">
                <div class="mb-1">
                  <label class="form-label" for="short_url_updated_at">Short Url Updated Date</label>
                  <input
                    type="text"
                    class="form-control"
                    id="short_url_updated_at"
                    name="short_url_updated_at"
                    aria-describedby="name_space_sf"
                    placeholder="Add Short Url Updated Date"
                    value="{{$client->short_url_updated_at}}"
                    required
                  />
                </div>
              </div>
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
