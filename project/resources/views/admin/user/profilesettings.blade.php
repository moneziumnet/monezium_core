@extends('layouts.admin')
@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ $data->name }}</h5>
    <ol class="breadcrumb py-0 m-0">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.user.index') }}">{{ __('User List') }}</a></li>
    </ol>
  </div>
</div>


<div class="row mt-3">
  <div class="col-lg-12">
    <div class="card mt-1 tab-card">
      @include('admin.user.profiletab')

      <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show p-3 active" id="settings" role="tabpanel" aria-labelledby="settings-tab">
          <div class="col">
            <h3 class="page-title">
              {{__('Changed Password')}}
              </h2>
          </div>

          <div class="card-body">
            <form id="request-form" action="{{ route('admin-user-changepassword',$data->id) }}" method="POST" enctype="multipart/form-data">
              @include('includes.admin.form-both')
              {{ csrf_field() }}
              <div class="form-group">
                <label for="inp-name">{{ __('New Password') }}</label>
                <input name="newpass" class="form-control" autocomplete="off" placeholder="{{__('New Password')}}" type="password" required>
              </div>
              <div class="form-group">
                <label for="inp-name">{{ __('Re-Type New Password') }}</label>
                <input name="renewpass" class="form-control" autocomplete="off" placeholder="{{__('Re-Type New Password')}}" type="password" required>
              </div>
              <div class="form-group">
                <button type="submit" class="btn btn-primary w-100">{{__('Submit')}}</button>
              </div>
            </form>
          </div>


          <div class="col">
            <h3 class="page-title">
              {{__('Other Settings')}}
              </h2>
          </div>
          <div class="card-body">
            <form class="geniusform" action="{{ route('admin-user-edit',$data->id) }}" method="POST" enctype="multipart/form-data">
              @include('includes.admin.form-both')
              {{ csrf_field() }}
              <div class="form-group">
                <label for="inp-phone">{{ __('Phone Number') }}</label>
                <input type="text" class="form-control" id="inp-phone" name="phone" placeholder="{{ __('Enter Phone') }}" value="{{ $data->phone }}" required>
              </div>
              @php
              $userType = explode(',', $data->user_type);
              @endphp

              <div class="form-group">
                <label for="inp-name">{{ __('Type') }}</label>

                <select class="select mb-3" name="user_type[]" multiple id="user_type">
                  {{-- <option value="">{{ __('Select Customer Type') }}</option> --}}
                  @foreach(DB::table('customer_types')->orderBy('type_name','asc')->get() as $c_type)
                  <option value="{{ $c_type->id }}" @if(in_array($c_type->id, $userType)) selected @endif>{{ $c_type->type_name }}</option>
                  @endforeach
                </select>
              </div>
              <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
</div>
<!--Row-->
@endsection
@section('scripts')
<script src="{{ asset('assets/admin/js/multiselect.js') }}"></script>

<script type="text/javascript">
  $('#user_type').multiselect({
    columns: 1,
    placeholder: 'Select User Type'
  });
</script>
@endsection

@section('styles')
<style type="text/css">
  .ms-options-wrap,
  .ms-options-wrap * {
    box-sizing: border-box;
  }

  .ms-options ul li {
    list-style: none;
    margin-left: -40px;
  }

  .ms-options-wrap>button:focus,
  .ms-options-wrap>button {
    position: relative;
    width: 100%;
    text-align: left;
    border: 1px solid #d1d3e2;
    background-color: #fff;
    padding: 5px 20px 5px 5px;
    margin-top: 1px;
    font-size: 13px;
    color: #6e707e;
    outline: none;
    white-space: nowrap;
  }

  .ms-options-wrap>button:after {
    content: ' ';
    height: 0;
    position: absolute;
    top: 50%;
    right: 5px;
    width: 0;
    border: 6px solid rgba(0, 0, 0, 0);
    border-top-color: #999;
    margin-top: -3px;
  }

  .ms-options-wrap>.ms-options {
    position: absolute;
    left: 0;
    width: 100%;
    margin-top: 1px;
    margin-bottom: 20px;
    background: white;
    z-index: 2000;
    border: 1px solid #d1d3e2;
    text-align: left;
  }

  .ms-options-wrap>.ms-options>.ms-search input {
    width: 100%;
    padding: 4px 5px;
    border: none;
    border-bottom: 1px groove;
    outline: none;
  }

  .ms-options-wrap>.ms-options .ms-selectall {
    display: inline-block;
    font-size: .9em;
    text-transform: lowercase;
    text-decoration: none;
  }

  .ms-options-wrap>.ms-options .ms-selectall:hover {
    text-decoration: underline;
  }

  .ms-options-wrap>.ms-options>.ms-selectall.global {
    margin: 4px 5px;
  }

  .ms-options-wrap>.ms-options>ul>li.optgroup {
    padding: 5px;
  }

  .ms-options-wrap>.ms-options>ul>li.optgroup+li.optgroup {
    border-top: 1px solid #aaa;
  }

  .ms-options-wrap>.ms-options>ul>li.optgroup .label {
    display: block;
    padding: 5px 0 0 0;
    font-weight: bold;
  }

  .ms-options-wrap>.ms-options>ul label {
    position: relative;
    display: inline-block;
    width: 100%;
    padding: 2px 3px;
    margin: 1px 0;
  }

  .ms-options-wrap>.ms-options>ul li.selected label,
  .ms-options-wrap>.ms-options>ul label:hover {
    background-color: #efefef;
  }

  .ms-options-wrap>.ms-options>ul input[type="checkbox"] {
    margin-right: 5px;
    position: absolute;
    left: 4px;
    top: 7px;
  }
</style>
@endsection