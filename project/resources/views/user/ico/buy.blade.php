@extends('layouts.user')

@push('css')
@endpush

@section('contents')
    <div class="container-fluid">
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        {{ __('Overview') }}
                    </div>
                    <h2 class="page-title">
                        {{ __('Buy ICO Token') }}
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">

                        <a href="{{ route('user.ico') }}" class="btn btn-primary d-sm-inline-block">
                            <i class="fas fa-backward me-1"></i> {{ __('ICO Token List') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-fluid">
            <div class="row row-cards">
                <div class="card p-5">
                    <div class="row align-items-center">
                        <div class="col-md-5 mb-3">
                            <h2 class="text-center mb-3">Details</h2>
                            <li class="list-group-item d-flex justify-content-between">
                                @lang('Name')<span>{{ $ico_token->name }}</span></li>
                            <li class="list-group-item d-flex justify-content-between">
                                @lang('Price')<span>{{ amount($ico_token->price, 1, 2) }} USD</span></li>
                            <li class="list-group-item d-flex justify-content-between">@lang('Code')<span
                                    class="badge badge-primary">{{ $ico_token->currency->code }}</span></li>
                            <li class="list-group-item d-flex justify-content-between">
                                @lang('Symbol')<span>{{ $ico_token->currency->symbol }}</span></li>
                            @php
                                $current_amount = $ico_token->total_supply - $ico_token->balance;
                            @endphp
                            <li class="list-group-item d-flex justify-content-between">
                                @lang('Available Amount')<span>{{ $current_amount }}</span></li>
                        </div>

                        <div class="col-md-6 offset-md-1 mb-3">
                            <form id="shop-form" action="{{ route('user.ico.buy', $ico_token->id) }}" method="POST">
                                @csrf
                                <div class="form-group mb-3">
                                    <div class="form-label">@lang('Select Wallet')</div>
                                    <select class="form-select from shadow-none" name="from_wallet_id" id="from_wallet_id"
                                        required>
                                        <option value="" selected>@lang('Select')</option>
                                        @foreach ($wallets as $wallet)
                                            <option value="{{ $wallet->id }}" data-curr="{{ $wallet->currency->id }}"
                                                data-rate="{{ getRate($wallet->currency) }}"
                                                data-code="{{ $wallet->currency->code }}"
                                                data-type="{{ $wallet->currency->type }}">
                                                {{ $wallet->currency->code }} --
                                                ({{ amount($wallet->balance, $wallet->currency->type, 2) }})
                                                -- Current
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <div class="form-label">@lang('Amount')</div>
                                    <input type="number" step="any" name="amount" id="amount"
                                        class="form-control amount shadow-none" min="1" max="{{ $current_amount }}"
                                        required>
                                </div>
                                <div class="total-price mt-3"></div>
                                <div class="form-footer">
                                    <button type="submit"
                                        class="btn btn-primary submit-btn w-100">{{ __('Buy Token') }}</button>
                                </div>
                            </form>
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

        $('#amount').on('keyup', function() {
            getTotal();
        });

        $('.from').on('change', function() {
            getTotal();
        });

        function getTotal() {
            var currency = $('.from option:selected');

            if (!currency.data('rate')) {
                $('.total-price').html('');
            } else {
                var total_price = currency.data('rate') * $('#amount').val() * {{ $ico_token->price }};
                $('.total-price').html('Total = ' + currency_format(total_price) + ' ' + currency.data('code'));
            }
        }

        function currency_format(val) {
            return (val).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }
    </script>
@endpush
