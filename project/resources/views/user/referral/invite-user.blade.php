@extends('layouts.user')

@push('css')
    
@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Invite User')}}
          </h2>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                          <p>{{ __('My Referral Link') }}</p>
                          <div class="input-group input--group">
                            <input type="text" name="key" value="{{ url('/').'?reff='.$user->affilate_code}}" class="form-control" id="cronjobURL" readonly>
                            <button class="btn btn-sm copytext input-group-text" id="copyBoard" onclick="myFunction()"> <i class="fa fa-copy"></i> </button>
                          </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="row row-cards mt-2">
            <div class="col-12">
                <div class="card p-3">
                    <h3>{{ __('Send Invitation') }}</h3>
                    @includeIf('includes.flash')
                    <form id="request-form" action="{{ route('user.referral.invite-user') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Enter Email Address')}}</label>
                            <input name="invite_email" id="invite_email" class="form-control" autocomplete="off" placeholder="{{__('example@gmail.com')}}" type="email" value="{{ old('invite_email') }}" required>
                        </div>

                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary submit-btn w-100">{{__('Send Invitation')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
    'use strict';
  
    function myFunction() {
      var copyText = document.getElementById("cronjobURL");
      copyText.select();
      copyText.setSelectionRange(0, 99999);
      document.execCommand("copy");
      toastr.options =
      {
        "closeButton" : true,
        "progressBar" : true
      }
      toastr.success("Copied.");
    }
  </script>
@endpush