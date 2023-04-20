<!DOCTYPE html>
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

    {{-- <link rel="stylesheet" href="{{ asset('assets/front/css/styles.php?color='.str_replace('#','',$gs->colors)) }}"> --}}

    @if ($default_font->font_value)
        <link href="https://fonts.googleapis.com/css?family={{ $default_font->font_value }}&display=swap" rel="stylesheet">
    @else
        <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
    @endif

    @stack('css')
    <link rel="shortcut icon" href="{{asset('assets/images/'.$gs->favicon)}}">
</head>

<body>
    <!-- Overlayer -->
    <span class="toTopBtn">
        <i class="fas fa-angle-up"></i>
    </span>
    <div class="overlayer"></div>
    <div class="loader">
        <h2>
            <span class="let1">l</span>
            <span class="let2">o</span>
            <span class="let3">a</span>
            <span class="let4">d</span>
            <span class="let5">i</span>
            <span class="let6">n</span>
            <span class="let7">g</span>
        </h2>
    </div>
    <!-- Overlayer -->

    <!-- Header -->
    <header class="position-relative">
        <div class="navbar-top bg--title">
            <div class="container">
                @include('partials.front.navbar')
            </div>
        </div>
        <div class="navbar-bottom">
            <div class="container">
                @include('partials.front.nav')
            </div>
        </div>
    </header>
    <!-- Header -->

    @yield('content')

    <!-- Footer -->
    @include('partials.front.footer')
    <!-- Footer -->

    @include('cookie-consent::index')

    <!-- Modal -->
    <div class="modal fade" id="modal-apply">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg--section">
                    <h5 class="modal-title loan-title m-0">@lang('Basic')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('user.loan.amount') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="pt-3 pb-4">
                            <label for="amount" class="form-label">@lang('Amount')</label>
                            <div class="input-group input--group">
                                <input type="number" name="amount" class="form-group-input form-control form--control"
                                    placeholder="0.00" id="amount">
                                <button type="button" class="input-group-text">{{$currency->curr_name}}</button>
                            </div>
                            <input type="hidden" name="planId" id="planId" value="">
                        </div>
                    </div>
                    <div class="modal-footer bg--section">
                        <button type="button" class="btn shadow-none btn--danger" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn shadow-none btn--success">@lang('Proceed')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal -->

    <!-- Modal -->
    <div class="modal fade" id="modal-pension">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg--section">
                    <h5 class="modal-title loan-title m-0">@lang('Basic')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('user.fdr.amount') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="pt-3 pb-4">
                            <label for="amount" class="form-label">@lang('Amount')</label>
                            <div class="input-group input--group">
                                <input type="number" name="amount" class="form-group-input form-control form--control"
                                    placeholder="0.00" id="amount">
                                <button type="button" class="input-group-text">{{$currency->curr_name}}</button>
                            </div>
                            <input type="hidden" name="planId" id="fdrplan" value="">
                        </div>
                    </div>
                    <div class="modal-footer bg--section">
                        <button type="button" class="btn shadow-none btn--danger" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn shadow-none btn--success">@lang('Proceed')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal -->


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
		let mainurl = '{{ url('/') }}';
	</script>

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
      
        @if(Session::has('info'))
        toastr.options =
        {
            "closeButton" : true,
            "progressBar" : true
        }
            toastr.info("{{ session('info') }}");
        @endif
      
        @if(Session::has('warning'))
        toastr.options =
        {
            "closeButton" : true,
            "progressBar" : true
        }
            toastr.warning("{{ session('warning') }}");
        @endif
    </script>

    <script>
        'use strict';
        function showDiv(select) {
            var company_input_list = $('.company-input');
            var private_input_list = $('.private-input');
            if (select.value == 1) {
                document.getElementById('corporate_div').style.display = "block";
                document.getElementById('private_div').style.display = "none";
                for (let i = 0; i < company_input_list.length; i++) {
                    const item = company_input_list[i];
                    item.required = true;
                }
                for (let i = 0; i < private_input_list.length; i++) {
                    const item = private_input_list[i];
                    item.required = false;
                }
            } else {
                document.getElementById('corporate_div').style.display = "none";
                document.getElementById('private_div').style.display = "block";
                for (let i = 0; i < company_input_list.length; i++) {
                    const item = company_input_list[i];
                    item.required = false;
                }
                for (let i = 0; i < private_input_list.length; i++) {
                    const item = private_input_list[i];
                    item.required = true;
                }
            }
        }


        $('.apply-loan').on('click',function(){
            let id = $(this).data('id');
            let title = $(this).data('title');

            $('#planId').val(id);
            $('.loan-title').text(title);
        });

        $('.apply-pension').on('click',function(){
            let id = $(this).data('id');
            let title = $(this).data('title');

            $('#fdrplan').val(id);
            $('.loan-title').text(title);
        });
    </script>
    @stack('js')
</body>

</html>
