@extends('layouts.user')

@push('css')
@endpush

@section('contents')
    <div class="container-xl">
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        {{ __('Payment to own account') }}
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <div class="card p-4">
                        @includeIf('includes.flash')
                        <form action="{{ route('user.merchant.send.money.store') }}" method="POST"
                            enctype="multipart/form-data" id="send_money_form">
                            @csrf
                            <div class="form-group mb-3 mt-3">
                                <label class="form-label required">{{ __('Select Shop') }}</label>
                                <select name="shop_id" value="{{ old('shop_id') }}" id="shop_id" class="form-control" required>
                                    <option value="">Select</option>
                                    @if (!empty($shops))
                                        @foreach ($shops as $shop)
                                            <option value="{{ $shop->shop_id }}">
                                                {{ $shop->shop->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="d-none" id="wallet_list">
                                {{ json_encode($wallets) }}
                            </div>
                            <div class="form-group mb-3 mt-3">
                                <label class="form-label required">{{ __('Select Wallet') }}</label>
                                <select name="wallet_id" value="{{ old('wallet_id') }}" id="wallet_id" class="form-control" required>
                                    <option value="" balance="0">Select</option>
                                </select>
                            </div>
                            <div class="form-group mt-3">
                                <label class="form-label required">{{ __('Amount') }}</label>
                                <input name="amount" id="amount" class="form-control"
                                    placeholder="{{ __('0.0') }}" type="number" step="any"
                                    value="{{ old('amount') }}" min="0.01" required>
                            </div>

                            <div class="form-footer">
                                <button type="submit" class="btn btn-primary w-100">{{ __('Confirm') }}</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('js')
    <script>
        'use strict';
        var wallet_list = JSON.parse($('#wallet_list').html());
        $("#shop_id").on('change', function() {
            var shop_id = $("#shop_id").val();
            var wallets = wallet_list.filter(item => item.shop_id == shop_id);
            var options = wallets.map(item => {
                const balance = (item.balance * 1).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                return `<option value="${item.id}" balance="${item.balance}">
                    ${item.currency.code} -- (${balance})
                </option>`;
            }).join("");
            $('#wallet_id').html(`<option value="" balance="0">Select</option>${options}`);
        });
    </script>
@endpush
