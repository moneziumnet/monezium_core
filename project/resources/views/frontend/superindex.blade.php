@extends('layouts.front')

@push('css')

@endpush

@section('content')
<!-- Banner -->
<section class="banner-section bg--overlay bg_img" data-img="{{ asset('assets/images/'.$ps->hero_photo) }}">
    <div class="container">
        <div class="banner-wrapper">
            <div class="banner-content">
                <h1 class="banner-title">{{ $ps->hero_title }}</h1>
                <p>
                    {{ $ps->hero_subtitle }}
                </p>
                <div class="btn__grp">
                    <a href="{{ $ps->hero_btn_url }}" class="cmn--btn">
                        @lang('Get Started')
                    </a>
                    <a href="{{ $ps->hero_link }}" class="video--btn" data-lightbox>
                        <i class="fas fa-play"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Banner -->

<!-- Feature -->
<section class="feature-section pt-100 pb-50">
    <div class="container">
        <div class="mt--120">
            <div class="row justify-content-center g-3 g-md-4 g-lg-3 g-xl-4">
                @foreach ($features as $key=>$data)
                <div class="col-lg-3 col-sm-6">
                    <div class="feature-item">
                        <div class="feature-item__icon">
                            <img src="{{asset('assets/images/'.$data->photo)}}" alt="download bitcoin">
                        </div>
                        <div class="feature-item__cont">
                            <h5 class="feature-item__cont-title">{{$data->title}}</h5>
                            <p>
                                {{$data->details}}
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
<!-- Feature -->

<!-- About -->
<section class="about-section pt-50 pb-50">
    <div class="container">
        <div class="row gy-5">
            <div class="col-lg-6">
                <div class="about-thumb h-100">
                    <div class="thumb">
                        <img src="{{asset('assets/images/'.$ps->about_photo)}}" alt="about">
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="about-content">
                    <div class="section-title">
                        <h6 class="subtitle text--base">@lang('Who We are')</h6>
                        <h2 class="title">{{ $ps->about_title }}</h2>
                        <p>
                            @php
                            echo $ps->about_text;
                            @endphp
                        </p>
                    </div>
                    <ul class="about-list">
                        @if ($ps->about_attributes)
                        @foreach (json_decode($ps->about_attributes,true) as $key=>$attribute)
                        <li>
                            <span>
                                {{ $attribute }}
                            </span>
                        </li>
                        @endforeach
                        @endif

                    </ul>
                    <div class="btn__grp mt-4 pt-3">
                        <a href="{{ $ps->about_link }}" class="cmn--btn btn-outline">@lang('Get Started')</a>
                        <a href="{{ route('front.about') }}" class="cmn--btn">@lang('More About Us')</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- About -->

<!-- Service -->
<section class="service-section pt-50 pb-100">
    <div class="container">
        <div class="section-title text-center">
            <h6 class="subtitle text--base">@lang('Smart Banking')
            </h6>
            <h2 class="title">{{ $ps->service_title }}</h2>
            <p>
                @php
                echo $ps->service_subtitle;
                @endphp
            </p>
        </div>
        <div class="row g-4 g-xxl-4 g-xl-3 justify-content-center">
            @foreach ($services as $service)
            <div class="col-md-6 col-xl-4">
                <div class="service-item">
                    <div class="service-item__icon">
                        <img src="{{asset('assets/images/'.$data->photo)}}" alt="strong" />
                    </div>
                    <div class="service-item__cont">
                        <h5 class="service-item__cont-title">
                            {{ $service->title }}
                        </h5>
                        <p>
                            @php
                            echo $service->details;
                            @endphp
                        </p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
<!-- Service -->

<!-- How It Works -->
<section class="how-it-works overflow-hidden bg--section pt-100 pb-50">
    <div class="container">
        <div class="section-title text-center">
            <h6 class="subtitle text--base">@lang('Strategy')</h6>
            <h2 class="title">{{ $ps->strategy_title }}</h2>
            @php
            echo $ps->strategy_details;
            @endphp
        </div>
        <div class="row flex-wrap-reverse">
            <div class="col-lg-6">
                <div class="how-it-wrapper">
                    <div class="how-it-header bg--title">
                        <h3 class="title text--white m-0">@lang('Create Account')</h3>
                        <p>@lang('Veniam laudantium cumque quasi, fuga magni esse.')</p>
                    </div>
                    <div class="how-it-body">
                        <ul class="how-it-area">
                            @foreach ($process as $key=>$data)
                            <li class="{{ $loop->first ? 'open active' : ''}}">
                                <h6 class="subtitle">{{ $loop->iteration }}. {{ $data->title }}</h6>
                                <div class="text">
                                    {{ $data->details }}
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="how-it-img text-lg-end">
                    <img src="{{ asset('assets/images/'.$ps->strategy_banner) }}" alt="about">
                </div>
            </div>
        </div>
    </div>
</section>
<!-- How It Works -->

<!-- Plan -->
<section class="plan-section bg--section pt-50 pb-100" id="pricing">
    <div class="container">
        <div class="section-title text-center">
            <h6 class="subtitle text--base">@lang('Subscription Plan for Institution')</h6>
            
        </div>

        <div class="tab-pane fade show active" id="deposit">
            <div class="row g-4 justify-content-center pricing--wrapper">
                @foreach ($subscripplans as $key=>$data)
                <div class="col-lg-4 col-sm-10 col-md-6">
                    <div class="plan__item">
                        <div class="plan__item-header">
                            <div class="left">
                                <h5 class="title">{{ $data->name}}</h5>
                            </div>
                            <div class="right">
                                <h5 class="title">{{ showprice($data->price,$currency) }}</h5>
                                <span>@lang('Price')</span>
                            </div>
                        </div>
                        <div class="plan__item-body">
                            <ul>
                                <li>
                                    <div class="name font-weight-normal">
                                        @lang('Duration')
                                    </div>

                                    <div class="info">
                                        {{ $data->duration . ' ' . $data->durationtype }}
                                    </div>
                                </li>
                            </ul>
                            <a href="{{ route('user.domain.register', $data->id) }}" class="cmn--btn bg--base w-100 text--white">{{__('Apply')}}</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>


    </div>
</section>
<!-- Plan -->

<!-- Apps -->
<section class="apps-section pt-100 pb-50">
    <div class="container">
        <div class="row align-items-center justify-content-between flex-wrap-reverse gy-5">
            <div class="col-lg-4 col-md-5">
                <div class="app-img">
                    <img src="{{ asset('assets/images/'.$ps->app_banner) }}" alt="app">
                </div>
            </div>
            <div class="col-md-7">
                <div class="section-title">
                    <h6 class="subtitle text--base">@lang('Apps')</h6>
                    <h2 class="title">{{ $ps->app_title }}</h2>

                    @php
                    echo $ps->app_details;
                    @endphp

                </div>
                <div class="app__btns">
                    <a href="{{ $ps->app_store_link }}">
                        <img src="{{ asset('assets/images/'.$ps->app_store_photo) }}" alt="about">
                    </a>
                    <a href="{{ $ps->app_google_link }}">
                        <img src="{{ asset('assets/images/'.$ps->app_google_store) }}" alt="about">
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Apps -->

<!-- Clients -->
<section class="clients-section pt-50 pb-50">
    <div class="container">
        <div class="row">
            <div class="col-xl-4">
                <div class="section-title">
                    <h6 class="subtitle text--base">@lang('Testimonials')</h6>
                    <h2 class="title">{{ $ps->review_title }}</h2>
                    <p>
                        @php
                        echo $ps->review_text;
                        @endphp
                    </p>
                    <div class="owl-trigger">
                        <div class="owl-prev"></div>
                        <div class="owl-next active"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-8">
                <div class="clients-slider bg--title client-slider-bg owl-theme owl-carousel">
                    @foreach ($testimonials as $key=>$data)
                    <div class="clients-item h-100">
                        <div class="clients-content">
                            <blockquote>
                                @php
                                echo $data->details;
                                @endphp
                            </blockquote>
                            <h4 class="name text--base">{{ $data->title }}</h4>
                        </div>
                        <div class="clients-thumb">
                            <div class="thumb">
                                <img src="{{ asset('assets/images/'.$data->photo) }}" alt="clients">
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Clients -->

<!-- Counter -->
<section class="counter-section pb-100 pt-50">
    <div class="container">
        <div class="row g-4">
            @foreach ($counters as $key=>$data)
            <div class="col-sm-6 col-lg-3">
                <div class="counter-item">
                    <div class="counter-icon">
                        <i class="{{ $data->icon }}"></i>
                    </div>
                    <div class="counter-content">
                        <h3 class="counter-title">
                            @if ($data->is_money == 1)
                            <span class="count">$</span>
                            @endif
                            <span class="odometer count" data-odometer-final="{{ $data->count }}"></span>
                            <span class="count">M</span>
                        </h3>
                        <h6 class="counter-subtitle">{{ $data->title }}</h6>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
<!-- Counter -->

<!-- CTAs -->
@includeIf('partials.front.cta')
<!-- CTAs -->

@endsection

@push('js')

@endpush