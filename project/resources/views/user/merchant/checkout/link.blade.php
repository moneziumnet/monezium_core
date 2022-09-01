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
                    <h2 class="text-center">
                    {{__('Payment Method')}}
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
                                <form action="{{route('user.merchant.checkout.link_pay', $checkout->ref_id)}}" method="GET" id="pay_form" enctype="multipart/form-data">
                                    @csrf
                                    <div class="text-center">
                                        <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                                        <div class="col">
                                            <h2>{{$checkout->name}}
                                            </h2>
                                            <div class="page-pretitle">
                                            {{$checkout->description}}
                                            </div>
                                        </div>
                                        <h3></h3>
                                    </div>
                                    <div class="form-group mb-3 mt-3">
                                        <label class="form-label required">{{__('Amount')}}({{$checkout->currency->code}})</label>
                                        @if ($checkout->amount == 0)
                                        <input name="amount" id="amount" class="form-control" autocomplete="off"  type="text" value="{{ $checkout->amount }}" required>
                                        @else
                                        <input name="amount" id="amount" class="form-control" autocomplete="off"  type="text" value="{{ $checkout->amount }}" readonly required>
                                        @endif
                                    </div>
                                    <input type="hidden" name="id" value="{{$checkout->id}}">
                                    <div class="form-footer">
                                        @foreach($cryptolist as $currency)
                                        <button type="submit" id="submit" name="link_pay_submit" value="{{$currency->id}}" class="btn btn-primary w-100">{{$currency->curr_name}} - {{$currency->code}}</button>
                                        <h3></h3>
                                    @endforeach
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
