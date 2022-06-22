@extends('layouts.front')
@section('content')

    <div class="card">
        <div class="d-sm-flex align-items-center justify-content-between py-3">
        <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Institution Profile') }}</h5>
        </div>
    </div>

        <div class="card mb-4 mt-3">
          <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
              <h6 class="m-0 font-weight-bold text-primary">{{ __('Institution Profile Form') }}</h6>
          </div>

          <div class="card mt-3 tab-card">
            <div class="card-header tab-card-header">
              <ul class="nav nav-pills card-header-tabs" id="pills-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link" id="one-tab" data-toggle="tab" href="#one" role="tab" aria-controls="One" aria-selected="true">Information</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="two-tab" data-toggle="tab" href="#two" role="tab" aria-controls="Two" aria-selected="false">Contacts</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="three-tab" data-toggle="tab" href="#three" role="tab" aria-controls="Three" aria-selected="false">Modules</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="four-tab" data-toggle="tab" href="#four" role="tab" aria-controls="Four" aria-selected="false">Documents</a>
                </li>
              </ul>
            </div>
    
            


          </div>

@endsection
@section('scripts')
<script type="text/javascript">

"use strict";
$('#myTab a').on('click', function (e) {
  e.preventDefault()
  $(this).tab('show')
})

$('.datepicker').datepicker({
    format: 'dd-mm-yyyy',
    autoclose: true
});
</script>
@endsection