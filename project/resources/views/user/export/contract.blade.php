<html lang="en">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>{{__($gs->title)}} - @lang('Contract : '.$data->id) </title>
    <!-- CSS files -->
    {{-- <link rel="shortcut icon" href="{{getPhoto($gs->favicon)}}"> --}}
    <link rel="stylesheet" href="{{asset('assets/admin/css/font-awsome.min.css')}}">

    <link href="{{asset('assets/user/')}}/css/tabler.min.css" rel="stylesheet"/>
    <link href="{{asset('assets/user/')}}/css/tabler-flags.min.css" rel="stylesheet"/>
    <link href="{{asset('assets/user/')}}/css/tabler-payments.min.css" rel="stylesheet"/>
    <link href="{{asset('assets/user/')}}/css/tabler-vendors.min.css" rel="stylesheet"/>
    <link href="{{asset('assets/user/css/custom.css')}}" rel="stylesheet"/>
    <link href="{{asset('assets/user/')}}/css/demo.min.css" rel="stylesheet"/>


    <link rel="stylesheet" type="text/css" href="{{asset('assets/user/')}}/css/bootstrap-4.3.1.css">
    <script type="text/javascript" src="{{asset('assets/user/')}}/js/jquery-1.12.4.min.js"></script>
    <link type="text/css" href="{{asset('assets/user/')}}/css/jquery-ui.css" rel="stylesheet">
    <script type="text/javascript" src="{{asset('assets/user/')}}/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="{{asset('assets/user/')}}/js/jquery.signature.js"></script>
    <link rel="stylesheet" type="text/css" href="{{asset('assets/user/')}}/css/jquery.signature.css">

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
                            <div class="mt-5 mb-3">
                                @foreach ($information as $title => $text)
                                    <h2 class="ms-1">{{ $title }}</h2>
                                    <p>{!!nl2br($text)!!}</p>
                                @endforeach
                            </div>

                            @if ($data->status == 1)
                                <div>
                                    <div class="wrapper-image-preview text-center" style="width:250px">
                                        <p class="text-muted"> {{__('Contractor signed')}} </p>
                                        <div class="contract-signature-preview-pdf" style="background-image: url({{ $data->contracter_image_path ? asset('assets/images/'.$data->contracter_image_path) : '' }});"></div>
                                    </div>
                                    <div class="wrapper-image-preview text-center" style="width: 250px;position: absolute;right: 15px;margin-top: -115px;">
                                        <p class="text-muted">{{ __('Customer signed')}}</p>
                                        <div class="contract-signature-preview-pdf" style="background-image: url({{ $data->customer_image_path ? asset('assets/images/'.$data->customer_image_path) : '' }});"></div>
                                    </div>
                                </div>
                            @else
                            <div>
                                <div class="wrapper-image-preview text-center" style="width:250px">
                                    <p class="text-muted">{{$data->contracter_image_path ? __('Contractor signed') : __('Contractor not signed')}}</p>
                                    <div class="contract-signature-preview-pdf" style="background-image: url({{ $data->contracter_image_path ? asset('assets/images/'.$data->contracter_image_path) : '' }});"></div>
                                </div>
                                <div class="wrapper-image-preview text-center" style="width: 250px;position: absolute;right: 15px;margin-top: -120px;">
                                    <p class="text-muted">{{$data->customer_image_path ? __('Customer signed') : __('Customer not signed')}}</p>
                                    <div class="contract-signature-preview-pdf" style="background-image: url({{ $data->customer_image_path ? asset('assets/images/'.$data->customer_image_path) : '' }});"></div>
                                </div>
                            </div>
                            @endif
                        </div>
                        <p class="text-muted text-center mt-5">{{__('Thank you very much for doing new contract. We look forward to working with
                            you again!')}} <br> <small class="mt-5">{{__('All right reserved ')}} <a href="{{url('/')}}">{{$gs->title}}</a></small></p>

                      </div>
                    </div>
                </div>
            </div>
          </div>
      </div>
      <script src="{{asset('assets/admin/js/jquery.min.js')}}"></script>
      <!-- Tabler Core -->
      <script src="{{asset('assets/user/')}}/js/tabler.min.js"></script>
      <script src="{{asset('assets/user/')}}/js/demo.min.js"></script>
      {{-- @include('notify.alert') --}}
      @stack('script')

</body>
</html>
