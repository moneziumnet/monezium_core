<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>{{$gs->title}}</title>

    <link rel="stylesheet" href="{{asset('assets/front/css/bootstrap.min.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/front/css/animate.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/front/css/all.min.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/front/css/lightbox.min.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/front/css/odometer.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/front/css/owl.min.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/front/css/main.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/front/css/toastr.min.css')}}">

    <link rel="stylesheet" href="{{ asset('assets/front/css/styles.php?color='.str_replace('#','',$gs->colors)) }}">

    @if ($default_font->font_value)
        <link href="https://fonts.googleapis.com/css?family={{ $default_font->font_value }}&display=swap" rel="stylesheet">
    @else
        <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
    @endif

    @if ($default_font->font_family)
        <link rel="stylesheet" id="colorr" href="{{ asset('assets/front/css/font.php?font_familly='.$default_font->font_family) }}">
    @else
        <link rel="stylesheet" id="colorr" href="{{ asset('assets/front/css/font.php?font_familly='."Open Sans") }}">
    @endif

    @stack('css')
    <link rel="shortcut icon" href="{{asset('assets/images/'.$gs->favicon)}}">
</head>

<body>
    <div class="align-items-center d-flex h-100 " style="background-color: rgba(0,0,0,.03);">
        <div class="card p-4 mx-auto" style="max-width:400px;">

            <div class="card-body my-4">
                <div class="text-center text-black my-4">
                    <h3 class="text-dark" ><b>{{__("Welcome")}}</b></h2>
                </div>
                <div class = "text-center my-4">
                    <img src="{{asset('assets/images/'.$gs->logo)}}" width="50%" >
                </div>

                <form class="row gy-3 gx-4 align-items-center" id="loginform" action="{{ route('user.login.submit') }}" method="POST">
                    @csrf
                    <div class="col-sm-12">
                        @includeIf('includes.user.form-both')
                    </div>
                    <div class="col-sm-12">
                        <input type="email" id="email" name="email" placeholder="Email" class="form-control form--control">
                    </div>
                    <div class="col-sm-12 mt-3">
                        <input type="password" id="password" name="password"  placeholder="Password" class="form-control form--control">
                    </div>
                    <input type="hidden" id="global_ip" name="global_ip" class="form-control form--control">

                    <div class="col-sm-12">
                        <button type="submit" class="btn btn-primary w-100 mt-4" style="border-radius:2.25rem">
                            @lang('Login')
                        </button>
                    </div>
                </form>

                <p class="text-muted text-center mt-5 ">
                    <small class="mt-5">{{__("Don't have an Account?")}} <a href="{{ url('/user/register/1') }}">{{ __('Sign Up') }}</a></small>
                </p>
            </div>
        </div>
    </div>
    <!-- Tabler Core -->
    <script src="{{asset('assets/front/js/jquery-3.6.0.min.js')}}"></script>
    <script src="{{asset('assets/front/js/bootstrap.min.js')}}"></script>
    <script src="{{asset('assets/front/js/viewport.jquery.js')}}"></script>
    <script src="{{asset('assets/front/js/odometer.min.js')}}"></script>
    <script src="{{asset('assets/front/js/lightbox.min.js')}}"></script>
    <script src="{{asset('assets/front/js/owl.min.js')}}"></script>
    <script src="{{asset('assets/front/js/toastr.min.js')}}"></script>
    <script src="{{asset('assets/front/js/notify.js')}}"></script>
    <script src="{{asset('assets/front/js/main.js')}}"></script>
    <script src="{{asset('assets/front/js/custom.js')}}"></script>

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

    <script type="text/javascript">
        $(document).ready(function() {
            $.getJSON("https://api.ipify.org/?format=json", function(e) {
                $('#global_ip').val(e.ip);
            });
        })
    </script>


</body>

</html>
