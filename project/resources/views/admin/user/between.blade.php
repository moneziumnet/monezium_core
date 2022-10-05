@extends('layouts.admin')
@section('content')
    <div class="card">
        <div class="d-sm-flex align-items-center justify-content-between py-3">
            @php
                $accounttype = ['0' => 'All', '1' => 'Current', '2' => 'Card', '3' => 'Deposit', '4' => 'Loan', '5' => 'Escrow', '6' => 'Supervisor', '7' => 'Merchant', '8' => 'Crypto', '9' => 'System', '10' => 'Manager'];
                $dcurr = App\Models\Currency::findOrFail($wallet->currency_id);
            @endphp
            <h5 class="mb-0 text-gray-800 pl-3">
                <strong class="mr-3">{{ $accounttype[$wallet->wallet_type] }} {{ $wallet->wallet_no }}</strong>
                ({{ $dcurr->symbol }} {{ amount($wallet->balance, $dcurr->type, 2) }} {{ $dcurr->code }})
            </h5>
            <ol class="breadcrumb py-0 m-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.user.index') }}">{{ __('User List') }}</a></li>
            </ol>
        </div>
    </div>
    @php
        $userType = explode(',', $data->user_type);
        $supervisor = DB::table('customer_types')->where('type_name', 'Supervisors')->first()->id;
        $merchant = DB::table('customer_types')->where('type_name', 'Merchants')->first()->id;
        $wallet_type_list = array( '1'=>'Current', '2'=>'Card', '3'=>'Deposit', '4'=>'Loan', '5'=>'Escrow', '8'=>'Crypto');
        if(in_array($supervisor, $userType)) {
            $wallet_type_list['6'] = 'Supervisor';
        }
        elseif (DB::table('managers')->where('manager_id', $data->id)->first()) {
            $wallet_type_list['10'] = 'Manager';
        }
        if(in_array($merchant, $userType)) {
            $wallet_type_list['7'] = 'Merchant';
        }
    @endphp

    <div class="row mt-3">
        <div class="col-lg-12">
            <div class="card tab-card">
                @include('admin.user.profiletab')
                <div class="tab-content" id="myTabContent">
                    <h3 class="text-center my-3">Payment between account</h3>
                    @include('includes.admin.form-success')
                    <div class="tab-pane fade show active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
                        <div class="card-body">
                            <form action="{{route('admin-wallet-between-send', [$wallet->user_id, $wallet->id])}}" method="post" id="between_form">
                                @csrf
                                <div class="mx-auto col-md-6 mb-3">
                                    <div class="form-label">@lang('Amount')</div>
                                    <input type="number" step="any" name="amount" id="amount" class="form-control amount shadow-none" required>
                                </div>

                                <div class="mx-auto col-md-6 mb-3">
                                    <div class="form-label">@lang('Currency')</div>
                                    <input type="text" class="form-control shadow-none" value="{{$wallet->currency->code}}" readonly>
                                </div>
    
                                <div class="mx-auto col-md-6 mb-3">
                                    <div class="form-label">@lang('To Wallet')</div>
                                    <select class="form-control wallet" name="wallet_type" id="wallet_type" required>
                                        <option value="" selected>@lang('Select')</option>
                                        @foreach ($wallet_type_list as $key=>$wallet_type)
                                        <option value="{{$key}}" >{{$wallet_type}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <input type="hidden" name="wallet_id" value="{{$wallet->id}}" />

                                <div class="mx-auto col-md-6 mb-3">
                                    <div class="form-label">&nbsp;</div>
                                    <a href="#" class="btn btn-primary exchange w-100">
                                        @lang('Transfer')
                                    </a>
                                </div>
                                <div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="modal-body py-4">
                                                <div class="text-center">
                                                    <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                                                    <h3>@lang('Are you sure to transfer?')</h3>
                                                    <ul class="list-group details-list mt-2">
                                                        <li class="list-group-item">@lang('From Wallet')<span id="modal_from_wallet"></span></li>
                                                        <li class="list-group-item">@lang('To Wallet')<span id="modal_to_wallet"></span></li>
                                                        <li class="list-group-item">@lang('Currency')<span id="modal_currency"></span></li>
                                                        <li class="list-group-item">@lang('Amount')<span id="modal_amount"></span></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <div class="w-100">
                                                    <div class="row">
                                                        <div class="col">
                                                            <a href="javascript:;" class="btn w-100" data-dismiss="modal">@lang('Cancel')</a>
                                                        </div>
                                                        <div class="col">
                                                            <button type="submit" class="btn btn-primary w-100 confirm">@lang('Confirm')</button>
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
    </div>

    <!--Row-->
@endsection
@section('scripts')
    <script type="text/javascript">
        "use strict";
        $('.exchange').on('click',function () {
            $('#modal_from_wallet').text('{{$wallet_type_list[$wallet->wallet_type]}}')
            $('#modal_currency').text('{{$wallet->currency->code}}')
            $('#modal_to_wallet').text($('#wallet_type  option:selected').text())
            $('#modal_amount').text($('#amount').val())
            $('#modal-success').modal('show');
        })
    </script>
@endsection
