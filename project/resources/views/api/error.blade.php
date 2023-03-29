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
                Genius Bank Payment
            </div>
            <div class="card-body">
                <div class="text-center my-4">
                    <h2 class="me-3">Your access has been blocked!</h2>
                </div>

                <div class="text-center" style="margin: 100px 0px;">
                    <h3>Invalid Site Access Key or Shop Site Key.<br/>Please contact your site owner.</h3>
                </div>

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

    @stack('js')
</body>

</html>
