@extends('layouts.admin')
@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ $data->name }}</h5>
    <ol class="breadcrumb py-0 m-0">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.user.index') }}">{{ __('User List') }}</a></li>
    </ol>
  </div>
</div>


<div class="row mt-3">
  <div class="col-lg-12">
    <div class="card mt-1 tab-card">
      @include('admin.user.profiletab')

      <div class="tab-content" id="myTabContent">
        @php
        $currency = defaultCurr();
        @endphp
        @include('includes.admin.form-success')
        <div class="tab-pane fade show p-3 active" id="accounts" role="tabpanel" aria-labelledby="accounts-tab">

          <div class="card-body">
            <div class="card-header">
              <h4>@lang('Your Wallets')</h4>
            </div>

            <div class="row mb-3">
              @foreach ($wallets as $item)
              <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1"> {{$item->currency->curr_name}}</div>
                        <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">{{amount($item->balance,$item->currency->type,2)}} {{$item->currency->code}} ({{$item->currency->symbol}}) </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              @endforeach
            </div>
            <div class="card-header">
              <h5>@lang('Transaction Type')</h5>
              <h6>@lang('Deposit, Internal, Withdrawal')</h6>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
<!--Row-->
@endsection