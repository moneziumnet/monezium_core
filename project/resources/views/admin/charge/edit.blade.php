@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="d-sm-flex align-items-center justify-content-between">
      <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Edit Charge') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.manage.charge', $charge->plan_id)}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{route('admin.bank.plan.index')}}">{{ __('Pricing Plan') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.manage.charge', $charge->plan_id) }}">{{ __('Manage Charges') }}</a></li>
      </ol>
    </div>
  </div>

    <div class="row justify-content-center mt-3">
        <div class="col-md-10">
            <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Edit Charge Form') }}</h6>
                  </div>

                <div class="card-body">
                    <form action="{{route('admin.update.charge',$charge->id)}}" method="post">
                        @csrf
                        @if($charge->data)
                        @foreach ($charge->data as $key => $value)
                            <div class="form-group">
                              <label for="">{{ucwords(str_replace('_',' ',$key))}} 
                                @if ($key == 'percent_charge' || $key == 'commission')
                                  (%)
                                @endif 
                                <span class="text-danger">*</span></label>
                              <input type="text" name="{{$key}}" class="form-control" value="{{@$value}}">
                            </div>
                        @endforeach
                            @if ($charge->name == 'Transfer Money')
                            <code>(@lang('Put 0 for no limit'))</code>
                            @endif
                        @endif
                        {{-- @if (access('update charge')) --}}
                        <div class="form-group text-right">
                            {{-- <button class="btn btn-primary btn-lg">@lang('Update')</button> --}}
                            <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Update') }}</button>
                        </div>
                        {{-- @endif --}}
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection