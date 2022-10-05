@extends('layouts.front')

@push('css')

@endpush

@section('content')
<!-- Hero -->
<section class="hero-section bg--overlay bg_img" data-img="{{ asset('assets/images/'.$gs->breadcumb_banner) }}">
    <div class="container">
        <div class="hero-content">
            <h2 class="hero-title"></h2>
            <ul class="breadcrumb">
                <li>
                    <a href="{{ route('front.index') }}">@lang('Home')</a>
                </li>
                <li>
                    @lang('Registration')
                </li>
            </ul>
        </div>
    </div>
</section>
<!-- Hero -->

<!-- Account -->
<section class="account-section pt-100 pb-100">
    <div class="container">
        <div class="account-wrapper bg--body">
            <div class="section-title mb-3">
                <h6 class="subtitle text--base">@lang('Sign Up')</h6>
                <h3 class="title">@lang('Create Account Now')</h3>
            </div>
            <form id="registerform" class="account-form row gy-3 gx-4 align-items-center" action="{{ route('user.register.submit', $id) }}" method="POST">
                @includeIf('includes.user.form-both')
                @csrf
                <input type="hidden" name="reff" id="reff" value="{{Session::get('affilate')}}">
                <div class="form-group">
                    <label for="inp-user-type" class="form-label">{{ __('Select Type') }}</label>
                    <select id="test" class="form-control form--control mb-3" name="form_select" onchange="showDiv( this)">
                        <option value="0"> Private</option>
                        <option value="1"> Corporate</option>
                    </select>
                </div>
                <div class="col-sm-6">
                    <label for="name" class="form-label">@lang('Your First Name')</label>
                    <input type="text" id="name" name="firstname" class="form-control form--control" required>
                </div>
                <div class="col-sm-6">
                    <label for="name" class="form-label">@lang('Your Last Name')</label>
                    <input type="text" id="name" name="lastname" class="form-control form--control" required>
                </div>
                <div class="col-sm-6">
                    <label for="customer_dob" class="form-label">@lang('Your Birthday')</label>
                    <input type="date" id="customer_dob" name="customer_dob" class="form-control form--control" required>
                </div>
                <div class="col-sm-6">
                    <label for="email" class="form-label">@lang('Your Email')</label>
                    <input type="email" id="email" name="email" class="form-control form--control" required>
                </div>
                <div class="col-sm-12">
                    <label for="phone" class="form-label">@lang('Your Phone')</label>
                    <input type="text" id="phone" name="phone" class="form-control form--control" required>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="inp-name" class="form-label">{{ __('Address') }}</label>
                        <input type="text" class="form-control form--control" id="address" name="address" placeholder="{{ __('Enter Address') }}" value="" required>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="inp-name" class="form-label">{{ __('City') }}</label>
                        <input type="text" class="form-control form--control" id="city" name="city" placeholder="{{ __('Enter City') }}" value="" required>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="inp-name" class="form-label">{{ __('Zip Code') }}</label>
                        <input type="text" class="form-control form--control" id="zip" name="zip" placeholder="{{ __('Enter Zip Code') }}" value="" required>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="inp-name" class="form-label">{{ __('Select Country') }}</label>
                        <select class="form-control form--control" name="country" required>
                            <option value="">{{ __('Select Country') }}</option>
                            @foreach(DB::table('countries')->get() as $dta)
                            <option value="{{ $dta->id }}">{{ $dta->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="password" class="form-label">@lang('Your Password')</label>
                        <input type="password" id="password" name="password" class="form-control form--control" required>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="confirm-password" class="form-label">@lang('Confirm Password')</label>
                        <input type="password" id="confirm-password" name="password_confirmation" class="form-control form--control" required>
                    </div>
                </div>
                <div id="private_div" class="col-sm-12">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="personal-code" class="form-label">{{ __('Personal Code/Number') }}</label>
                                <input type="text" class="private-input form-control form--control" id="personal_code" name="personal_code" placeholder="{{ __('Enter Personal Code/Number') }}" value="" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="your-id" class="form-label">{{ __('Your ID Number') }}</label>
                                <input type="text" class="private-input form-control form--control" id="your_id" name="your_id" placeholder="{{ __('Enter Your ID Number') }}" value="" required>
                            </div>
                        </div>
                        <div class="col-sm-6 mt-3">
                            <div class="form-group">
                                <label for="your-id" class="form-label" required>{{ __('Provider Authority Name') }}</label>
                                <input type="text" class="private-input form-control form--control" id="issued_authority" name="issued_authority" placeholder="{{ __('Enter Provider Authority Name') }}" value="" required>
                            </div>
                        </div>
                        <div class="col-sm-6 mt-3">
                            <div class="form-group">
                                <label for="date-of-issue" class="form-label">{{ __('Date of Issue') }}</label>
                                <input type="date" class="private-input form-control form--control datepicker" id="date_of_issue" name="date_of_issue" placeholder="{{ __('yyyy-mm-dd') }}" value="" required>
                            </div>
                        </div>
                        <div class="col-sm-6 mt-3">
                            <div class="form-group">
                                <label for="date-of-expire" class="form-label">{{ __('Date of Expire') }}</label>
                                <input type="date" class="private-input form-control form--control datepicker" id="date_of_expire" name="date_of_expire" placeholder="{{ __('yyyy-mm-dd') }}" value="" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="corporate_div" style="display: none;" class="col-sm-12">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="dob" class="form-label">{{ __('Company Name') }}</label>
                                <input type="text" class="company-input form-control form--control" id="company_name"name="company_name" placeholder="{{ __('Enter Company Name') }}" value="">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                @php
                                    $companytype = ['LIMITED_LIABILITY', 'SOLE_TRADER', 'PARTNERSHIP', 'PUBLIC_LIMITED_COMPANY', 'JOINT_STOCK_COMPANY', 'CHARITY']
                                @endphp
                                <label for="inp-user-type" class="form-label">{{ __('Select Company Type') }}</label>
                                <select id="company_type" class="form-control" name="company_type" required>
                                    <option value=""> {{__('Select Company Type')}}</option>
                                    @foreach ( $companytype as $type )
                                        <option value="{{$type}}"> {{$type}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="inp-name" class="form-label">{{ __('Company Address') }}</label>
                                <input type="text" class="company-input form-control form--control" id="company_address" name="company_address" placeholder="{{ __('Enter Company Address') }}" value="">
                            </div>
                        </div>
                        <div class="col-sm-6 mt-3">
                            <div class="form-group">
                                <label for="dob" class="form-label">{{ __('Registration No') }}</label>
                                <input type="text" class="company-input form-control form--control" id="company_reg_no"name="company_reg_no" placeholder="{{ __('Enter Company Registration No') }}" value="">
                            </div>
                        </div>
                        <div class="col-sm-6 mt-3">
                            <div class="form-group">
                                <label for="dob" class="form-label">{{ __('VAT No') }}</label>
                                <input type="text" class="company-input form-control form--control" id="company_vat_no"name="company_vat_no" placeholder="{{ __('Enter Company VAT No') }}" value="">
                            </div>
                        </div>
                        <div class="col-sm-12 mt-3">
                            <div class="form-group">
                                <label for="dob" class="form-label">{{ __('Registration Date') }}</label>
                                <input type="date" class="company-input form-control form--control" id="company_dob" name="company_dob" placeholder="{{ __('yyyy-mm-dd') }}" value="">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 d-flex flex-wrap justify-content-between align-items-center">
                    <button type="submit" class="cmn--btn bg--base me-3">
                        @lang('Register Now')
                    </button>
                    <div class="text-end">
                        <a href="{{ route('user.login')}}" class="text--base">@lang('Already have
                            an account ')?</a>
                    </div>
                </div>
                <div class="mt-2">
                    <div class="form-check mt-3 mb-0">
                        <input type="checkbox" id="accept" class="form-check-input" checked>
                        <label class="form-check-label" for="accept">@lang('I accept all the') <a href="#0" class="text--base">@lang('privacy & policy')</a></label>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
<!-- Account -->

@endsection
@section('scripts')
<script type="text/javascript">
    'use strict';
    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true
    });
</script>
@endsection

@push('js')

@endpush
