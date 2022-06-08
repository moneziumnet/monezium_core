@extends('layouts.admin')

@section('content')

<div class="card">
    <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Add New Customer') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.user.index')}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
    <ol class="breadcrumb m-0 py-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.user.index') }}">{{ __('Manage Customer') }}</a></li>
        <li class="breadcrumb-item"><a href="{{route('admin.user.create')}}">{{ __('Add New Customer') }}</a></li>
    </ol>
    </div>
</div>

<div class="row justify-content-center mt-3">
  <div class="col-md-10">
    <!-- Form Basic -->
    <div class="card mb-4">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">{{ __('Add New Customer Form') }}</h6>
      </div>

      <div class="card-body">
        
        <form class="geniusform" action="{{route('admin.user.store')}}" method="POST" enctype="multipart/form-data">

            @include('includes.admin.form-both')

            {{ csrf_field() }}


            <div class="form-group" id="set-picture">
                <label>{{ __('Set Picture') }} </label>
                <div class="wrapper-image-preview">
                    <div class="box">
                        <div class="back-preview-image" style="background-image: url({{ asset('assets/images/placeholder.jpg') }});"></div>
                        <div class="upload-options">
                            <label class="img-upload-label" for="img-upload"> <i class="fas fa-camera"></i> {{ __('Upload Picture') }} </label>
                            <input id="img-upload" type="file" class="image-upload" name="photo" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="inp-name">{{ __('Name') }}</label>
                <input type="text" class="form-control" id="inp-name" name="name"  placeholder="{{ __('Enter Name') }}" value="" required>
            </div>

            <div class="form-group">
                <label for="inp-email">{{ __('Email') }}</label>
                <input type="email" class="form-control" id="inp-email" name="email"  placeholder="{{ __('Enter Email') }}" value="" required>
            </div>

            <div class="form-group">
                <label for="inp-phone">{{ __('Phone') }}</label>
                <input type="text" class="form-control" id="inp-phone" name="phone"  placeholder="{{ __('Enter Phone') }}">
			      </div>

            <div class="form-group">
                <label for="inp-address">{{ __('Address') }}</label>
                <input type="text" class="form-control" id="inp-address" name="address"  placeholder="{{ __('Enter Address') }}">
            </div>


            <div class="form-group">
                <label for="inp-city">{{ __('City') }}</label>
                <input type="text" class="form-control" id="inp-city" name="city"  placeholder="{{ __('Enter City') }}">
            </div>

            <div class="form-group">
                <label for="inp-vat">{{ __('VAT Number') }}</label>
                <input type="text" class="form-control" id="inp-vat" name="vat"  placeholder="{{ __('Enter VAT') }}"  >
            </div>

            <div class="form-group">
                <label for="inp-zip">{{ __('Postal Code') }}</label>
                <input type="text" class="form-control" id="inp-zip" name="zip"  placeholder="{{ __('Enter Zip') }}">
            </div>

            <div class="form-group">
              <label for="inp-pass">{{ __('Password') }}</label>
              <input type="password" class="form-control" id="inp-pass" name="password"  placeholder="{{ __('Enter Password') }}" required>
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
