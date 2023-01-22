@extends('layouts.admin')

@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Edit Domain') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.requestdomain.index')}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
    <ol class="breadcrumb m-0 py-0">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.requestdomain.index') }}">{{ __('Domain management') }}</a></li>
      <li class="breadcrumb-item"><a href="{{route('admin.requestdomain.edit',$data->id)}}">{{ __('Edit Domain') }}</a></li>
    </ol>
  </div>
</div>

<div class="row justify-content-center mt-3">
  <div class="col-md-10">
    <!-- Form Basic -->
    <div class="card mb-4">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">{{ __('Edit Form') }}</h6>
      </div>

      <!-- <div class="alert alert-warning">
        {{ __('Your database user must have permission to CREATE DATABASE, because we need to create database when new tenant create.') }}
      </div> -->

      <div class="card-body">

        <form class="geniusform" action="{{route('admin.requestdomain.user.update',$data->id)}}" method="POST" enctype="multipart/form-data">

          @include('includes.admin.form-both')
          {{ csrf_field() }}

          <div class="form-group">
            <label for="inp-name">{{ __('Name') }}</label>
            <input type="text" pattern="[^()/><\][-;!|]+" class="form-control" id="inp-name" name="name" placeholder="{{ __('Enter Name') }}" value="{{$data->name}}" required>
          </div>

          <div class="form-group">
            <label for="inp-email">{{ __('Email') }}</label>
            <input type="email" class="form-control" id="inp-email" name="email" placeholder="{{ __('Enter Email Address') }}" value="{{$data->email}}" required>
          </div>
          <div class="form-group">
            <label for="inp-domains">{{ __('Domain configration') }}</label>
            <input type="text" pattern="[^()/><\][;!|]+" class="form-control" id="inp-domains" name="domains" placeholder="{{ __('Enter domain name') }}" value="{{$data->domain_name}}" required>
            <span>{{ __('how to add-on domain in your hosting panel.') }}<a href="{{ asset('assets/pdf/adddomain.pdf') }}" class="m-2" target="_blank">{{ __('Document') }}</a></span>
          </div>

          <div class="form-group">
            <label for="inp-db_name">{{ __('Database Name') }}</label>
            <input type="text" pattern="[^()/><\][;!|]+" class="form-control" id="inp-db_name" name="db_name" placeholder="{{ __('Enter Database Name') }}" value="" required>
          </div>

          <div class="form-group">
            <label for="inp-db_username">{{ __('Database User') }}</label>
            <input type="text" pattern="[^()/><\][;!|]+" class="form-control" id="inp-db_username" name="db_username" placeholder="{{ __('Enter Database Username') }}" value="" required>
          </div>

          <div class="form-group">
            <label for="inp-db_password">{{ __('Database Password') }}</label>
            <input type="password" class="form-control" id="inp-db_password" name="db_password" placeholder="{{ __('Enter Database Password') }}" value="">
          </div>
          <input type="hidden" name="type" value="{{ $data->type }}">
          <input type="hidden" name="password" value="{{ $data->password }}">

          <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>
        </form>
      </div>
    </div>
  </div>

</div>
@endsection


@section('scripts')


@endsection
