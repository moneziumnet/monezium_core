@extends('layouts.user')
{{--
@section('title')
   @lang('Exchange Money')
@endsection

@section('breadcrumb')
   @lang('Exchange Money')
@endsection --}}

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <div class="page-pretitle">
            {{__('Overview')}}
          </div>
          <h2 class="page-title">
            {{__('Exchange Money')}}
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
                                $wallet_type_list = array('0'=>'All', '1'=>'Current', '2'=>'Card', '3'=>'Deposit', '4'=>'Loan', '5'=>'Escrow');
                                $modules = explode(" , ", auth()->user()->modules);
                                if (in_array('Crypto',$modules)) {
                                    $wallet_type_list['8'] = 'Crypto';
                                }
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
                                <select class="form-select from shadow-none" name="from_wallet_id" id="from_wallet_id">
                                    <option value="" selected>@lang('Select')</option>
                                    @foreach ($wallets as $wallet)
                                    @if (isset($wallet_type_list[$wallet->wallet_type]))
                                    @php
                                        $code = $wallet->currency->code;
                                        if($wallet->currency->type == 2) {
                                            $amount = amount(Crypto_Balance($wallet->user_id, $wallet->currency_id), 2);
                                            $amount_fiat = $amount/($rate->data->rates->$code);
                                            $amount = $amount.'('.$amount_fiat.$currency->code.')';
                                        }
                                        else {
                                            $amount = amount($wallet->balance,$wallet->currency->type,2);
                                        }
                                    @endphp
                                    @if ($amount > 0)
                                    <option value="{{$wallet->id}}" data-curr="{{$wallet->currency->id}}" data-rate="{{$rate->data->rates->$code ?? $wallet->currency->rate}}" data-code="{{$wallet->currency->code}}" data-type="{{$wallet->currency->type}}">{{$wallet->currency->code}} -- ({{ $amount}}) --  {{$wallet_type_list[$wallet->wallet_type]}} </option>
                                    @endif
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
                                <select class="form-select wallet" name="wallet_type" id="wallet_type" disabled>
                                    @foreach ($wallet_type_list as $key=>$wallet)
                                    <option value="{{$key}}" >{{$wallet}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-label">@lang('To Currency')</div>
                                <select class="form-select to shadow-none" name="to_wallet_id" id="to_wallet_id" disabled>
                                    <option value="" selected>@lang('Select')</option>
                                </select>
                            </div>

                            <div class="col-md-12 mb-3 info d-none">
                                <ul class="list-group mt-2">
                                    <li class="list-group-item d-flex justify-content-between font-weight-bold">@lang('From Currency : ')<span class="fromCurr"></span></li>

                                    <li class="list-group-item d-flex justify-content-between font-weight-bold">@lang('To Currency : ')<span class="toCurr"></span></li>

                                    <li class="list-group-item d-flex justify-content-between font-weight-bold">@lang('Exchange Amount : ')<span class="exAmount"></span></li>


                                    <li class="list-group-item d-flex justify-content-between font-weight-bold">@lang('Exchange Charge : ')<span class="exCharge"></span></li>

                                    <li class="list-group-item d-flex justify-content-between font-weight-bold">@lang('Will get : ')<span class="total_amount"></span></li>
                                </ul>
                            </div>


                            <div class="col-md-12 mb-3">
                                <div class="form-label">&nbsp;</div>
                                <a href="#" class="btn btn-primary exchange w-100">
                                    @lang('Exchange')
                                </a>
                            </div>


                            <div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    <div class="modal-status bg-primary"></div>
                                    <div class="modal-body py-4">
                                        <div class="text-center">

                                            <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                                            <h3>@lang('Are you sure to exchange?')</h3>
                                        </div>
                                        <ul class="list-group mt-2">
                                            <li class="list-group-item d-flex justify-content-between" style="word-break: break-all;">@lang('From Currency')<span style="margin-left: 60px" id="modal_from_currency"></span></li>
                                            <li class="list-group-item d-flex justify-content-between" style="word-break: break-all;">@lang('To Wallet')<span style="margin-left: 60px" id="modal_to_wallet"></span></li>
                                            <li class="list-group-item d-flex justify-content-between" style="word-break: break-all;">@lang('To Currency')<span style="margin-left: 60px" id="modal_to_currency"></span></li>
                                            <li class="list-group-item d-flex justify-content-between" style="word-break: break-all;">@lang('Amount')<span style="margin-left: 60px" id="modal_amount"></span></li>
                                        </ul>

                                        <div class="form-group mt-3 mb-3" id="otp_body">
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
                        </div>
                    </form>
                </div>
                </div>
            <!-- </div> -->
        </div>

        <div class="row row-deck row-cards mt-3">
            <div class="col-md-12 d-flex justify-content-between">
                <h2> @lang('Recent Exchanges')</h2>
                <a href="{{route('user.exchange.history')}}" class="btn btn-primary">@lang('See All')</a>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="table-responsive">
                    <table class="table table-vcenter card-table table-striped">
                        <thead>
                        <tr>
                            <th>@lang('From Currency')</th>
                            <th>@lang('From Amount')</th>
                            <th>@lang('To Currency')</th>
                            <th>@lang('To Amount')</th>
                            <th>@lang('Charge')</th>
                            <th>@lang('Date')</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($recentExchanges as $item)
                            <tr>
                            <td data-label="@lang('From Currency')">{{@$item->fromCurr->code}}</td>
                            <td data-label="@lang('From Amount')">{{amount($item->from_amount,$item->fromCurr->type,2)}} {{@$item->fromCurr->code}}</td>
                            <td data-label="@lang('To Currency')">{{@$item->toCurr->code}}</td>
                            <td data-label="@lang('To Amount')">{{amount($item->to_amount,$item->toCurr->type,2)}} {{@$item->toCurr->code}}</td>
                            <td data-label="@lang('Charge')">{{amount($item->charge,$item->fromCurr->type,2)}} {{$item->fromCurr->code}}</td>
                            <td data-label="@lang('Date')">{{dateFormat($item->created_at)}}</td>
                            </tr>
                            @empty
                            <tr>
                                <td class="text-center" colspan="12">@lang('No data found!')</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
    <script>
        'use strict';
        $('.from').on('change',function () {
            var from = $('.from option:selected');
            var to = $('.to option:selected');

            if(from.data('curr') ==  to.val()){
                $('.info').addClass('d-none')
                return false
            }else{
                if( to.val() != '') $('.info').removeClass('d-none')

            }

            exchange();
            $('.to').attr('disabled',false)
            $('.wallet').attr('disabled',false)
        })

        $('.amount').on('keyup',function () {
            exchange();
        })

        $('.wallet').on('change', function () {
            var wtype = $('.wallet').val();

            let _optionHtml = '<option value="" selected>@lang("Select")</option>';
            let curlist = wtype == '8' ? '{{$crypto_currencies}}' : '{{$currencies}}';
            const obj = curlist.replace(/&quot;/g, '"');
            $.each(JSON.parse(obj), function(key, value) {
                 _optionHtml += '<option value="' + value.id + '" data-rate="' + value.rate + '" data-code="' + value.code + '" data-type="'+ value.type +'">' + value.code + '</option>';
            })
            $('.to').html(_optionHtml);
        })

        function exchange() {
            var from = $('.from option:selected');
            var to = $('.to option:selected');

            var amount = parseFloat($('.amount').val())
            var fromCode = from.data('code')
            var toCode   = to.data('code')
            var fromtype = from.data('type')
            var totype   = to.data('type')
            var fromRate = from.data('rate')
            var toRate =to.data('rate')
            console.log(fromRate)
            console.log(toRate)

            var defaultAmount = amount/fromRate;
            var finalAmount = defaultAmount * toRate;

            var url = "{{url('user/exchange-money/calcharge')}}"+'/'+defaultAmount+'/'+fromtype+'/'+totype;
            $.get(url,function (res) {

                $('.fromCurr').text(fromCode)
                $('.toCurr').text(toCode)
                $('.exAmount').text(amount +' '+ fromCode)
                $('.exCharge').text((parseFloat(res)*fromRate).toFixed(8) +' '+ fromCode)
                $('.total_amount').text(finalAmount.toFixed(8) +' '+ toCode)
            });

        }

        $('.to').on('change',function () {
            var from = $('.from option:selected');
            var to = $('.to option:selected');

            if(from.data('curr') ==  to.val()){
                $('.info').addClass('d-none')
                return false
            }

            exchange();
            if(to.val() != ''){
                $('.info').removeClass('d-none')
            }else{
                $('.info').addClass('d-none')
            }
        })

        $('.exchange').on('click',function () {
            var from = $('.from option:selected');
            var to = $('.to option:selected').val();
            var amount = $('.amount').val()

            var verify = "{{$user->paymentCheck('Exchange')}}";

            $('#modal_from_currency').text($('#from_wallet_id  option:selected').text().split('--')[0])
            $('#modal_to_currency').text($('#to_wallet_id  option:selected').text())
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
                        toastr.options = { "closeButton" : true, "progressBar" : true }
                        toastr.error('The OTP code can not be sent to you.');
                    }
                });
            } else {
                $('#otp_body').remove();
                $('#modal-success').modal('show');
            }

            $('#modal-success').modal('show')
        })

        // $('.confirm').on('click',function () {
        //     $('#form').submit()
        //     $(this).attr('disabled',true)
        // })

    </script>
@endpush
