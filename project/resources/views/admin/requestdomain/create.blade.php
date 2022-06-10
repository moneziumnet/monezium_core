@extends('layouts.admin')

@section('content')

<div class="card">
    <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Add New Domain') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.requestdomain.index')}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
    <ol class="breadcrumb m-0 py-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.requestdomain.index') }}">{{ __('Domain management') }}</a></li>
        <li class="breadcrumb-item"><a href="{{route('admin.requestdomain.create')}}">{{ __('Add New Domain') }}</a></li>
    </ol>
    </div>
</div>

<div class="row justify-content-center mt-3">
  <div class="col-md-10">
    <!-- Form Basic -->
    <div class="card mb-4">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">{{ __('Add New Domain Form') }}</h6>
      </div>

      <div class="card-body">
        
        <form class="geniusform" action="{{route('admin.requestdomain.store')}}" method="POST" enctype="multipart/form-data">

            @include('includes.admin.form-both')
            {{ csrf_field() }}

            <div class="form-group">
                <label for="inp-name">{{ __('Name') }}</label>
                <input type="text" class="form-control" id="inp-name" name="name"  placeholder="{{ __('Enter Name') }}" value="" required>
            </div>

            <div class="form-group">
                <label for="inp-email">{{ __('Email') }}</label>
                <input type="email" class="form-control" id="inp-email" name="email"  placeholder="{{ __('Enter Email') }}" value="" required>
            </div>

            <div class="form-group">
              <label for="inp-pass">{{ __('Password') }}</label>
              <input type="password" class="form-control" id="inp-pass" name="password"  placeholder="{{ __('Enter Password') }}" value="" required>
            </div>

            <div class="form-group">
                <label for="password2" class="d-block">{{ __('Password Confirmation') }}</label> 
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="{{ __('Enter confirm password') }}" required autocomplete="new-password">
            </div>

            <div class="form-group">
                <label for="inp-domains">{{ __('Domain configration') }}</label>
                <input type="text" class="form-control" id="inp-domains" name="domains"  placeholder="{{ __('Enter domain name') }}" value="" required>
                <span>{{ __('how to add-on domain in your hosting panel.') }}<a
                                href="{{ asset('assets/pdf/adddomain.pdf') }}" class="m-2"
                                target="_blank">{{ __('Document') }}</a></span>
            </div>
            <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>
        </form>
      </div>
    </div>
  </div>

</div>
@endsection


@section('scripts')


@endsection
