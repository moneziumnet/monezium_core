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
        $userType   = explode(',', $data->user_type);
        $supervisor = DB::table('customer_types')->where('type_name', 'Supervisors')->first()->id;
        $merchant   = DB::table('customer_types')->where('type_name', 'Merchants')->first()->id;
        $wallet_type_list = [
            '1' => 'Current',
            '2' => 'Card',
            '3' => 'Deposit',
            '4' => 'Loan',
            '5' => 'Escrow',
            '8' => 'Crypto'
        ];
        if (in_array($supervisor, $userType)) {
            $wallet_type_list['6'] = 'Supervisor';
        } elseif ( DB::table('managers')->where('manager_id', $data->id)->first()) {
            $wallet_type_list['10'] = 'Manager';
        }

        if (in_array($merchant, $userType)) {
            $wallet_type_list['7'] = 'Merchant';
        }
    @endphp

    <div class="row mt-3">
        <div class="col-lg-12">
            <div class="card tab-card">
                @include('admin.user.profiletab')
                <div class="tab-content" id="myTabContent">
                    <h3 class="text-center my-3">External Payment</h3>
                    @include('includes.admin.form-success')
                    <div class="tab-pane fade show active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
                        <div class="card-body col-md-6 mx-auto">
                            <form action="{{ route('admin-wallet-external-send', [$wallet->user_id, $wallet->id]) }}"
                                method="post" id="between_form" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group mt-3">
                                    <label class="form-label required">{{ __('Beneficiary') }}</label>
                                    <select name="beneficiary_id" id="beneficiary_id" class="form-control" required>
                                        <option value="">{{ __('Select Beneficiary') }}</option>
                                        @foreach ($beneficiaries as $beneficiary)
                                            <option value="{{ $beneficiary->id }}" data="{{ json_encode($beneficiary) }}">
                                                {{ $beneficiary->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mt-3">
                                    <label class="form-label required">{{ __('Bank Account') }}</label>
                                    <select name="subbank" id="subbank" class="form-control" required>
                                        <option value="">{{ __('Select Bank Account') }}</option>
                                        @foreach ($banks as $item)
                                            <option value="{{ $item->id }}">
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mt-3">
                                    <label class="form-label required">{{ __('Currency') }}</label>
                                    <input type="text" pattern="[^()/><\][\\;!|]+" class="form-control" value="{{ $wallet->currency->code }}"
                                        readonly />
                                </div>
                                <div class="form-group mb-3 mt-3">
                                    <label class="form-label required">{{ __('Amount') }}</label>
                                    <input name="amount" id="amount" class="form-control" autocomplete="off"
                                        placeholder="{{ __('0.0') }}" type="number" step="any"
                                        value="{{ old('amount') }}" min="1" required>
                                </div>
                                <div class="form-group mt-3">
                                    <label class="form-label required">{{ __('Payment Type') }}</label>
                                    <select name="payment_type" id="payment_type" class="form-control" required>
                                        <option value="">{{ __('Select Payment Type') }}</option>
                                        @if ($wallet->currency->code == 'EUR')
                                            <option value="SEPA">{{ __('SEPA') }}</option>
                                            <option value="SEPA_INSTANT">{{ __('SEPA_INSTANT') }}</option>
                                        @endif
                                        <option value="SWIFT">{{ __('SWIFT') }}</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3 mt-3 document-part d-none">
                                    <label class="form-label document" id="document_label">{{ __('Document') }}</label>
                                    <input class="document form-control" name="document" id="document" class="form-control"
                                        autocomplete="off" type="file" accept=".xls,.xlsx,.pdf,.jpg,.png">
                                </div>
                                <div class="form-group mt-3">
                                    <label class="form-label required">{{ __('Description') }}</label>
                                    <textarea name="description" id="description" class="form-control" autocomplete="off"
                                        placeholder="{{ __('Please input description') }}" type="text" required></textarea>
                                </div>
                                <input type="hidden" name="wallet_id" value="{{$wallet->id}}" />
                                <div class="form-footer">
                                    <button id="form_submit"
                                        class="btn btn-primary w-100">{{ __('Submit') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal modal-blur fade" id="modal-verify" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-center">{{ 'Details' }}</h5>
                </div>
                <div class="modal-body">
                    <ul class="list-group mt-2 details-list">
                        <li class="list-group-item">@lang('Beneficiary Name')<span id="beneficiary_name"></span></li>
                        <li class="list-group-item">@lang('Beneficiary Address')<span id="beneficiary_address"></span></li>
                        <li class="list-group-item">@lang('Bank Name')<span id="bank_name"></span></li>
                        <li class="list-group-item">@lang('Bank Address')<span id="bank_address"></span></li>
                        <li class="list-group-item">@lang('SWIFT BIC')<span id="swift"></span></li>
                        <li class="list-group-item">@lang('Account IBAN')<span id="iban"></span></li>
                        <li class="list-group-item">@lang('Amount')<span id="detail_amount"></span></li>
                        <li class="list-group-item">@lang('Description')<span id="detail_description"></span></li>
                    </ul>
                    <button id="submit-btn" class="btn btn-primary col-12 mt-3">{{ __('Submit') }}</button>
                </div>
            </div>
        </div>
    </div>
    <!--Row-->
@endsection
@section('scripts')
    <script type="text/javascript">
        "use strict";
        $('#amount').on('change', function() {
            if ($('#amount').val() >= parseFloat('{{ $other_bank_limit }}')) {
                $('.document-part').removeClass('d-none');
            } else {
                $('.document-part').addClass('d-none');
            }
        })

        $('#form_submit').on('click', function(event) {
            if(!document.getElementById('between_form').checkValidity()){
                return;
            }
            event.preventDefault();
            const attrData = $('#beneficiary_id option:selected').attr('data');
            if (attrData) {
                const data = JSON.parse(attrData);
                $('#beneficiary_name').html(data.name);
                $('#beneficiary_address').html(data.address ?? 'None');
                $('#bank_name').html(data.bank_name ?? 'None');
                $('#bank_address').html(data.bank_address ?? 'None');
                $('#swift').html(data.swift_bic ?? 'None');
                $('#iban').html(data.account_iban ?? 'None');
                $('#detail_amount').html('{{$wallet->currency->symbol}}' + $('#amount').val() + ' {{$wallet->currency->code}}');
                $('#detail_description').html($('#description').val());
            }
            $('#modal-verify').modal('show');
        })
        $('#submit-btn').on('click', function(){
            $("#between_form").submit();
        })
    </script>
@endsection
