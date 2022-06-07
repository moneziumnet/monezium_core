@extends('layouts.merchant')
@section('content')

    <div class="card">
        <div class="d-sm-flex align-items-center justify-content-between py-3">
        <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Profile Setting') }}</h5>
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="{{route('merchant.dashboard')}}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('merchant.profile.setting') }}">{{ __('Profile Setting') }}</a></li>
        </ol>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">{{ __('Profile Setting Form') }}</h6>
        </div>

        <div class="card-body">
        <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
        <form class="geniusform" action="{{ route('merchant.profile.setting') }}" method="POST" enctype="multipart/form-data">

            @include('includes.merchant.form-both')

            {{ csrf_field() }}

            <div class="form-group">
                <label>{{ __('Profile Picture') }} <small class="small-font">({{ __('Preferred Size 600 X 600') }})</small></label>
                <div class="wrapper-image-preview">
                    <div class="box">
                        <div class="back-preview-image" style="background-image: url({{ @$user->photo ? asset('assets/images/'.$user->photo):asset('assets/images/placeholder.jpg') }});"></div>
                        <div class="upload-options">
                            <label class="img-upload-label" for="img-upload"> <i class="fas fa-camera"></i> {{ __('Upload Picture') }} </label>
                            <input id="img-upload" type="file" class="image-upload" name="photo" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="inp-name">{{ __('Business Name') }}</label>
                <input type="text" class="form-control" id="inp-business-name" name="business_name"  placeholder="{{ __('Enter Business Name') }}" value="{{@$user->business_name}}" required>
            </div>
           
            <div class="form-group">
                <label for="inp-name">{{ __('Your Name') }}</label>
                <input type="text" class="form-control" id="inp-name" name="name"  placeholder="{{ __('Enter Your Name') }}" value="{{@$user->name}}" required>
            </div>

            <div class="form-group">
                <label for="inp-eml">{{ __('Email Address') }}</label>
                <input type="email" class="form-control" id="inp-eml" name="email"  placeholder="{{ __('Enter Email Address') }}" value="{{@$user->email}}" disabled>
            </div>

            <div class="form-group">
                <label for="inp-phn">{{ __('Phone') }}</label>
                <input type="text" class="form-control" id="inp-phn" name="phone"  placeholder="{{ __('Phone Number') }}" value="{{ @$user->phone }}" disabled>
            </div>
            
            <div class="form-group">
                <label for="inp-country">{{ __('Country') }}</label>
                <input type="text" class="form-control" id="inp-country" name="country"  placeholder="{{ __('Country') }}" value="{{ @$user->country }}" required disabled>
            </div>
            <div class="form-group">
                <label for="inp-city">{{ __('City') }}</label>
                <input type="text" class="form-control" id="inp-city" name="city"  placeholder="{{ __('City') }}" value="{{ @$user->city }}" required>
            </div>
            <div class="form-group">
                <label for="inp-address">{{ __('Address') }}</label>
                <input type="text" class="form-control" id="inp-address" name="address"  placeholder="{{ __('Address') }}" value="{{ @$user->address }}" required>
            </div>
            <div class="form-group">
                <label for="inp-zip">{{ __('Zip') }}</label>
                <input type="text" class="form-control" id="inp-zip" name="zip"  placeholder="{{ __('Zip') }}" value="{{ @$user->zip }}" required>
            </div>

            

            <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>

        </form>
        </div>
    </div>

@endsection