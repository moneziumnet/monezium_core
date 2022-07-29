<footer class="bg--section">
    <div class="footer-top position-relative">
        <div class="container">
            <div class="footer-wrapper">
                <div class="footer-logo">
                    <a href="{{ route('front.index') }}">
                        <img src="{{asset('assets/images/'.$gs->logo)}}" width="110" height="32" alt="Tabler" class="navbar-brand-image">
                      </a>
                </div>
                <div class="footer-links">
                    <h5 class="title">@lang('About')</h5>
                    <ul>
                        {{-- @foreach(DB::table('pages')->whereStatus(1)->orderBy('id','desc')->get() as $data)
                            <li>
                                <a href="{{ route('front.page',$data->slug) }}">{{ $data->title }}</a>
                            </li>
                        @endforeach --}}
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
                            <a href="{{$ps->contact_email}}">{{$ps->contact_email}}</a>
                        </li>
                        <li>
                            <a href="{{$ps->phone}}">{{$ps->phone}}</a>
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
    </div>
    <div class="footer-bottom position-relative pb-5">
        <div class="container text-center">
            <p>
                @php
                    echo $gs->copyright;
                @endphp
            </p>
        </div>
    </div>
</footer>