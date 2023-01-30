@extends('layouts.front')

@push('css')

@endpush

@section('content')
<!-- Account -->
<section class="account-section pt-100 pb-100">
    <div class="container">
        <div class="account-wrapper bg--body">
            <div class="section-title mb-3">
                <h3 class="title">@lang('Register Institution Now')</h3>
            </div>
            <form id="registerform" class="account-form row gy-3 gx-4 align-items-center" action="{{ route('user.domain.register.submit',$data->id) }}" method="POST">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label for="name" class="form-label">@lang('Your Name')</label>
                        <input type="text" pattern="[^()/><\][\\\-;!|]+" id="name" name="name" placeholder="{{ __('Enter Your Name') }}" class="form-control form--control" required>
                    </div>
                    <div class="col-sm-6">
                        <label for="email" class="form-label">@lang('Your Email')</label>
                        <input type="email" id="email" name="email" placeholder="{{ __('Enter Your Email') }}" class="form-control form--control" required>
                    </div>
                    <div class="col-sm-6">
                        <label for="phone" class="form-label">@lang('Your Phone')</label>
                        <input type="number" id="phone" name="phone" placeholder="{{ __('Enter Your Phone') }}" class="form-control form--control" required>
                    </div>
                    <div class="col-sm-6">
                        <label for="domains" class="form-label">@lang('Domain configration')</label>
                        <input type="text" id="domains" name="domains" placeholder="{{ __('Enter Domain Name') }}" class="form-control form--control" required>
                    </div>
                    <div class="col-sm-6">
                        <label for="password" class="form-label">@lang('Password')</label>
                        <input type="password" id="password" name="password" placeholder="{{ __('Enter Password') }}" class="form-control form--control" required>
                    </div>
                    <div class="col-sm-6">
                        <label for="confirm-password" class="form-label">@lang('Confirm Password')</label>
                        <input type="password" id="confirm-password" name="password_confirmation" placeholder="{{ __('Enter Confirm Password') }}" class="form-control form--control" required>
                    </div>

                    <!-- <div class="section-title mb-3"> -->
                    <h5 class="title">@lang('Institution Information')</h5>
                    <!-- </div> -->

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="inp-name" class="form-label">{{ __('Company Name') }}</label>
                            <input type="text" pattern="[^()/><\][\\;!|]+" class="form-control form--control" id="inp-name" name="iname" placeholder="{{ __('Enter Company Name') }}" value="" required>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="inp-name" class="form-label">{{ __('VAT Number') }}</label>
                            <input type="text" pattern="[^()/><\][\\;!|]+" class="form-control form--control" id="vat" name="vat" placeholder="{{ __('Enter VAT Number') }}" value="{{$data->vat}}" required>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="inp-name" class="form-label">{{ __('Address') }}</label>
                            <input type="text" pattern="[^()/><\][\\;!|]+" class="form-control form--control" id="address" name="address" placeholder="{{ __('Enter Address') }}" value="" required>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="inp-name" class="form-label">{{ __('City') }}</label>
                            <input type="text" pattern="[^()/><\][\\;!|]+" class="form-control form--control" id="city" name="city" placeholder="{{ __('Enter City') }}" value="" required>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="inp-name" class="form-label">{{ __('Zip Code') }}</label>
                            <input type="text" pattern="[^()/><\][\\;!|]+" class="form-control form--control" id="zip" name="zip" placeholder="{{ __('Enter Zip Code') }}" value="" required>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="inp-name" class="form-label">{{ __('Select Country') }}</label>
                            <select class="form-control form--control mb-3" name="country_id" required>
                                <option value="">{{ __('Select Country') }}</option>
                                @foreach(DB::table('countries')->get() as $dta)
                                <option value="{{ $dta->id }}">{{ $dta->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="inp-email" class="form-label">{{ __('Email of Institution') }}</label>
                            <input type="email" class="form-control form--control" id="inp-email" name="iemail" placeholder="{{ __('Enter Email') }}" value="" required>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="inp-phone" class="form-label">{{ __('Phone of Institution') }}</label>
                            <input type="number" class="form-control form--control" id="inp-phone" name="iphone" placeholder="{{ __('Enter Phone') }}" value="" required>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="form-check mt-3 mb-0">
                            <input type="checkbox" id="accept" class="form-check-input" checked>
                            <label class="form-check-label" for="accept">@lang('I accept all the') <a href="#0" class="text--base">@lang('privacy & policy')</a></label>
                        </div>
                    </div>

                    <div class="col-sm-12 d-flex flex-wrap justify-content-between align-items-center">
                        <button type="submit" class="cmn--btn bg--base me-3">
                            @lang('Register Now')
                        </button>
                    </div>
                    @includeIf('includes.user.form-both')
                    @csrf
                </div>
            </form>
        </div>
    </div>
</section>
<!-- Account -->

@endsection

@push('js')

@endpush
