@extends('layouts.user')

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <div class="page-pretitle">
            {{__('Overview')}}
          </div>
          <h2 class="page-title">
            {{__('Payment between accounts')}}
          </h2>
        </div>
      </div>
    </div>
</div>


<div class="page-body">
    <div class="container-xl">
        <div class="row row-deck row-cards">
            <!-- <div class="col-12"> -->
                @includeIf('includes.flash')
                <div class="card">
                <div class="card-body">
                    <form action="" id="form" method="post">
                    @csrf
                        <div class="row">
                            @php
                                $userType = explode(',', auth()->user()->user_type);
                                $supervisor = DB::table('customer_types')->where('type_name', 'Supervisors')->first()->id;
                                $merchant = DB::table('customer_types')->where('type_name', 'Merchants')->first()->id;
                                $wallet_type_list = array( '1'=>'Current', '2'=>'Card', '3'=>'Deposit', '4'=>'Loan', '5'=>'Escrow', '8'=>'Crypto');
                                if(in_array($supervisor, $userType)) {
                                    $wallet_type_list['6'] = 'Supervisor';
                                }
                                elseif (DB::table('managers')->where('manager_id', auth()->id())->first()) {
                                    $wallet_type_list['10'] = 'Manager';
                                }
                                if(in_array($merchant, $userType)) {
                                    $wallet_type_list['7'] = 'Merchant';
                                }
                                @endphp
                            <div class="col-md-6 mb-3">
                                <div class="form-label">@lang('From Currency')</div>
                                <select class="form-select from shadow-none" name="from_wallet_id" id="from_wallet_id" required>
                                    <option value="" selected>@lang('Select')</option>
                                    @foreach ($wallets as $wallet)
                                    @if (isset($wallet_type_list[$wallet->wallet_type]))
                                    <option value="{{$wallet->id}}" data-curr="{{$wallet->currency->id}}" data-rate="{{$wallet->currency->rate}}" data-code="{{$wallet->currency->code}}" data-type="{{$wallet->currency->type}}">{{$wallet->currency->code}} -- ({{amount($wallet->balance,$wallet->currency->type,2)}}) --  {{$wallet_type_list[$wallet->wallet_type]}}</option>
                                    @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-label">@lang('Amount')</div>
                                <input type="number" step="any" name="amount" id="amount" class="form-control amount shadow-none" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-label">@lang('To Wallet')</div>
                                <select class="form-select wallet" name="wallet_type" id="wallet_type" required>
                                    <option value="" selected>@lang('Select')</option>
                                    @foreach ($wallet_type_list as $key=>$wallet)
                                    <option value="{{$key}}" >{{$wallet}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12 mb-3">
                                <div class="form-label">&nbsp;</div>
                                <a href="#" class="btn btn-primary exchange w-100">
                                    @lang('Transfer')
                                </a>
                            </div>


                            <div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    <div class="modal-status bg-primary"></div>
                                    <div class="modal-body py-4">
                                        <div class="text-center">
                                            <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                                            <h3>@lang('Are you sure to transfer?')</h3>
                                            <ul class="list-group mt-2">
                                                <li class="list-group-item d-flex justify-content-between" style="word-break: break-all;">@lang('From Wallet')<span style="margin-left: 60px" id="modal_from_wallet"></span></li>
                                                <li class="list-group-item d-flex justify-content-between" style="word-break: break-all;">@lang('To Wallet')<span style="margin-left: 60px" id="modal_to_wallet"></span></li>
                                                <li class="list-group-item d-flex justify-content-between" style="word-break: break-all;">@lang('Currency')<span style="margin-left: 60px" id="modal_currency"></span></li>
                                                <li class="list-group-item d-flex justify-content-between" style="word-break: break-all;">@lang('Amount')<span style="margin-left: 60px" id="modal_amount"></span></li>
                                            </ul>
                                        </div>

                                        <div class="form-group mt-2" id="otp_body">
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
                        </div>
                    </form>
                </div>
                </div>
            <!-- </div> -->
        </div>

    </div>
</div>
@endsection
@push('js')
    <script>
        'use strict';

        $('.exchange').on('click',function () {
            var verify = "{{$user->paymentCheck('Payment between accounts')}}";

            $('#modal_from_wallet').text($('#from_wallet_id  option:selected').text().split('--')[2])
            $('#modal_currency').text($('#from_wallet_id  option:selected').text().split('--')[0])
            $('#modal_to_wallet').text($('#wallet_type  option:selected').text())
            $('#modal_amount').text($('#amount').val())
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
             $('#modal-success').modal('show');
        })

        // $('.confirm').on('click',function () {
        //     $('#form').submit()
        //     $(this).attr('disabled',true)
        // })

    </script>
@endpush
