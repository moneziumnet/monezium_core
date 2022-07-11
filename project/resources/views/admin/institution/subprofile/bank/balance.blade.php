@extends('layouts.admin')

@section('content')
<div class="card">
  <div class="d-sm-flex align-items-center py-3 justify-content-between">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Bank\'s balance') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.subinstitution.banks',$data->ins_id)}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
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
      <h6 class="m-0 font-weight-bold text-primary">{{ __('Show balance Form') }}</h6>
    </div>

    <div class="card-body">
    <div class="card-body">
            <div class="card-header">
              <h4>@lang('Bank'): {{$data->name}}, {{$data->iban}}</h4>
            </div>

            <div class="row mb-3">
            @if (count($bank_balance) == 0)
					  <div class="col-12 text-center">
							<h5 class="m-0">{{__('No Balance Found')}}</h5>
					  </div>
				    @else
              @foreach ($bank_balance as $item)
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
            @endif
            </div>
          </div>

    </div>
  </div>
</div>

</div>

@endsection

@section('scripts')
<script type="text/javascript">
  'use strict';
</script>
@endsection