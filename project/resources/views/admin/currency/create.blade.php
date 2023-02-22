@extends('layouts.admin')

@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Add New Currency') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.currency.index')}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="javascript:;">{{ __('Payment Settings') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.currency.index') }}">{{ __('Currencies') }}</a></li>
      <li class="breadcrumb-item"><a href="{{route('admin.currency.create')}}">{{ __('Add New Currency') }}</a></li>
    </ol>
  </div>
</div>

<div class="row justify-content-center mt-3">
  <div class="col-md-10">
    <!-- Form Basic -->
    <div class="card mb-4">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">{{ __('Add New Currency Form') }}</h6>
      </div>

      <div class="card-body">

        <form class="geniusform" action="{{route('admin.currency.store')}}" method="POST" enctype="multipart/form-data">

          @include('includes.admin.form-both')

          {{ csrf_field() }}

          <div class="form-group">
            <label for="c-name">{{ __('Currency Name') }}</label>
            <input type="text" pattern="[^()/><\][\\;&$@!|]+" class="form-control" name="curr_name" placeholder="{{ __('Enter Currency Name') }}" required="" value="">
          </div>

          <div class="form-group">
            <label for="inp-code">{{ __('Currency Code') }}</label>
            <input type="text" pattern="[^()/><\][\\;&$@!|]+" class="form-control" id="inp-code" name="code" placeholder="{{ __('Enter Currency code') }}" required="" value="">
          </div>

          <div class="form-group">
            <label for="inp-symbol">{{ __('Currency Symbol') }}</label>
            <input type="text" pattern="[^()/><\][\\;&$@!|]+" class="form-control" id="inp-symbol" name="symbol" placeholder="{{ __('Enter Currency symbol') }}" required="" value="">
          </div>

          <div class="form-group">
            <label for="inp-rate">{{ __('Rate') }}</label>
            <input type="number" step="any" class="form-control" id="inp-rate" name="rate" placeholder="{{ __('Enter Currency Rate') }}" required="" value="">
          </div>

          <div class="form-group">
            <label>@lang('Currency Type')</label>
            <select class="form-control" name="type" required>
              <option value="" selected>--@lang('Select Type')--</option>
              <option value="1">@lang('FIAT')</option>
              <option value="2">@lang('CRYPTO')</option>
            </select>
          </div>

          <!-- <div class="form-group">
            <label>@lang('Set As Default') </label>
            <select class="form-control" name="is_default" required>
              <option value="" selected>--@lang('Select')--</option>
              <option value="1">@lang('Yes')</option>
              <option value="0">@lang('No')</option>
            </select>
          </div> -->

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
