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
                        <h2 class="me-3">Payment by {{ucFirst($merchant_setting->keyword)}}</h2>

                </div>

                <form action="{{route('api.pay.submit')}}" method="POST" enctype="multipart/form-data" id="pay_form_submit">
                    @csrf


                    <div class="form-group mb-3 mt-3">
                        <label class="form-label required">{{__('Amount')}}</label>
                        <input name="amount" id="amount" class="form-control" autocomplete="off"  type="number" value="{{ $amount }}" readonly required>
                    </div>

                    @if ($merchant_setting->keyword == 'stripe')
                        <input type="hidden" name="cmd" value="_xclick">
                        <input type="hidden" name="no_note" value="1">
                        <input type="hidden" name="lc" value="UK">
                        <input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest">

                        <div class="col-lg-6 mb-3">
                            <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control card-elements" name="cardNumber" placeholder="{{ __('Card Number') }}" autocomplete="off" autofocus oninput="validateCard(this.value);"/>
                            <span id="errCard"></span>
                        </div>

                        <div class="col-lg-6 cardRow mb-3">
                            <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control card-elements" placeholder="{{ ('Card CVC') }}" name="cardCVC" oninput="validateCVC(this.value);">
                            <span id="errCVC"></span>
                        </div>

                        <div class="col-lg-6 mb-3">
                            <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control card-elements" placeholder="{{ __('Month') }}" name="month" >
                        </div>

                        <div class="col-lg-6">
                            <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control card-elements" placeholder="{{ __('Year') }}" name="year">
                        </div>
                    @endif

                    <input type="hidden" name="currency_id" value="{{$currency->id}}">
                    <input type="hidden" name="user_id" value="{{$merchant_setting->user_id}}">
                    <input type="hidden" name="payment" value="gateway">
                    <input type="hidden" name="gateway_id" value="{{$merchant_setting->id}}">
                    <input type="hidden" name="shop_key" value="{{$shop_key}}">
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

    <script type="text/javascript" src="{{ asset('assets/front/js/payvalid.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/front/js/paymin.js') }}"></script>
    <script type="text/javascript" src="https://js.stripe.com/v3/"></script>
    <script type="text/javascript" src="{{ asset('assets/front/js/payform.js') }}"></script>

    <script>
        'use strict';
        $(document).on('submit','#pay_form_submit',function(e){
            if($(this).attr('method').toUpperCase() == "POST") {
                e.preventDefault();

                $.ajax({
                    method: $(this).attr('method'),
                    url: $(this).attr('action'),
                    headers: {
                        'Access-Control-Allow-Origin': '*',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: $(this).serialize(),
                    success: function(msg) {
                        window.close();
                        window.opener.postMessage(msg,"*");
                    }
                });
            }
        });
        var cnstatus = false;
        var dateStatus = false;
        var cvcStatus = false;

        function validateCard(cn) {
        cnstatus = Stripe.card.validateCardNumber(cn);
        if (!cnstatus) {
            $("#errCard").html('Card number not valid<br>');
        } else {
            $("#errCard").html('');
        }
        btnStatusChange();


        }

        function validateCVC(cvc) {
        cvcStatus = Stripe.card.validateCVC(cvc);
        if (!cvcStatus) {
            $("#errCVC").html('CVC number not valid');
        } else {
            $("#errCVC").html('');
        }
        btnStatusChange();
        }
    </script>
    @stack('js')
</body>

</html>
