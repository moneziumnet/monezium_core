@extends('layouts.user')

@section('contents')

<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="d-flex align-items-end card-header tab-card-header">
            <div class="me-auto">
                <div class="page-pretitle">
                    {{ __('Overview') }}
                </div>
                <h2 class="page-title">
                    {{ __('Merchant Setting') }}
                </h2>
            </div>
            <div class="">
                <ul class="nav nav-pills card-header-tabs" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ $tab == 'paypal' ? 'active' : '' }}" href="{{ route('user.merchant.setting') }}" role="button">Paypal</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $tab == 'stripe' ? 'active' : '' }}" href="{{ route('user.merchant.setting', 'stripe') }}"
                            role="button">Stripe</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="container-xl mt-3 mb-3">
    <div class="row row-deck row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="" id="form" method="post">
                        @csrf
                        @if($tab == 'paypal')
                            <div class="row form-group mt-3">
                                <div class="col-md-6 mx-auto">
                                    <div class="form-label">{{__( 'Client Id')}}</div>
                                    <input type="text" pattern="[^()/><\][;!|]+" name="client_id" class="form-control shadow-none" value="{{$setting->information['client_id'] ?? ''}}" required>
                                </div>
                            </div>
                            <div class="row form-group mt-3">
                                <div class="col-md-6 mx-auto">
                                    <div class="form-label">{{__( 'Client Secret')}}</div>
                                    <input type="text" name="client_secret" class="form-control shadow-none" value="{{$setting->information['client_secret'] ?? ''}}" required>
                                </div>
                            </div>
                            <div class="row form-group mt-3">
                                <div class="col-md-6 mx-auto">
                                    <div class="form-check">
                                        <input type="checkbox" name="sandbox_check" id="sandbox_check" class="form-check-input" {{$setting && $setting->information['sandbox_check'] == 1 ? 'checked' : ''}}>
                                        <label class="form-check-label" for="sandbox_check">{{__( 'Sandbox Check')}}</label>
                                    </div>
                                </div>
                            </div>
                        @elseif ($tab == 'stripe')
                            <div class="row form-group mt-3">
                                <div class="col-md-6 mx-auto">
                                    <div class="form-label">{{__( 'Key')}}</div>
                                    <input type="text" name="key" class="form-control shadow-none" value="{{$setting->information['key'] ?? ''}}" required>
                                </div>
                            </div>
                            <div class="row form-group mt-3">
                                <div class="col-md-6 mx-auto">
                                    <div class="form-label">{{__( 'Secret')}}</div>
                                    <input type="text" name="secret" class="form-control shadow-none" value="{{$setting->information['secret'] ?? ''}}" required>
                                </div>
                            </div>
                        @endif
                        <div class="row mb-3">
                            <div class="col-md-6 form-footer mx-auto">
                                <button type="submit" class="btn btn-primary submit-btn w-100" >{{__('Confirm')}}</button>
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
    <script>
        'use strict';
    </script>
@endpush
