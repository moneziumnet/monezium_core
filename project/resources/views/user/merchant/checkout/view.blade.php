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
            <div class="container-xl">
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
            <div class="container-xl">
                <div class="row row-cards">
                    <div class="col-12">
                        <div class="card p-5">
                                @includeIf('includes.flash')
                                <form action="{{route('user.merchant.checkout.transaction')}}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="text-center">

                                        <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                                        <h3>@lang('Details')</h3>
                                        <img id="qrcode" src="{{'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl='.$checkout->currency->address.'&choe=UTF-8'}}" class="" alt="">
                                    </div>
                                    <div class="text-center mt-2">
                                        <span id="qrdetails" class="ms-2 check">{{__($checkout->currency->address)}}</span>
                                    </div>

                                    <div class="form-group mb-3 mt-3">
                                        <label class="form-label required">{{__('Address')}}</label>
                                        <input name="address" id="address" class="form-control" autocomplete="off"  type="text" value="{{ $checkout->currency->address }}" readonly required>
                                    </div>

                                    <div class="form-group mb-3 mt-3">
                                        <label class="form-label required">{{__('Amount')}}</label>
                                        <input name="amount" id="amount" class="form-control" autocomplete="off"  type="text" value="{{ $checkout->amount }}" readonly required>
                                    </div>

                                    <div class="form-group mb-3 mt-3">
                                        <label class="form-label required">{{__('Hash')}}</label>
                                        <input name="hash" id="hash" class="form-control" autocomplete="off"  type="text" placeholder="0x...." required>
                                    </div>
                                    <input type="hidden" name="id" value="{{$checkout->id}}">

                                    <div class="form-footer">
                                        <button type="submit" class="btn btn-primary w-100">{{__('Done')}}</button>
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

</body>
</html>
