@extends('layouts.admin')

@section('content')

<div class="card">
    <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Currency API') }}</h5>
        <ol class="breadcrumb py-0 m-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.gs.currencyapi') }}">{{ __('API Settings') }}</a></li>
        </ol>
    </div>
</div>

  <div class="card mb-4 mt-3">
    @include('admin.system.systemapitab')

    <div class="p-3">
        <div class="card mt-3 p-3">
            <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
            <form class="geniusform ms-2" action="{{ route('admin.gs.update') }}" method="POST" enctype="multipart/form-data">

                @include('includes.admin.form-both')

                {{ csrf_field() }}

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="inp-key">{{  __('API Url')  }}</label>
                            <input type="url"  class="form-control" id="inp-key" name="currency_api"  placeholder="{{ __('Url') }}" value="{{ $gs->currency_api }}">
                        </div>
                    </div>


                </div>

                <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>

            </form>
        </div>
    </div>
  </div>

@endsection
