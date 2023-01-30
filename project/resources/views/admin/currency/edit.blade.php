@extends('layouts.admin')

@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Edit Currency') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.currency.index')}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="javascript:;">{{ __('Payment Settings') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.currency.index') }}">{{ __('Currencies') }}</a></li>
      <li class="breadcrumb-item"><a href="{{route('admin.currency.edit',$data->id)}}">{{ __('Edit Currency') }}</a></li>
    </ol>
  </div>
</div>

<div class="row justify-content-center mt-3">
  <div class="col-md-10">
    <div class="card mb-4">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">{{ __('Edit Currency Form') }}</h6>
      </div>

      <div class="card-body">

        <form class="geniusform" action="{{route('admin.currency.update',$data->id)}}" method="POST" enctype="multipart/form-data">

          @include('includes.admin.form-both')

          {{ csrf_field() }}

          <div class="form-group">
            <label for="c-name">{{ __('Currency Name') }}</label>
            <input type="text" pattern="[^()/><\][\\;!|]+" class="form-control" name="curr_name" placeholder="{{ __('Enter Currency Name') }}" required="" value="{{ $data->curr_name }}">
          </div>

          <div class="form-group">
            <label for="inp-code">{{ __('Currency Code') }}</label>
            <input type="text" pattern="[^()/><\][\\;!|]+" class="form-control" id="inp-code" name="code" placeholder="{{ __('Enter Currency Code') }}" required="" value="{{ $data->code }}">
          </div>

          <div class="form-group">
            <label for="inp-symbol">{{ __('Symbol') }}</label>
            <input type="text" pattern="[^()/><\][\\;!|]+" class="form-control" id="inp-symbol" name="symbol" placeholder="{{ __('Enter Currency Symbol') }}" required="" value="{{ $data->symbol }}">
          </div>

          <div class="form-group">
            <label for="inp-rate">{{ __('Rate') }}</label>
            <input type="number" step="any" class="form-control" id="inp-rate" name="rate" placeholder="{{ __('Enter Currency Rate 0') }}" required="" value="{{ numFormat($data->rate,8) }}">
          </div>

          <div class="form-group">
            <label>@lang('Currency Type')</label>
            <select class="form-control" name="type" required>
              <option value="">--@lang('Select Type')--</option>
              <option value="1" {{$data->type == 1 ? 'selected':''}}>@lang('FIAT')</option>
              <option value="2" {{$data->type == 2 ? 'selected':''}}>@lang('CRYPTO')</option>
            </select>
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
