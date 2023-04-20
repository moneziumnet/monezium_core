@extends('layouts.front')

@push('css')

@endpush

@section('content')
    <!-- Hero -->
    <section class="hero-section bg--overlay bg_img" data-img="{{ asset('assets/images/'.$gs->breadcumb_banner) }}">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">@lang('Login')</h1>
            </div>
        </div>
    </section>
    <!-- Hero -->

    <!-- Account -->
    <section class="account-section pt-100 pb-100">
        <div class="container">
            <div class="account-wrapper bg--body">
                <div class="section-title mb-3">
                    <h6 class="subtitle text--base">@lang('Sign In')</h6>
                    <h3 class="title">@lang('Login Now')</h3>
                </div>
                <form class="account-form row gy-3 gx-4 align-items-center" id="loginform" action="{{ route('user.login.submit') }}" method="POST">
                    @includeIf('includes.user.form-both')
                    @csrf
                    <div class="col-sm-12">
                        <label for="email" class="form-label">@lang('Your Email')</label>
                        <input type="email" id="email" name="email" class="form-control form--control">
                    </div>
                    <div class="col-sm-12">
                        <label for="password" class="form-label">@lang('Your Password')</label>
                        <input type="password" id="password" name="password" class="form-control form--control">
                    </div>
                    <input type="hidden" id="global_ip" name="global_ip" class="form-control form--control">
                    <div class="col-sm-12">
                        <button type="submit" class="cmn--btn bg--base me-3">
                            @lang('Login Now')
                        </button>
                        <div class="d-flex flex-wrap justify-content-between align-items-center mt-2">
                            <a href="{{ route('user.forgot') }}" class="text--base mt-1">@lang('Forget Password')</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <!-- Account -->


@endsection

@push('js')
<script type="text/javascript">
    $(document).ready(function() {
        $.getJSON("https://api.ipify.org/?format=json", function(e) {
            $('#global_ip').val(e.ip);
        });
    })
</script>


@endpush
