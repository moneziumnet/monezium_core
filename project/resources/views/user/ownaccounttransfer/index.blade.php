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
            {{__('Own Money Transfer')}}
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
                                $wallet_type_list = array('0'=>'All', '1'=>'Current', '2'=>'Card', '3'=>'Deposit', '4'=>'Loan', '5'=>'Escrow', '8'=>'Crypto');
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
                                <select class="form-select from shadow-none" name="from_wallet_id">
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
                                <input type="text" name="amount" class="form-control amount shadow-none" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-label">@lang('To Wallet')</div>
                                <select class="form-select wallet" name="wallet_type" >
                                    @foreach ($wallet_type_list as $key=>$wallet)
                                    <option value="{{$key}}" >{{$wallet}}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- <div class="col-md-6 mb-3">
                                <div class="form-label">@lang('To Currency')</div>
                                <select class="form-select to shadow-none" name="to_wallet_id" >
                                    <option value="" selected>@lang('Select')</option>
                                    @foreach ($currencies as $curr)
                                    <option value="{{$curr->id}}" data-rate="{{$curr->rate}}" data-code="{{$curr->code}}"  data-type="{{$curr->type}}">{{$curr->code}}</option>
                                    @endforeach
                                </select>
                            </div> --}}

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
                                    <div class="modal-body text-center py-4">
                                        <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                                        <h3>@lang('Are you sure to transfer?')</h3>
                                    </div>
                                    <div class="modal-footer">
                                        <div class="w-100">
                                            <div class="row">
                                            <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">
                                                @lang('Cancel')
                                                </a></div>
                                            <div class="col">
                                                <button type="button" class="btn btn-primary w-100 confirm">
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
            $('#modal-success').modal('show')
        })

        $('.confirm').on('click',function () {
            $('#form').submit()
            $(this).attr('disabled',true)
        })

    </script>
@endpush
