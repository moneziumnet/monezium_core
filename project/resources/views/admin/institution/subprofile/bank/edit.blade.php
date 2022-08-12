@extends('layouts.admin')

@section('content')
<div class="card">
  <div class="d-sm-flex align-items-center py-3 justify-content-between">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Edit Bank') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.subinstitution.banks',$data->ins_id)}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
    <ol class="breadcrumb py-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.subinstitution.index') }}">{{ __('Sub Institutions List') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.subinstitution.banks',$data->ins_id)}}">{{ __('Banks List') }}</a></li>
    </ol>
  </div>
</div>

<div class="row justify-content-center mt-3">
<div class="col-md-10">
  <div class="card mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
      <h6 class="m-0 font-weight-bold text-primary">{{ __('Edit Bank Form') }}</h6>
    </div>

    <div class="card-body">
      <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
      <form class="geniusform" action="{{route('admin.subinstitution.banks.update',$data->id)}}" method="POST" enctype="multipart/form-data">

          @include('includes.admin.form-both')

          {{ csrf_field() }}

          <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                    <label for="name">{{ __('Bank Name') }}</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="{{ __('Enter Bank Name') }}" value="{{$data->name}}" required>
                  </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                    <label for="address">{{ __('Bank Address') }}</label>
                    <input type="text" class="form-control" id="address" name="address" placeholder="{{ __('Bank Address') }}" min="1" value="{{$data->address}}" required>
                  </div>
              </div>

          </div>
          <hr>

          @if($bank_gateway)
          <h6 class="m-0 font-weight-bold text-primary mb-3">{{ __('Gateway Details') }} ( {{$bank_gateway->name}} )</h6>
          <div class="row">
            @foreach($bank_gateway->information as $key => $value)
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="{{strval($key)}}">{{ strval($key) }}</label>
                        <input type="text" class="form-control" id="{{$key}}" name="key[{{strval($key)}}]" placeholder="{{ __('Please input correct value') }}" value="{{$value}}" required>
                    </div>
                </div>
            @endforeach
          </div>
          @endif

          <div class="row d-flex justify-content-center">
              <button type="submit" id="submit-btn" class="btn btn-primary w-100 mt-3">{{ __('Submit') }}</button>
          </div>

      </form>
    </div>
  </div>
</div>

</div>

@endsection


