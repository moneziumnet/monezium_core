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
                @includeIf('includes.user.form-both')
                @csrf
                <div class="col-sm-6">
                    <label for="name" class="form-label">@lang('Your Name')</label>
                    <input type="text" id="name" name="name" class="form-control form--control">
                </div>
                <div class="col-sm-6">
                    <label for="email" class="form-label">@lang('Your Email')</label>
                    <input type="text" id="email" name="email" class="form-control form--control">
                </div>
                <div class="col-sm-6">
                    <label for="phone" class="form-label">@lang('Your Phone')</label>
                    <input type="text" id="phone" name="phone" class="form-control form--control">
                </div>
                <div class="col-sm-6">
                    <label for="domains" class="form-label">@lang('Domain configration')</label>
                    <input type="text" id="domains" name="domains" class="form-control form--control">
                </div>
                <div class="col-sm-6">
                    <label for="password" class="form-label">@lang('Password')</label>
                    <input type="password" id="password" name="password" class="form-control form--control">
                </div>
                <div class="col-sm-6">
                    <label for="confirm-password" class="form-label">@lang('Confirm Password')</label>
                    <input type="password" id="confirm-password" name="password_confirmation" class="form-control form--control">
                </div>
                <div class="col-sm-12 d-flex flex-wrap justify-content-between align-items-center">
                    <button type="submit" class="cmn--btn bg--base me-3">
                        @lang('Register Now')
                    </button>
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

@push('js')

@endpush