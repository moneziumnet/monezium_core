@extends('layouts.user')

@section('styles')

@endsection

@section('contents')
<div class="container-xl">
  <div class="page-header d-print-none">
    <div class="row align-items-center">
      <div class="col">
        <h2 class="page-title">
          {{__('KYC Form')}}
        </h2>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
      <div class="row row-cards">
          <div class="col-12">
              <div class="card p-5">
                      @includeIf('includes.flash')
                      <form action="{{route('user.kyc.submit')}}" method="POST" enctype="multipart/form-data">
                          @csrf
                          <div class="form-group mb-3 mt-3">
                              <label class="form-label required">@lang('Own Photo')</label>
                              <div id="my_camera"></div>
                              <br/>
                              <input type=button value="Take a Photo" onClick="take_snapshot()">
                              <input type="hidden" name="image" class="image-tag">
                          </div>
                          <div class="form-group mb-3 mt-3">
                              <div id="results">Your captured photo will appear here...</div>
                          </div>
                          <div class="form-footer">
                            <button type="submit" class="btn btn-primary w-100">{{__('Submit')}}</button>
                          </div>
                      </form>
              </div>
          </div>
      </div>
  </div>
</div>


@endsection

@push('js')
<script language="JavaScript">
  Webcam.set({
      width: 490,
      height: 350,
      image_format: 'jpeg',
      jpeg_quality: 90
  });
  
  Webcam.attach( '#my_camera' );
  
  function take_snapshot() {
      Webcam.snap( function(data_uri) {
          $(".image-tag").val(data_uri);
          document.getElementById('results').innerHTML = '<img src="'+data_uri+'"/>';
      } );
  }
</script>
@endpush
