<footer class="bg--section">
    <!-- <div class="footer-top position-relative">
        <div class="container">
            <div class="footer-wrapper">
                <div class="footer-logo">
                    <a href="{{ route('front.index') }}">
                        <img src="{{asset('assets/images/'.$gs->logo)}}" width="110" height="32" alt="Tabler" class="navbar-brand-image" style="width: auto;">
                      </a>
                </div>
                <div class="footer-links">
                    <h5 class="title">@lang('About')</h5>
                    <ul>

                        <li>
                            <a href="{{ route('front.page','privacy') }}">{{__('Privacy & Policy') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('front.page','term-service') }}">{{ __('Term of Service') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('front.page','about') }}">{{ __('About Us') }}</a>
                        </li>
                    </ul>
                </div>
                <div class="footer-links mobile-second-item">
                    <h5 class="title">@lang('Contact')</h5>
                    <ul>
                        <li>
                            <a href="#0">{{$ps->street}}</a>
                        </li>
                        <li>
                            <a href="#0">{{$ps->contact_email}}</a>
                        </li>
                        <li>
                            <a href="#0">{{$ps->phone}}</a>
                        </li>
                    </ul>
                </div>
                
                <div class="footer-comunity">
                    <h5 class="title">@lang('Community')</h5>
                    <ul class="social-icons justify-content-start mt-0 mb-4">
                        @if ($social->f_status)
                            <li>
                                <a href="{{$social->facebook}}"><i class="fab fa-facebook-f"></i></a>
                            </li>
                        @endif

                        @if ($social->t_status)
                            <li>
                                <a href="{{$social->twitter}}"><i class="fab fa-twitter"></i></a>
                            </li>
                        @endif

                        @if ($social->l_status)
                            <li>
                                <a href="{{$social->linkedin}}"><i class="fab fa-linkedin-in"></i></a>
                            </li>
                        @endif
                    </ul>
                    
                </div>
            </div>
        </div>
    </div> -->
    <div class="footer-bottom">
        <div class="container-xl d-flex">
            <div class="col-sm-6 col-md-10 d-flex" style="justify-content: left; align-items: center;">
                <a href="{{ route('front.page','privacy') }}" class="footer-link" style="margin: 0 0.5rem;">{{__('Privacy & Policy') }}</a>
                <a href="{{ route('front.page','term-service') }}" class="footer-link" style="margin: 0 0.5rem;">{{ __('Term of Service') }}</a>
                <a href="{{ route('front.page','about') }}" class="footer-link" style="margin: 0 0.5rem;">{{ __('About Us') }}</a>
                <a href="{{ route('front.page','contact') }}" class="footer-link" style="margin: 0 0.5rem;">{{ __('Contacts') }}</a>
                <!-- @php
                    echo $gs->copyright;
                @endphp -->
                <span style="margin: 0 0.6rem; font-weight:400">
                @php
                    echo $gs->copyright;
                @endphp
                </span>
            </div>
            <div class="col-sm-6 col-md-2 d-flex" style="justify-content: right; align-items: center;">
                <span style="float: right;">English</span>
                <a href="?theme=dark" class="hide-theme-dark footer-link" style="float: right;margin-left:0.8rem;" title="Enable dark mode" data-bs-toggle="tooltip" data-bs-placement="bottom">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z" />
                    </svg>
                </a>
                <a href="?theme=light" class="hide-theme-light footer-link" style="float: right;margin-left:0.8rem;" title="Enable light mode" data-bs-toggle="tooltip" data-bs-placement="bottom">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <circle cx="12" cy="12" r="4" />
                    <path d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7" />
                    </svg>
                </a>
            </div>
            
        </div>
    </div>
</footer>