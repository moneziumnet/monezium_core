@extends('layouts.user')

@push('css')
<style>
    .document {
        display:none;
        }

        </style>
@endpush

@section('title', __('Incoming'))

@section('contents')
<div class="container-fluid">
    <div class="page-header d-print-none">
        @include('user.deposittab')
      <div class="row align-items-center mt-3">
        <div class="col">
          <h2 class="page-title">
            {{__('Incoming (Crypto)')}}
          </h2>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-fluid">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card p-3 p-sm-4 p-lg-5">
                    @includeIf('includes.flash')
                    <form action="{{ route('user.cryptodeposit.store') }}" method="post"  enctype="multipart/form-data">
                        @csrf
                        <div class="form-group mt-3">
                            <label class="form-label required">{{__('Select Crypto')}}</label>
                            <select name="currency_id" id="currency_id" class="form-select" required>
                                <option value="">{{ __('Select Crypto Currency') }}</option>
                                @foreach ($cryptocurrencies as $key => $currency)
                                        <option value="{{$currency->id}}">{{$currency->code}}</option>
                                @endforeach
                            </select>
                            <span class="ms-2 check"></span>
                        </div>

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Amount')}}</label>
                            <input name="amount" id="amount" class="form-control" autocomplete="off" placeholder="{{__('0.0')}}" type="number" step="any" value="{{ old('amount') }}" required>
                        </div>

                        <input type="hidden" name="user_id" value="{{auth()->id()}}">
                        <input type="hidden" name="address" id="address" value="">

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
                                        <h3>@lang('Deposit Details')</h3>
                                        <img id="qrcode" src="" class="" alt="">
                                    </div>
                                    <div class="text-center mt-2">
                                        <span id="qrdetails" class="ms-2 check"></span>
                                    </div>

                                    <div class="form-group mt-3" id="otp_body">
                                        <label class="form-label required">{{__('OTP Code')}}</label>
                                        <input name="otp_code" id="otp_code" class="form-control" placeholder="{{__('OTP Code')}}" type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" value="{{ old('opt_code') }}" required>
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
              $('#address').val(res);
              $('#qrdetails').text(res);
              $('#qrcode').attr('src', `https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=${res}&choe=UTF-8`)
          })
      })
      $('#submit').on('click', function() {
        if (($('#currency_id').val().length != 0) && ($('#amount').val().length != 0)) {
            var verify = "{{$user->paymentCheck('Crypto Incoming')}}";
            event.preventDefault();
            if (verify) {
                var url = "{{url('user/sendotp')}}";
                $.get(url,function (res) {
                    console.log(res)
                    if(res=='success') {
                        $('#modal-success').modal('show');
                    }
                    else {
                        toastr.options = { "closeButton" : true, "progressBar" : true }
                        toastr.error('The OTP code can not be sent to you.');
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
