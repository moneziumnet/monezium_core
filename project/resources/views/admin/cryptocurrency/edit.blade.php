@extends('layouts.admin')

@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Edit Crypto Currency') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.crypto.currency.index')}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.crypto.currency.index') }}">{{ __('Crypto Currencies') }}</a></li>
      <li class="breadcrumb-item"><a href="{{route('admin.crypto.currency.edit',$data->id)}}">{{ __('Edit Currency') }}</a></li>
    </ol>
  </div>
</div>

<div class="row justify-content-center mt-3">
  <div class="col-md-10">
    <div class="card mb-4">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">{{ __('Edit Crypto Currency Form') }}</h6>
      </div>

      <div class="card-body">

        <form class="geniusform" action="{{route('admin.crypto.currency.update',$data->id)}}" method="POST" enctype="multipart/form-data">

          @include('includes.admin.form-both')

          {{ csrf_field() }}

          <div class="form-group">
            <label for="c-name">{{ __('Currency Name') }}</label>
            <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" name="curr_name" placeholder="{{ __('Enter Currency Name') }}" required="" value="{{ $data->curr_name }}">
          </div>

          <div class="form-group">
            <label for="address">{{ __('Address') }}</label>
            <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" name="address" placeholder="{{ __('Enter Crypto Address') }}" required="" value="{{ $data->address }}">
          </div>

          <div class="form-group">
            <label for="inp-code">{{ __('Currency Code') }}</label>
            <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="inp-code" name="code" placeholder="{{ __('Enter Currency Code') }}" required="" value="{{ $data->code }}">
          </div>

          <div class="form-group">
            <label for="inp-symbol">{{ __('Symbol') }}</label>
            <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="inp-symbol" name="symbol" placeholder="{{ __('Enter Currency Symbol') }}" required="" value="{{ $data->symbol }}">
          </div>

          <div class="form-group">
            <label for="inp-decimal">{{ __('Decimals') }}</label>
            <input type="number" class="form-control" id="inp-decimal" name="cryptodecimal" placeholder="{{ __('Enter Currency Decimal') }}" required="" value="{{ $data->cryptodecimal }}">
          </div>

          <div class="form-group">
            <label for="inp-rate">{{ __('Rate') }}</label>
            <input type="number" step="any" class="form-control" id="inp-rate" name="rate" placeholder="{{ __('Enter Currency Rate 0') }}" required="" value="{{ numFormat($data->rate,8) }}">
          </div>

          <div class="form-group">
            <label>@lang('Status') </label>
            <select class="form-control" name="status" required>
              <option value="">--@lang('Select')--</option>
              <option value="1" {{$data->status == 1 ? 'selected':''}}>@lang('Active')</option>
              <option value="0" {{$data->status == 0 ? 'selected':''}}>@lang('Inactive')</option>
            </select>
          </div>


          <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>

        </form>
      </div>
    </div>
  </div>

</div>
@endsection
