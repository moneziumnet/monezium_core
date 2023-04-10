<header class="navbar navbar-expand-xl navbar-light d-print-none">
  <div class="container-fluid">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    @if($website_theme == 1) 
      <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
        <a href="{{ route('front.index') }}">
          <img src="{{asset('assets/images/'.$gs->logo)}}" width="110" height="32" alt="Tabler" class="navbar-brand-image">
        </a>
      </h1>
    @else
    <!-- <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3"></h1> -->
    <div style="font-weight: 900;font-size: 16px;">
      @yield('title')
    </div>
    @endif
    <div class="navbar-nav flex-row order-md-last">
      {{-- <div class="change-language me-2">
        <select name="currency" class="currency selectors nice language-bar">
          @foreach(DB::table('currencies')->get() as $currency)
          <option value="{{route('front.currency',$currency->id)}}" {{ Session::has('currency') ? ( Session::get('currency') == $currency->id ? 'selected' : '' ) : (DB::table('currencies')->where('is_default','=',1)->first()->id == $currency->id ? 'selected' : '') }}>
            {{$currency->code}}
          </option>
          @endforeach
        </select>
      </div> --}}
      <div class="my-2 my-md-0 flex-grow-1 flex-md-grow-0 order-first nav-item">
        <form action="{{route('user.transaction')}}" method="get" autocomplete="off" novalidate="">
          <div class="input-icon">
            <span class="input-icon-addon">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"></path><path d="M21 21l-6 -6"></path></svg>
            </span>
            <input type="text"  name="search" class="form-control" placeholder="Search…" aria-label="Search in website">
          </div>
        </form>
      </div>
      <div class="change-language flex-grow-1 flex-md-grow-0 nav-item me-2">
        <select name="language" class="language selectors nice language-bar">
            @foreach(DB::table('languages')->get() as $language)
                <option value="{{route('front.language',$language->id)}}" {{ Session::has('language') ? ( Session::get('language') == $language->id ? 'selected' : '' ) : (DB::table('languages')->where('is_default','=',1)->first()->id == $language->id ? 'selected' : '') }} >
                    {{$language->language}}
                </option>
            @endforeach
        </select>
      </div>
      <a href="?theme=dark" class="nav-link px-0 hide-theme-dark " title="Enable dark mode" data-bs-toggle="tooltip" data-bs-placement="bottom">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z" />
        </svg>
      </a>
      <a href="?theme=light" class="nav-link px-0 hide-theme-light" title="Enable light mode" data-bs-toggle="tooltip" data-bs-placement="bottom">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <circle cx="12" cy="12" r="4" />
          <path d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7" />
        </svg>
      </a>
      @php
        $notifications = DB::table('action_notifications')->where('user_id', auth()->id())->latest()->limit(5)->get();
      @endphp   
      <div class="nav-item dropdown me-3">
        <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" tabindex="-1" aria-label="Show notifications" aria-expanded="false">
          <!-- Download SVG icon from http://tabler-icons.io/i/bell -->
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M10 5a2 2 0 0 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6"></path><path d="M9 17v1a3 3 0 0 0 6 0v-1"></path></svg>
          @if ($notifications->count() > 0)
          <span class="badge bg-red"></span>
          @endif
        </a>
        <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">@lang('Last Notications')</h3>
            </div>
            <div class="list-group list-group-flush list-group-hoverable">
              @if ($notifications->count() == 0)
              <div class="list-group-item">
                <div class="row align-items-center">
                  <div class="col-md-12 text-center">
                    <div class="h3 d-block text-muted text-truncate mt-n1 ms-5 me-5">
                      @lang('No Notications')
                    </div>
                  </div>
                </div>
              </div>
              @else
                @foreach ($notifications as $key => $item )
                  <div class="list-group-item">
                    <div class="row align-items-center">
                      <div class="col text-truncate">
                        <a href="{{route('user.notifications')}}" class="text-body d-block">@lang("Notification") {{__($key + 1)}}</a>
                        <div class="d-block text-muted text-truncate mt-n1">
                          {{__(substr($item->description, 0, 50))}} ...
                        </div>
                      </div>
                    </div>
                  </div>
                @endforeach
              @endif
            </div>
          </div>
        </div>
      </div>
      <div class="nav-item dropdown">
        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
          <span class="avatar avatar-sm" style="background-image: url({{ auth()->user()->photo ? asset('assets/images/'.auth()->user()->photo) : asset('assets/user/img/user.jpg')}})"></span>
          <div class="d-none d-xl-block ps-2">
            <div>{{auth()->user()->company_name ?? auth()->user()->name}}</div>
          </div>
        </a>
        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
          <a href="{{ route('user.profile.index') }}" class="dropdown-item">{{__('Profile')}}</a>
          <a class="dropdown-item" href="{{route('user.notifications')}}">
            {{__('Notifications')}}
          </a>
          <a class="dropdown-item" href="{{route('user.aml.kyc')}}">
            {{__('AML/KYC')}}
          </a>
          <a class="dropdown-item" href="{{route(config('chatify.routes.prefix'))}}">
             {{__('Live Chat')}}
          </a>
          <a class="dropdown-item" href="{{route('user.module.view')}}">
            {{__('Setting')}}
          </a>
          <a class="dropdown-item" href="{{route('user.message.index')}}">
            {{__('Support Tickets')}}
          </a>
          <a href="{{ route('user.logout') }}" class="dropdown-item">{{__('Logout')}}</a>
        </div>
      </div>
    </div>
  </div>
</header>
