@extends('layouts.admin')

@section('content')

<div class="card">
    <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('View Contract') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.aoa.index', $data->contract_id)}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
    <ol class="breadcrumb m-0 py-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.contract.management', $data->contract->user_id) }}">{{ __('Contracts') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.aoa.index', $data->contract_id) }}">{{ __('AoA') }}</a></li>
    </ol>
    </div>
</div>

    <div class="card mb-4 mt-3">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">{{ __('View Contract') }}</h6>
      </div>

      <div class="card-body">

            @include('includes.admin.form-both')

            {{ csrf_field() }}


            <div class="text-center">
                <h1>
                    {{__($data->title)}}
                </h1>
            </div>
            <div class="mt-5 mb-3">
                @foreach ($information as $title => $text)
                    <h2 class="ms-1">{{ $title }}</h2>
                    <p>{!!nl2br($text)!!}</p>
                @endforeach
            </div>

      </div>
    </div>


@endsection
@section('scripts')
<script type="text/javascript">
  'use strict';

</script>
@endsection

