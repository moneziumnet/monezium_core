<html lang="en">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>{{__($gs->title)}} - @lang('Invoice : '.$invoice->number) </title>
    <!-- CSS files -->
    {{-- <link rel="shortcut icon" href="{{getPhoto($gs->favicon)}}"> --}}

    <link rel="shortcut icon" href="{{asset('assets/images/'.$gs->favicon)}}">
    <link href="{{ asset('assets/user/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{asset('assets/user/css/tabler.min.css')}}" rel="stylesheet"/>
    <link href="{{asset('assets/user/css/tabler-flags.min.css')}}" rel="stylesheet"/>
    <link href="{{asset('assets/user/css/tabler-payments.min.css')}}" rel="stylesheet"/>
    <link href="{{asset('assets/user/css/tabler-vendors.min.css')}}" rel="stylesheet"/>
	<link rel="stylesheet" href="{{asset('assets/front/css/toastr.min.css')}}">
    <link href="{{asset('assets/user/css/demo.min.css')}}" rel="stylesheet"/>
    <link href="{{asset('assets/user/css/custom.css')}}" rel="stylesheet"/>
    @stack('css')
  </head>

  <body>
    @php
        $type = $invoice->type ? $invoice->type : 'Invoice';
    @endphp
    <div class="wrapper mb-3">
          <div class="page-wrapper">
            <div class="container-xl">
              <!-- Page title -->
              <div class="page-header text-white d-print-none">
                <div class="row align-items-center">
                  <div class="col">
                    <h2 class="page-title">
                        @lang('Invoice')
                    </h2>
                  </div>
                  <!-- Page title actions -->
                  <div class="col-auto ms-auto d-print-none">
                    <a href="{{route('invoice.pay',encrypt($invoice->number))}}" class="btn btn-secondary">
                     <i class="fas fa-file-invoice-dollar me-2"></i>
                      @lang('Pay Invoice')
                    </a>
                    <button type="button" class="btn btn-primary ms-2" onclick="javascript:window.print();">
                      <i class="fas fa-print me-2"></i>
                      @lang('Print Invoice')
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <div class="page-body">
                <div class="container-xl">
                    <div class="card card-lg">
                      <div class="card-body">
                        @if ($invoice->template == 0)
                          @include('user.invoice.template_basic')
                        @elseif ($invoice->template == 1)
                          @include('user.invoice.template_classic')
                        @else
                          @include('user.invoice.template_pro')
                        @endif
                      </div>
                    </div>
                </div>
            </div>
          </div>
      </div>
      <!-- Tabler Core -->
      <script src="{{asset('assets/front/js/toastr.min.js')}}"></script>
    <script src="{{asset('assets/user/js/jquery-3.6.0.min.js')}}"></script>
    <script src="{{asset('assets/user/js/tabler.min.js')}}"></script>
    <script src="{{asset('assets/user/js/demo.min.js')}}"></script>
    <script src="{{asset('assets/front/js/custom.js')}}"></script>
    <script src="{{asset('assets/user/js/notify.min.js')}}"></script>
    <script src="{{asset('assets/front/js/toastr.min.js')}}"></script>

      {{-- @include('notify.alert') --}}
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
      @stack('js')
    </body>
</html>
