@extends('layouts.user')

@push('css')
<style>
    .document {
        display:none;
        }

        </style>
@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
        @include('user.ex_payment_tab')
      <div class="row align-items-center mt-3">
        <div class="col">
          <h2 class="page-title">
            {{__('Withdraw (Crypto)')}}
          </h2>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card p-3 p-sm-4 p-lg-5">
                    @includeIf('includes.flash')
                    <form action="{{ route('user.cryptowithdraw.store') }}" method="post"  enctype="multipart/form-data">
                        @csrf
                        <div class="form-group mt-3">
                            <label class="form-label required">{{__('Select Crypto')}}</label>
                            <select name="currency_id" id="currency_id" class="form-select" required>
                                <option value="">{{ __('Select Crypto Currency') }}</option>
                                @foreach ($wallets as $key => $wallet)
                                        <option value="{{$wallet->currency->id}}">{{$wallet->currency->code}} -- ({{amount($wallet->balance,$wallet->currency->type,8)}})</option>
                                @endforeach
                            </select>
                            <span class="ms-2 check"></span>
                        </div>

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Amount')}}</label>
                            <input name="amount" id="amount" class="form-control" autocomplete="off" placeholder="{{__('0.0')}}" type="number" step="any" value="{{ old('amount') }}" required>
                        </div>


                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Your Crypto Address')}}</label>
                            <input name="sender_address" id="sender_address" class="form-control" autocomplete="off" placeholder="{{__('0x....')}}" type="text" value="{{ old('sender_address') }}" required>
                        </div>


                        <input type="hidden" name="user_id" value="{{auth()->id()}}">

                        <div class="form-footer">
                            <button id="submit" class="btn btn-primary w-100">{{__('Submit')}}</button>
                        </div>

                        <div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                <div class="modal-status bg-primary"></div>
                                <div class="modal-body py-4">
                                    <div class="text-center">

                                        <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                                        <h3>@lang('Details')</h3>
                                    </div>
                                    <ul class="list-group mt-2">
                                        <li class="list-group-item d-flex justify-content-between">@lang('Receiver Address')<span id="receiver_address"></span></li>
                                        <li class="list-group-item d-flex justify-content-between">@lang('Your Crypto Address')<span id="modal_se_address"></span></li>
                                        <li class="list-group-item d-flex justify-content-between">@lang('Amount')<span id="re_amount"></span></li>
                                        <li class="list-group-item d-flex justify-content-between">@lang('currency')<span id="re_currency"></span></li>
                                    </ul>

                                    <div class="form-group mt-3" id="otp_body">
                                        <label class="form-label required">{{__('OTP Code')}}</label>
                                        <input name="otp_code" id="otp_code" class="form-control" placeholder="{{__('OTP Code')}}" type="text" step="any" value="{{ old('opt_code') }}" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                <div class="w-100">
                                    <div class="row">
                                    <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">
                                        @lang('Cancel')
                                        </a></div>
                                    <div class="col">
                                        <button type="submit" class="btn btn-primary w-100 confirm">
                                        @lang('Confirm')
                                        </button>
                                    </div>
                                    </div>
                                </div>
                                </div>
                            </div>
                            </div>
                        </div>


                    </form>


                </div>
            </div>
        </div>
    </div>
</div>



@endsection

@push('js')

  <script type="text/javascript">
  'use strict';
      $('#currency_id').on('click', function() {
          var url = '{{route('user.cryptodeposit.currency')}}';
          var token = '{{ csrf_token() }}';
          var data  = {id:$(this).val(),_token:token}
          $.post(url,data, function(res) {
              $('.check').text('@lang('Received Address is ')' + res).addClass('text-success');
              $('#receiver_address').text(res);
          })
      })

      $('#submit').on('click', function() {
        if (($('#currency_id').val().length != 0) && ($('#sender_address').val().length != 0) && ($('#amount').val().length != 0)) {
            var verify = "{{$user->paymentCheck('Withdraw Crypto')}}";
            event.preventDefault();
            $('#modal_se_address').text($('#sender_address').val());
            $('#re_currency').text($('#currency_id option:selected').text().split('--')[0]);
            $('#re_amount').text($('#amount').val());
            $('#re_description').text($('#details').val());
            if (verify) {
                var url = "{{url('user/sendotp')}}";
                $.get(url,function (res) {
                    console.log(res)
                    if(res=='success') {
                        $('#modal-success').modal('show');
                    }
                    else {
                        alert('The OTP code can not be sent to you.')
                    }
                });
            } else {
                $('#otp_body').remove();
                $('#modal-success').modal('show');
            }
            $('#modal-success').modal('show')
        }
      })
  </script>

@endpush
