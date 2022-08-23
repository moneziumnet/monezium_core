<html lang="en">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>{{__($gs->title)}} - @lang('AoA : '.$data->id) </title>
    <!-- CSS files -->
    {{-- <link rel="shortcut icon" href="{{getPhoto($gs->favicon)}}"> --}}
    <link rel="stylesheet" href="{{asset('assets/admin/css/font-awsome.min.css')}}">

    <link href="{{asset('assets/user/')}}/css/tabler.min.css" rel="stylesheet"/>
    <link href="{{asset('assets/user/')}}/css/tabler-flags.min.css" rel="stylesheet"/>
    <link href="{{asset('assets/user/')}}/css/tabler-payments.min.css" rel="stylesheet"/>
    <link href="{{asset('assets/user/')}}/css/tabler-vendors.min.css" rel="stylesheet"/>
    <link href="{{asset('assets/user/css/custom.css')}}" rel="stylesheet"/>
    <link href="{{asset('assets/user/')}}/css/demo.min.css" rel="stylesheet"/>


    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.css">
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <link type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/south-street/jquery-ui.css" rel="stylesheet">
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script type="text/javascript" src="http://keith-wood.name/js/jquery.signature.js"></script>
    <link rel="stylesheet" type="text/css" href="http://keith-wood.name/css/jquery.signature.css">

    <style>
        .kbw-signature { width: 100%; height: 200px;}
        #sig canvas{
            width: 100% !important;
            height: auto;
        }
    </style>


    @stack('style')
  </head>

  <body>
    <div class="wrapper mb-3 mt-5">
          <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">
                    <div class="card card-lg">
                      <div class="card-body">
                        @include('includes.flash')
                        <div class="row">
                            <div class="text-center">
                                <h1>
                                    {{__($data->title)}}
                                </h1>
                            </div>
                            <div class="text-center mt-5">
                                <p>
                                    {{__($description)}}
                                </p>
                            </div>
                            <div class = "row">

                                <div class="wrapper-image-preview col-6">
                                    <p class="text-muted text-center"> {{__('Contracter')}} </p>
                                    <div class="box full-width">
                                        <div class="back-preview-image" style="background-image: url({{ $data->contracter_image_path ? asset('assets/images/'.$data->contracter_image_path) : '' }});"></div>
                                    </div>
                                </div>

                                <div class="wrapper-image-preview col-6">
                                    <p class="text-muted text-center"> {{__('Customer')}} </p>
                                    <div class="box full-width">
                                        <div class="back-preview-image" style="background-image: url({{ $data->customer_image_path ? asset('assets/images/'.$data->customer_image_path) : '' }});"></div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <p class="text-muted text-center mt-5">{{__('Thank you very much for doing new Aoa. We look forward to working with
                            you again!')}} <br> <small class="mt-5">{{__('All right reserved ')}} <a href="{{url('/')}}">{{$gs->title}}</a></small></p>

                      </div>
                    </div>
                </div>
            </div>
          </div>
      </div>
      <script src="{{asset('assets/admin/js/jquery.min.js')}}"></script>
      <script src="{{asset('assets/user/')}}/js/tabler.min.js"></script>
      <script src="{{asset('assets/user/')}}/js/demo.min.js"></script>
      @stack('script')

</body>
</html>
