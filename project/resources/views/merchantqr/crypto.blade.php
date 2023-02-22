<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>{{ __($gs->title) }} - Payment</title>
    <!-- CSS files -->
    {{-- <link rel="shortcut icon" href="{{getPhoto($gs->favicon)}}"> --}}

    <link rel="shortcut icon" href="{{ asset('assets/images/' . $gs->favicon) }}">
    <link href="{{ asset('assets/user/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/user/css/tabler.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/user/css/tabler-flags.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/user/css/tabler-payments.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/user/css/tabler-vendors.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/front/css/toastr.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/user/css/demo.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/user/css/custom.css') }}" rel="stylesheet" />
    @stack('css')
</head>

<body>
    <div>
        <div class="card my-3 mx-auto" style="max-width:400px;">
            <div class="card-header">
                MT Payment System
            </div>
            <div class="card-body">
                <div class="text-center my-4">

                    <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                        <h2 class="me-3">Payment by crypto</h2>

                </div>

                <form action="{{route('qr.pay.submit')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="text-center">
                        <img id="qrcode" src="{{'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl='.$wallet->wallet_no.'&choe=UTF-8'}}" class="" alt="">
                    </div>
                    <div class="text-center mt-2">
                        <span id="qrdetails" class="ms-2 check">{{__($wallet->wallet_no)}}</span>
                    </div>

                    <div class="form-group mb-3 mt-3">
                        <label class="form-label required">{{$wallet->currency->code}} {{__('Address')}}</label>
                        <input name="address" id="address" class="form-control" autocomplete="off"  type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" value="{{ $wallet->wallet_no }}" readonly required>
                    </div>

                    <div class="form-group mb-3 mt-3">
                        <label class="form-label required">{{__('Amount')}}</label>
                        <input name="amount" id="amount" class="form-control" autocomplete="off"  type="number" value="{{ $total_amount / $cal_amount }}" readonly required>
                    </div>

                    <input type="hidden" name="currency_id" value="{{$wallet->currency->id}}">
                    <input type="hidden" name="user_id" value="{{$wallet->user_id}}">
                    <input type="hidden" name="payment" value="crypto">

                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary w-100">{{__('Done')}}</button>
                    </div>
                </form>

                <p class="text-muted text-center mt-5">
                    <small class="mt-5">All right reserved <br/> <a href="{{ url('/') }}">{{ $gs->title }}</a></small>
                </p>
            </div>
        </div>
    </div>
    <!-- Tabler Core -->
    <script src="{{ asset('assets/front/js/toastr.min.js') }}"></script>
    <script src="{{ asset('assets/user/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/user/js/tabler.min.js') }}"></script>
    <script src="{{ asset('assets/user/js/demo.min.js') }}"></script>
    <script src="{{ asset('assets/front/js/custom.js') }}"></script>
    <script src="{{ asset('assets/user/js/notify.min.js') }}"></script>
    <script src="{{ asset('assets/front/js/toastr.min.js') }}"></script>

    <script>
        'use strict';
    </script>
    @stack('js')
</body>

</html>
