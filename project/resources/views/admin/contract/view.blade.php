@extends('layouts.admin')

@section('content')

<div class="card">
    <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('View Contract') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.contract.management')}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
    <ol class="breadcrumb m-0 py-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.contract.management') }}">{{ __('Contracts') }}</a></li>
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


              <div class="form-group">
                <label for="inp-title">{{ __('Title') }}</label>
                <input type="text" class="form-control" id="inp-title" name="title"  placeholder="{{ __('Enter Title') }}" value="{{$data->title}}" readonly>
              </div>

              <div class="form-group">
                <label for="inp-details">{{ __('Description') }}</label>
                <textarea name="description" class="form-control summernote" id="inp-details" cols="30" rows="10" placeholder="{{__('Description')}}" readonly>{{$data->description}}</textarea>
              </div>

      </div>
    </div>


@endsection
@section('scripts')
<script type="text/javascript">
  'use strict';

</script>
@endsection

