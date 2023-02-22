@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="d-sm-flex align-items-center justify-content-between py-3">
        <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Edit Contact') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.institution.contacts',$data->id) }}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
        <h5 class=" mb-0 text-gray-800 pl-3">{{ $data->name }}</h5>
        <ol class="breadcrumb py-0 m-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.institution.index') }}">{{ __('Institutions List') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.institution.contacts',$data->id) }}">{{ __('Contacts List') }}</a></li>
        </ol>
    </div>
</div>

<div class="row mt-3">
    <div class="col-lg-12">
        <div class="card mt-1 tab-card">
            @include('admin.institution.profile.tab')
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show p-3 active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
                    <form class="geniusform" action="{{ route('admin.institution.create-contact', $data->id)}}" method="POST" enctype="multipart/form-data">
                        @include('includes.admin.form-both')
                        {{ csrf_field() }}

                        <input type="hidden" name="contact_id" value="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="full-name">{{ __('Contact Type') }}</label>
                                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="contact" name="contact" placeholder="{{ __('Contact Type') }}" value="" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="full-name">{{ __('Name') }}</label>
                                    <input type="text" pattern="[^À-ž()/><\][\\\-;&$@!|]+" class="form-control" id="full_name" name="fullname" placeholder="{{ __('Enter Name') }}" value="" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="dob">{{ __('Date of Birth') }}</label>
                                    <input type="text" class="form-control datepicker" id="dob" data-provide="datepicker" readonly data-date-format="yyyy-mm-dd" name="dob" placeholder="{{ __('yyyy-mm-dd') }}" value="" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="personal-code">{{ __('Personal Code/Number') }}</label>
                                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="personal-code" name="personal_code" placeholder="{{ __('Enter Personal Code/Number') }}" value="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="your-email">{{ __('Your Email') }}</label>
                                    <input type="email" class="form-control" id="your-email" name="your_email" placeholder="{{ __('Enter Your Email') }}" value="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="your-phone">{{ __('Your Phone') }}</label>
                                    <input type="number" class="form-control" id="your-phone" name="your_phone" placeholder="{{ __('Enter Phone Number') }}" value="">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="your-address">{{ __('Address') }}</label>
                                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="your-address" name="your_address" placeholder="{{ __('Enter Address') }}" value="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="c_city">{{ __('Your City') }}</label>
                                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="c_city" name="c_city" placeholder="{{ __('Enter City') }}" value="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="c_zipcode">{{ __('Zip Code') }}</label>
                                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="c_zipcode" name="c_zipcode" placeholder="{{ __('Enter Zip Code') }}" value="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="your-country">{{ __('Select Country') }}</label>
                                    <select class="form-control mb-3" name="c_country_id">
                                        <option value="">{{ __('Select Country') }}</option>
                                        @foreach(DB::table('countries')->get() as $country)
                                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="your-id">{{ __('Your ID Number') }}</label>
                                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="your-id" name="your_id" placeholder="{{ __('Enter Your ID Number') }}" value="">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="your-id" required>{{ __('Provider Authority Name') }}</label>
                                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="issued_authority" name="issued_authority" placeholder="{{ __('Enter Provider Authority Name') }}" value="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date-of-issue">{{ __('Date of Issue') }}</label>
                                    <input type="text" class="form-control datepicker" id="date_of_issue" name="date_of_issue" data-provide="datepicker" readonly data-date-format="yyyy-mm-dd" placeholder="{{ __('yyyy-mm-dd') }}" value="" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date-of-expire">{{ __('Date of Expire') }}</label>
                                    <input type="text" class="form-control datepicker" id="date_of_expire" name="date_of_expire" data-provide="datepicker" readonly data-date-format="yyyy-mm-dd" placeholder="{{ __('yyyy-mm-dd') }}" value="" required>
                                </div>
                            </div>
                        </div>
                        <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')

<script type="text/javascript">
    "use strict";

    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true
    });
</script>

@endsection
