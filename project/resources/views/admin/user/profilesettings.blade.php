@extends('layouts.admin')
@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ $data->company_name ?? $data->name }}</h5>
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
              {{__('Change Password')}}
              </h2>
          </div>

          <div class="card-body">
            <form class="geniusform" id="request-form" action="{{ route('admin-user-changepassword',$data->id) }}" method="POST" enctype="multipart/form-data">
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
            <form class="geniusform" action="{{ route('admin-user-update',$data->id) }}" method="POST" enctype="multipart/form-data">
              @include('includes.admin.form-both')
              {{ csrf_field() }}
              @php
              $userType = explode(',', $data->user_type);
              @endphp
              <div class="row">
                  <div class="form-group col-md-6">
                    <label for="kyc_method">{{ __('KYC Method') }}</label>
                    <select class="form-control" name="kyc_method" id="kyc_method" >
                        <option value="manual" @if('manual' == $data->kyc_method) selected @endif>Manual</option>
                        <option value="auto" @if('auto' == $data->kyc_method) selected @endif>Auto</option>
                    </select>
                  </div>
                  <div class="form-group col-md-6" id="manual_type" style="display: none">
                    <label for="manual_kyc">{{ __('Select Kyc Forms') }}</label>
                    <select class="form-control" name="manual_kyc" >
                        @foreach ($kycforms as $value )
                            <option value="{{$value->id}}" @if($data->manual_kyc == $value->id) selected @endif>{{__($value->name)}}</option>
                        @endforeach
                    </select>
                  </div>
              </div>

              <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>
            </form>
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
    $(document).ready(function() {
        if($('#kyc_method').val() == 'manual') {
            document.getElementById('manual_type').style.display = "block";
        }
        else {
            document.getElementById('manual_type').style.display = "none";
        }
    })
  $('#kyc_method').on('change', function(){
    if($('#kyc_method').val() == 'manual') {
        document.getElementById('manual_type').style.display = "block";
    }
    else {
        document.getElementById('manual_type').style.display = "none";
    }
  })
</script>
@endsection


