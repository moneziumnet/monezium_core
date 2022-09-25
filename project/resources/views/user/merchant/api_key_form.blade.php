@extends('layouts.user')

@push('css')
    
@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('API Access Key')}}
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
                            <p>{{ __('API Access Key') }}</p>
                            <div class="input-group input--group">
                              <input type="text" name="key" value="{{@$cred->access_key}}" class="form-control" id="cronjobURL" readonly>
                              <button class="btn btn-sm copytext input-group-text" id="copyBoard" onclick="myFunction()"> <i class="fa fa-copy"></i> </button>
                            </div>
                          </div>
                    </div>
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