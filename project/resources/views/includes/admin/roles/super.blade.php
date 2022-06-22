  <!-- <li class="nav-item">
    <a class="nav-link" href="{{ route('admin.gs.menubuilder') }}">
      <i class="fas fa-compass"></i>
      <span>{{ __('Menu Builder') }}</span></a>
  </li> -->

  <li class="nav-item">
    <a class="nav-link" href="{{ route('admin.requestdomain.index') }}">
      <i class="fas fa-landmark"></i>
      <span>{{ __('Domain management') }}</span>
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link" href="{{ route('admin.institution.index') }}">
      <i class="fas fa-fw fa-users"></i>
      <span>{{ __('Institutions management') }}</span>
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link" href="{{ route('admin.role.index') }}">
      <i class="fas fa-cash-register"></i>
      <span>{{ __('Module management') }}</span>
    </a>
  </li>


  <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTable1" aria-expanded="true"
      aria-controls="collapseTable">
      <i class="fas fa-fw fa-cogs"></i>
      <span>{{  __('General Settings') }}</span>
    </a>
    <div id="collapseTable1" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
        <a class="collapse-item" href="{{ route('admin.gs.logo') }}">{{ __('Logo') }}</a>
        <a class="collapse-item" href="{{ route('admin.gs.fav') }}">{{ __('Favicon') }}</a>
        <a class="collapse-item" href="{{ route('admin.gs.load') }}">{{ __('Loader') }}</a>
        <a class="collapse-item" href="{{ route('admin.gs.breadcumb') }}">{{ __('Breadcumb Banner') }}</a>
        <a class="collapse-item" href="{{ route('admin.gs.contents') }}">{{ __('Website Contents') }}</a>
        <a class="collapse-item" href="{{ route('admin.gs.error.banner') }}">{{ __('Error Banner') }}</a>
      </div>
    </div>
  </li>

  <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#homepage" aria-expanded="true"
    aria-controls="collapseTable">
    <i class="fas fa-igloo"></i>
    <span>{{ __('Home Page Manage') }}</span>
  </a>
    <div id="homepage" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
        <a class="collapse-item" href="{{ route('admin.ps.hero') }}">{{ __('Hero Section') }}</a>
        <a class="collapse-item" href="{{ route('admin.feature.index') }}">{{ __('Feature Section') }}</a>
        <a class="collapse-item" href="{{ route('admin.ps.about') }}">{{ __('About Us Section') }}</a>
        <a class="collapse-item" href="{{ route('admin.service.index') }}">{{ __('Service Section') }}</a>
        <a class="collapse-item" href="{{ route('admin.account.process.index') }}">{{ __('Account Register Process') }}</a>
        <a class="collapse-item" href="{{ route('admin.ps.account') }}">{{ __('Strategy Section') }}</a>
        <a class="collapse-item" href="{{ route('admin.ps.apps') }}">{{ __('Apps Section') }}</a>
        <a class="collapse-item" href="{{ route('admin.review.index') }}">{{ __('Testimonial Section') }}</a>
        <a class="collapse-item" href="{{ route('admin.counter.index') }}">{{ __('Counter Section') }}</a>
        <a class="collapse-item" href="{{ route('admin.ps.quick') }}">{{ __('Quick Start Section') }}</a>
        <a class="collapse-item" href="{{ route('admin.ps.heading') }}">{{ __('Section Heading') }}</a>
      </div>
    </div>
  </li>

  <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#langs" aria-expanded="true"
      aria-controls="collapseTable">
      <i class="fas fa-language"></i>
      <span>{{  __('Language Manage') }}</span>
    </a>
    <div id="langs" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
        <a class="collapse-item" href="{{route('admin.lang.index')}}">{{ __('Website Language') }}</a>
        <a class="collapse-item" href="{{route('admin.tlang.index')}}">{{ __('Admin Panel Language') }}</a>
      </div>
    </div>
  </li>
  

  <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#email_settings" aria-expanded="true"
      aria-controls="collapseTable">
      <i class="fa fa-envelope"></i>
      <span>{{  __('Email Settings') }}</span>
    </a>
    <div id="email_settings" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
        <a class="collapse-item" href="{{ route('admin.mail.index') }}">{{ __('Email Template') }}</a>
        <a class="collapse-item" href="{{ route('admin.mail.config') }}">{{ __('Email Configurations') }}</a>
        <a class="collapse-item" href="{{ route('admin.group.show') }}">{{ __('Group Email') }}</a>
      </div>
    </div>
  </li>


  <!-- <li class="nav-item">
    <a class="nav-link" href="{{ route('admin.user.message') }}">
      <i class="fas fa-vote-yea"></i>
      <span>{{ __('Messages') }}</span></a>
  </li> -->

  <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#payment_gateways" aria-expanded="true"
      aria-controls="collapseTable">
      <i class="fas fa-fw fa-newspaper"></i>
      <span>{{  __('Payment Settings') }}</span>
    </a>
    <div id="payment_gateways" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
        <a class="collapse-item" href="{{ route('admin.currency.index') }}">{{ __('Currencies') }}</a>
        <a class="collapse-item" href="{{ route('admin.payment.index') }}">{{  __('Payment Gateways')  }}</a>
      </div>
    </div>
  </li>

  <!-- <li class="nav-item">
    <a class="nav-link" href="{{route('admin.manage.kyc.user','user')}}">
      <i class="fas fa-child"></i>
      <span>{{ __('Manage KYC Form') }}</span></a>
  </li> -->



  
  <!-- <li class="nav-item">
    <a class="nav-link" href="{{ route('admin.font.index') }}">
      <i class="fas fa-font"></i>
      <span>{{ __('Fonts') }}</span></a>
  </li> -->

  <!-- <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#menu" aria-expanded="true"
      aria-controls="collapseTable">
      <i class="fas fa-fw fa-edit"></i>
      <span>{{  __('Menu Page Settings') }}</span>
    </a>
    <div id="menu" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
        <a class="collapse-item" href="{{ route('admin.ps.contact') }}">{{ __('Contact Us Page') }}</a>
        <a class="collapse-item" href="{{ route('admin.page.index') }}">{{ __('Other Pages') }}</a>
        <a class="collapse-item" href="{{ route('admin.faq.index') }}">{{ __('FAQ Page') }}</a>
      </div>
    </div>
  </li> -->


  <!-- <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#seoTools" aria-expanded="true"
      aria-controls="collapseTable">
      <i class="fas fa-wrench"></i>
      <span>{{  __('SEO Tools') }}</span>
    </a>
    <div id="seoTools" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
        <a class="collapse-item" href="{{route('admin.seotool.analytics')}}">{{ __('Google Analytics') }}</a>
        <a class="collapse-item" href="{{route('admin.seotool.keywords')}}">{{ __('Website Meta Keywords') }}</a>
        <a class="collapse-item" href="{{route('admin.social.index')}}">{{ __('Social Links') }}</a>
      </div>
    </div>
  </li> -->

  <!-- <li class="nav-item">
    <a class="nav-link" href="{{ route('admin.sitemap.index') }}">
      <i class="fa fa-sitemap"></i>
      <span>{{ __('Sitemaps') }}</span></a>
  </li> -->

  <!-- <li class="nav-item">
    <a class="nav-link" href="{{ route('admin.subs.index') }}">
      <i class="fas fa-fw fa-users-cog"></i>
      <span>{{ __('Subscribers') }}</span></a>
  </li> -->

  <li class="nav-item">
    <a class="nav-link" href="{{ route('admin.cache.clear') }}">
      <i class="fas fa-sync"></i>
      <span>{{ __('Clear Cache') }}</span></a>
  </li>

  <!-- <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#sactive" aria-expanded="true"
      aria-controls="collapseTable">
      <i class="fas fa-fw fa-at"></i>
      <span>{{  __('System Activation') }}</span>
    </a>
    <div id="sactive" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
        <a class="collapse-item" href="{{route('admin-activation-form')}}">{{ __('Activation') }}</a>
        <a class="collapse-item" href="{{route('admin-generate-backup')}}">{{ __('Generate Backup') }}</a>
      </div>
    </div>
  </li> -->


