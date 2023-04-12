<html lang="en">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>{{__($gs->title)}}</title>
    <!-- CSS files -->
    {{-- <link rel="shortcut icon" href="{{getPhoto($gs->favicon)}}"> --}}

    <link href="{{asset('assets/user/')}}/css/tabler.min.css" rel="stylesheet"/>
    <link href="{{asset('assets/user/')}}/fontawesome-free/css/all.min.css" rel="stylesheet"/>
    <link href="{{asset('assets/user/')}}/css/tabler-flags.min.css" rel="stylesheet"/>
    <link href="{{asset('assets/user/')}}/css/tabler-payments.min.css" rel="stylesheet"/>
    <link href="{{asset('assets/user/')}}/css/tabler-vendors.min.css" rel="stylesheet"/>
    <link href="{{asset('assets/user/css/custom.css')}}" rel="stylesheet"/>
    <link href="{{asset('assets/user/')}}/css/demo.min.css" rel="stylesheet"/>
</head>

<body>
  <div class="wrapper mb-3 mt-5">
        <div class="page-wrapper">
          <div class="page-body">
            <div class="container-fluid">
            <div class="page-header d-print-none">
                <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                    {{__('KYC Selfie')}}
                    </h2>
                </div>
                </div>
            </div>
            </div>

            <div class="page-body">
            <div class="container-fluid">
                <div class="row row-cards">
                    <div class="col-12">
                        <div class="card p-5">
                                @includeIf('includes.flash')
                                <form action="{{route('user.kyc.selfie.post')}}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group mb-3 mt-3">
                                        <label class="form-label">@lang('Select Type')</label>
                                        <select class="form-select shadow-none" name="type">
                                            <option value="" selected>@lang('Select')</option>
                                            <option value="passport" >@lang('Passport')</option>
                                            <option value="driver_license" >@lang('Driver License')</option>
                                            <option value="ID" >@lang('ID')</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-3 mt-3">
                                        <label class="form-label required">@lang('Own Photo')</label>
                                        <div id="my_camera"></div>
                                        <br/>
                                        <input type=button value="Take a Photo" onClick="take_snapshot()">
                                        <input type="hidden" name="image" class="image-tag">
                                    </div>
                                    <div class="form-group mb-3 mt-3">
                                        <div id="results">{{__("Your captured photo will appear here...")}}</div>
                                    </div>
                                    <div class="form-group mb-3 mt-3">
                                        <label class="form-label required">@lang('Front')</label>
                                        <input type=button value="Take a Photo for front part." onClick="take_front()">
                                        <input type="hidden" name="image_front" class="image-tag-front">
                                    </div>
                                    <div class="form-group mb-3 mt-3">
                                        <div id="results-front">{{__("Your captured photo will appear here...")}}</div>
                                    </div>
                                    <div class="form-group mb-3 mt-3">
                                        <label class="form-label required">@lang('Back')</label>
                                        <input type=button value="Take a Photo for back part." onClick="take_back()">
                                        <input type="hidden" name="image_back" class="image-tag-back">
                                    </div>
                                    <div class="form-group mb-3 mt-3">
                                        <div id="results-back">{{__("Your captured photo will appear here...")}}</div>
                                    </div>
                                    <input type="hidden" name="user_id" value="{{$user_id}}">
                                    <div class="form-footer">
                                        <button type="submit" class="btn btn-primary w-100">{{__('Submit')}}</button>
                                    </div>
                                </form>
                        </div>
                    </div>
                </div>
            </div>
            </div>
          </div>
        </div>
  </div>

  <script src="{{asset('assets/user/js/jquery-3.6.0.min.js')}}"></script>
  <script src="{{asset('assets/user/js/tabler.min.js')}}"></script>
  <script src="{{asset('assets/user/js/demo.min.js')}}"></script>
  <script src="{{asset('assets/front/js/custom.js')}}"></script>
  <script src="{{asset('assets/user/js/notify.min.js')}}"></script>
  <script src="{{asset('assets/user/js/webcam.min.js')}}"></script>
  <script src="{{asset('assets/front/js/toastr.min.js')}}"></script>
  <script src="{{asset('assets/user/')}}/js/instascan.min.js"></script>
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

  function take_front() {
      Webcam.snap( function(data_uri) {
          $(".image-tag-front").val(data_uri);
          document.getElementById('results-front').innerHTML = '<img src="'+data_uri+'"/>';
      } );
  }

  function take_back() {
      Webcam.snap( function(data_uri) {
          $(".image-tag-back").val(data_uri);
          document.getElementById('results-back').innerHTML = '<img src="'+data_uri+'"/>';
      } );
  }
</script>
</body>
</html>
