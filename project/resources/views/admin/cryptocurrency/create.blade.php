@extends('layouts.admin')

@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Add New Crypto Currency') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.crypto.currency.index')}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.crypto.currency.index') }}">{{ __('Crypto Currencies') }}</a></li>
      <li class="breadcrumb-item"><a href="{{route('admin.crypto.currency.create')}}">{{ __('Add New Crypto Currency') }}</a></li>
    </ol>
  </div>
</div>

<div class="row justify-content-center mt-3">
  <div class="col-md-10">
    <!-- Form Basic -->
    <div class="card mb-4">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">{{ __('New Crypto Currency Form') }}</h6>
      </div>

      <div class="card-body">

        <form class="geniusform" action="{{route('admin.crypto.currency.store')}}" method="POST" enctype="multipart/form-data">

          @include('includes.admin.form-both')

          {{ csrf_field() }}

          <div class="form-group">
            <label for="c-name">{{ __('Name') }}</label>
            <input type="text" pattern="[^À-ž()/><\][\\\-;&$@!|]+" class="form-control" name="curr_name" placeholder="{{ __('Enter Currency Name') }}" required="" value="">
          </div>

          <div class="form-group">
            <label for="address">{{ __('Address') }}</label>
            <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" name="address" placeholder="{{ __('Enter Crypto Address') }}" value="">
          </div>

          <div class="form-group">
            <label for="network">{{ __('Network') }}</label>
            <select class="form-control" name="network" required>
              <option value="" selected>--@lang('Select Crypto Network')--</option>
              <option value="Ether">@lang('Ethereum')</option>
              <option value="Btc">@lang('BitCoin')</option>
              <option value="Tron">@lang('Tron')</option>
            </select>
          </div>

          <div class="form-group">
            <label for="inp-code">{{ __('Code') }}</label>
            <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="inp-code" name="code" placeholder="{{ __('Enter Currency code') }}" required="" value="">
          </div>

          <div class="form-group">
            <label for="inp-symbol">{{ __('Symbol') }}</label>
            <input type="text" class="form-control" id="inp-symbol" name="symbol" placeholder="{{ __('Enter Currency symbol') }}" required="" value="">
          </div>

          <div class="form-group">
            <label for="inp-decimal">{{ __('Decimals') }}</label>
            <input type="number"  class="form-control" id="inp-decimal" name="cryptodecimal" placeholder="{{ __('Enter Currency Decimal') }}" required value="18">
          </div>

          <div class="form-group">
            <label for="inp-rate">{{ __('Rate') }}</label>
            <input type="number" step="any" class="form-control" id="inp-rate" name="rate" placeholder="{{ __('Enter Currency Rate') }}" required="" value="">
          </div>


          <div class="form-group">
            <label>@lang('Status') </label>
            <select class="form-control" name="status" required>
              <option value="" selected>--@lang('Select')--</option>
              <option value="1">@lang('Active')</option>
              <option value="0">@lang('Inactive')</option>
            </select>
          </div>

          <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>

        </form>
      </div>
    </div>
  </div>

</div>

@endsection
