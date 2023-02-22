@extends('layouts.admin')

@section('content')
{{-- // Update Nexmo --}}
<div class="card">
    <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Nexmo API') }}</h5>
        <ol class="breadcrumb py-0 m-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.gs.nexmo') }}">{{ __('API Settings') }}</a></li>
        </ol>
    </div>
</div>

  <div class="card mb-4 mt-3">
    @include('admin.system.systemapitab')

    <div class="p-3">
        <div class="card mt-3 p-3">
            <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
            <form class="geniusform" action="{{ route('admin.gs.update') }}" method="POST" enctype="multipart/form-data">

                @include('includes.admin.form-both')

                {{ csrf_field() }}

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="inp-key">{{  __('Key')  }}</label>
                            <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="inp-key" name="nexmo_key"  placeholder="{{ __('Key') }}" value="{{ $gs->nexmo_key }}">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="inp-secret">{{  __('Secret')  }}</label>
                            <input type="text" class="form-control" id="inp-secret" name="nexmo_secret"  placeholder="{{ __('Secret') }}" value="{{ $gs->nexmo_secret }}">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="inp-name">{{ __('Status') }}</label>
                            <select class="form-control mb-3" name="nexmo_status">
                            <option value="1" {{ $gs->nexmo_status == 1 ? 'selected' : '' }}>{{ __('YES') }}</option>
                            <option value="0" {{ $gs->nexmo_status == 0 ? 'selected' : '' }}>{{ __('NO') }}</option>
                            </select>
                        </div>
                    </div>

                </div>

                <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>

            </form>
        </div>
    </div>
  </div>

@endsection
