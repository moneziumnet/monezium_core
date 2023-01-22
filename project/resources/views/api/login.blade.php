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
                    <h2 class="me-3">Login</h2>
                </div>

                <form class="row gy-3 gx-4 align-items-center" action="{{ route('api.pay.login.submit') }}" method="POST">
                    @csrf
                    <div class="col-sm-12">
                        @includeIf('includes.user.form-both')
                    </div>
                    <div class="col-sm-12">
                        <label for="email" class="form-label">@lang('Your Email')</label>
                        <input type="email" id="email" name="email" class="form-control form--control">
                    </div>
                    <div class="col-sm-12">
                        <label for="password" class="form-label">@lang('Your Password')</label>
                        <input type="password" id="password" name="password" class="form-control form--control">
                    </div>
                    <div class="col-sm-12">
                        <button type="submit" class="btn btn-primary w-100 mt-3">
                            @lang('Login Now')
                        </button>
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
        @if(Session::has('message'))
        toastr.options =
        {
            "closeButton" : true,
            "progressBar" : true
        }
        toastr.success("{{ session('message') }}");
        @endif

        @if(Session::has('error'))
        toastr.options =
        {
            "closeButton" : true,
            "progressBar" : true
        }
        toastr.error("{{ session('error') }}");
        @endif
    </script>
</body>

</html>
