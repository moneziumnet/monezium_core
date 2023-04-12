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
        <div class="card my-3 mx-auto" style="max-width:450px;">
            <div class="card-header">
                {{ __($gs->disqus) }}
            </div>
            <div class="card-body">
                <div class="text-center my-4">
                    <h2 class="me-3">Choose Payment Method</h2>
                </div>

                <form action="{{route('api.pay.submit')}}" id="pay_form_submit" method="POST" name="pay_form_submit">
                    @csrf
                    <div class="payment-select-options">
                        <div class="mb-2">
                            <label class="form-selectgroup-item">
                                <input type="radio" name="payment" value="bank_pay" id="bank_pay"
                                    class="form-selectgroup-input select_method">
                                <span class="form-selectgroup-label">
                                    <i class="fas fa-credit-card me-2"></i>
                                    @lang('Pay with Bank')</span>
                            </label>
                        </div>
                        <div class="mb-2">
                            <label class="form-selectgroup-item">
                                <input type="radio" name="payment" value="gateway" id="gateway"
                                    class="form-selectgroup-input select_method">
                                <span class="form-selectgroup-label">
                                    <i class="fas fa-euro-sign me-2"></i>
                                    @lang('Pay with gateways')</span>
                            </label>
                        </div>
                        <div class="mb-2">
                            <label class="form-selectgroup-item">
                                <input type="radio" name="payment" value="crypto" id="crypto"
                                    class="form-selectgroup-input select_method">
                                <span class="form-selectgroup-label">
                                    <i class="fas fa-coins me-2"></i>
                                    @lang('Pay with Crypto')</span>
                            </label>
                        </div>
                        <div class="mb-2">
                            <label class="form-selectgroup-item">
                                <input type="radio" name="payment" value="wallet" id="wallet"
                                    class="form-selectgroup-input select_method" checked>
                                <span class="form-selectgroup-label">
                                    <i class="fas fa-wallet me-2"></i>
                                    @lang('Pay with customer wallet')
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group mt-4" id="bank_part" style="display: none">
                        <label class="form-label">{{ __('Bank Account') }}</label>
                        <select name="bank_account" id="bank_account" class="form-control">
                            @if (count($bankaccounts) != 0)
                                <option value="">{{ __('Select') }}</option>
                                @foreach ($bankaccounts as $account)
                                    <option value="{{ $account->id }}" data-data="{{ $account }}"
                                        data-bank="{{ $account->subbank }}" data-user="{{ $account->user->company_name ?? $account->user->name }}">
                                        {{ $account->subbank->name }}
                                    </option>
                                @endforeach
                            @else
                                <option value="">{{ __('There is no bank account for this currency.') }}
                                </option>
                            @endif
                        </select>
                    </div>
                    <div id="bank_account_part" style="display: none;">
                        <div class="form-group mt-3 text-start">
                            <label class="form-label">{{ __('Receiver Name') }}</label>
                            <input name="receiver_name" id="receiver_name" class="form-control shadow-none col-md-4"
                                type="text" readonly>
                        </div>
                        <div class="form-group mt-3 text-start">
                            <label class="form-label">{{ __('Bank Name') }}</label>
                            <input name="bank_name" id="bank_name" class="form-control shadow-none col-md-4"
                                type="text" readonly>
                        </div>
                        <div class="form-group mt-3 text-start">
                            <label class="form-label">{{ __('Bank Address') }}</label>
                            <input name="bank_address" id="bank_address" class="form-control shadow-none col-md-4"
                                type="text" readonly>
                        </div>
                        <div class="form-group mt-3 text-start">
                            <label class="form-label">{{ __('Bank IBAN') }}</label>
                            <input name="bank_iban" id="bank_iban" class="form-control shadow-none col-md-4"
                                type="text" readonly>
                        </div>
                        <div class="form-group mt-3 text-start">
                            <label class="form-label">{{ __('Bank SWIFT') }}</label>
                            <input name="bank_swift" id="bank_swift" class="form-control shadow-none col-md-4"
                                type="text" readonly>
                        </div>
                        <div class="form-group mt-3 text-start">
                            <label class="form-label">{{ __('Description') }}</label>
                            <input name="deposit_no" id="deposit_no" class="form-control shadow-none col-md-4"
                                type="text" readonly>
                        </div>
                    </div>

                    <div class="mt-4" id="default_pay" style="display: block;">
                        <button type="submit" class="btn btn-primary w-100 btn-block" id="btn-pay">
                            {{ __('Pay') }} <i class="ms-2 fas fa-long-arrow-alt-right"></i>
                        </button>
                    </div>
                    <div class="mt-4" id="crypto_pay" style="display: none;">
                        @foreach ($cryptolist as $crypto_item)
                            <button name="link_pay_submit" value="{{ $crypto_item->id }}"
                                class="col btn btn-primary w-100 mb-2 crypto-submit">
                                {{ __('Pay with ') }}{{ $crypto_item->curr_name }} -
                                {{ $crypto_item->code }}</button>
                        @endforeach
                    </div>
                    <div class="mt-4" id="gateway_pay" style="display: none;">
                        @foreach ($gateways as $gateway_item)
                            <button name="link_pay_submit" value="{{ $gateway_item->id }}"
                                class="col btn btn-primary w-100 mb-2 gateway-submit">
                                {{ __('Pay with ') }}{{ ucfirst($gateway_item->keyword) }}
                                </button>
                        @endforeach
                    </div>
                    <input type="hidden" name="user_id" value={{$user->id}} />
                    <input type="hidden" name="amount" value={{$amount}} />
                    <input type="hidden" name="currency_id" value={{$currency->id}} />
                    <input type="hidden" name="shop_key" value={{$shop_key}} />
                </form>

                <p class="text-muted text-center mt-5">
                    <small class="mt-5">All right reserved <br/> <a href="{{ url('/') }}">{{ $gs->title }}</a></small>
                </p>
            </div>
        </div>
    </div>
    <div class="modal modal-blur fade" id="modal-details" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
          <div class="modal-content">
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              <div class="modal-status bg-primary"></div>
              <div class="modal-body text-center py-4">
              <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
              <h3>@lang('Payment Details')</h3>
              <p class="bank_details"></p>
              <ul class="list-group details-list mt-2">
                  <li class="list-group-item">@lang('Receiver Name')<span id="detail_user_name"></span></li>
                  <li class="list-group-item">@lang('Bank Name')<span id="detail_bank_name"></span></li>
                  <li class="list-group-item">@lang('Bank Address')<span id="detail_bank_address"></span></li>
                  <li class="list-group-item">@lang('Bank IBAN')<span id="detail_bank_iban"></span></li>
                  <li class="list-group-item">@lang('Bank SWIFT')<span id="detail_bank_swift"></span></li>
                  <li class="list-group-item">@lang('Deposit No')<span id="detail_deposit_no"></span></li>
              </ul>
              <span class="btn btn-primary w-100 mt-3" id="payment_submit">Submit</span>
              </div>
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
        const characters ='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        function generateRandomString(length) {
            let result = ' ';
            const charactersLength = characters.length;
            for ( let i = 0; i < length; i++ ) {
                result += characters.charAt(Math.floor(Math.random() * charactersLength));
            }
            return result;
        }
        $('.select_method').on('click', function() {
            if ($(this).attr('id') == 'bank_pay') {
                $('#pay_form_submit').attr('action', "{{route('api.pay.submit')}}");
                $('#pay_form_submit').attr('method', "POST");
                document.getElementById("crypto_pay").style.display = "none";
                document.getElementById("gateway_pay").style.display = "none";
                document.getElementById("default_pay").style.display = "block";
                $("#bank_account").prop('required', true);
                document.getElementById("bank_part").style.display = "block";
            } else if ($(this).attr('id') == 'crypto') {
                $('#pay_form_submit').attr('action',"{{route('api.pay.crypto')}}");
                $('#pay_form_submit').attr('method', "GET");
                document.getElementById("crypto_pay").style.display = "block";
                document.getElementById("default_pay").style.display = "none";
                document.getElementById("gateway_pay").style.display = "none";
                $("#bank_account").prop('required', false);
                $("#description").prop('required', false);
                document.getElementById('bank_account_part').style.display = "none";
                document.getElementById("bank_part").style.display = "none";
            } else if ($(this).attr('id') == 'gateway') {
                $('#pay_form_submit').attr('action',"{{route('api.pay.gateway')}}");
                $('#pay_form_submit').attr('method', "GET");
                document.getElementById("crypto_pay").style.display = "none";
                document.getElementById("gateway_pay").style.display = "block";
                document.getElementById("default_pay").style.display = "none";
                $("#bank_account").prop('required', false);
                $("#description").prop('required', false);
                document.getElementById('bank_account_part').style.display = "none";
                document.getElementById("bank_part").style.display = "none";
            } else {
                $('#pay_form_submit').attr('action', "{{route('api.pay.submit')}}");
                $('#pay_form_submit').attr('method', "POST");
                document.getElementById("crypto_pay").style.display = "none";
                document.getElementById("gateway_pay").style.display = "none";
                document.getElementById("default_pay").style.display = "block";
                $("#bank_account").prop('required', false);
                $("#description").prop('required', false);
                document.getElementById('bank_account_part').style.display = "none";
                document.getElementById("bank_part").style.display = "none";
            }
        })
        $('#bank_account').on('change', function() {
            var selected = $('#bank_account option:selected').data('data');
            var bank = $('#bank_account option:selected').data('bank');
            var user = $('#bank_account option:selected').data('user');
            if (selected) {
                $('#receiver_name').val(user);
                $('#bank_name').val(bank.name);
                $('#bank_address').val(bank.address);
                $('#bank_iban').val(selected.iban);
                $('#bank_swift').val(selected.swift);
                $('#deposit_no').val(generateRandomString(12));
                document.getElementById('bank_account_part').style.display = "block";
            } else {
                $("#description").prop('required', false);
                document.getElementById('bank_account_part').style.display = "none";
            }
        })

        $('#btn-pay').on('click', function(e) {
            var payment_type = $('input[name=payment]:checked', '#pay_form_submit').val();
            if (payment_type == "bank_pay" && document.getElementById('pay_form_submit').checkValidity()) {
                e.preventDefault();
                $('#modal-details').modal('show');
                $('#detail_user_name').html($('#receiver_name').val());
                $('#detail_bank_name').html($('#bank_name').val());
                $('#detail_bank_address').html($('#bank_address').val());
                $('#detail_bank_iban').html($('#bank_iban').val());
                $('#detail_bank_swift').html($('#bank_swift').val());
                $('#detail_deposit_no').text($('#deposit_no').val());
            }
        });
        $('#payment_submit').on('click', function() {
            if (document.getElementById('pay_form_submit').checkValidity()) {
                $('#pay_form_submit').submit();
            }
        });
        $('.crypto-submit').on('click', function() {
            if (document.getElementById('pay_form_submit').checkValidity()) {
                $('#pay_form_submit').submit();
            }
        });
        
        $('.gateway-submit').on('click', function() {
            if (document.getElementById('pay_form_submit').checkValidity()) {
                $('#pay_form_submit').submit();
            }
        });

        $(document).on('submit','#pay_form_submit',function(e){
            if($(this).attr('method').toUpperCase() == "POST") {
                e.preventDefault();
                
                $.ajax({
                    method: $(this).attr('method'),
                    url: $(this).attr('action'),
                    data: $(this).serialize(),
                    success: function(msg) {
                        if(msg && msg.type == 'login') {
                            window.location = msg.payload
                        } else {
                            window.close();
                            window.opener.postMessage(msg,"*");             
                        }
                    }
                });
            }
        });
    </script>
    @stack('js')
</body>

</html>
