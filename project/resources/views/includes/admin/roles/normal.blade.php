{{-- @if(Auth::guard('admin')->user()->tenant_id) --}}

@if(getModule('Sub Institutions management'))
<li class="nav-item">
  <a class="nav-link" href="{{ route('admin.subinstitution.index') }}">
    <i class="fas fa-chart-line"></i>
    <span>{{ __('Manage Sub Institutions') }}</span>
  </a>
</li>

@endif

@if(getModule('Staff Management'))
<li class="nav-item">
  <a class="nav-link" href="{{ route('admin.staff.index') }}">
    <i class="fas fa-user-friends"></i>
    <span>{{ __('Staff Management') }}</span></a>
</li>
@endif

@php
  $profile_module_list = ["Information" , "Accounts" , "Documents" , "Setting" , "Pricing Plan" , "Customer Transactions" , "Banks" , "Modules" , "IBAN" , "Contract" , "Merchant Shop" , "AML/KYC" , "Beneficiary" , "Layer" , "Login History"];
  $current = '';
  foreach ($profile_module_list as $key => $value) {
    if(getModule($value)) {
      $current = $value;
    }
  }
@endphp
@if(getModule('Manage Customers') || !empty($current))
<li class="nav-item">
  <a class="nav-link" href="{{ route('admin.user.index') }}">
    <i class="fas fa-user"></i>
    <span>{{ __('Manage Customers') }}</span></a>
</li>
@endif


@if(getModule('Manage Pricing Plan'))
<li class="nav-item">
  <a class="nav-link" href="{{ route('admin.bank.plan.index') }}">
    <i class="fas fa-money-bill"></i>
    <span>{{ __('Manage Pricing Plan') }}</span></a>
</li>
@endif

@if(getModule('KYC Management'))
<li class="nav-item">
  <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#kyc_manage" aria-expanded="true" aria-controls="collapseTable">
    <i class="fas fa-child"></i>
    <span>{{ __('AML/KYC Management') }}</span>
  </a>
  <div id="kyc_manage" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
    <div class="bg-white py-2 collapse-inner rounded">
      {{-- <a class="collapse-item" href="{{route('admin.manage.kyc.user','user')}}">{{ __('KYC Form') }}</a> --}}
      <a class="collapse-item" href="{{route('admin.manage.kyc.index')}}">{{ __('KYC Form') }}</a>
      <a class="collapse-item" href="{{ route('admin.manage.module') }}">{{ __('KYC Modules') }}</a>
    </div>
  </div>
</li>
@endif

@if(getModule('Crypto Management'))
<li class="nav-item">
  <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#crypto_manage" aria-expanded="true" aria-controls="collapseTable">
    <i class="fas fa-user"></i>
    <span>{{ __('Crypto Management') }}</span>
  </a>
  <div id="crypto_manage" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
    <div class="bg-white py-2 collapse-inner rounded">
      <a class="collapse-item" href="{{ route('admin.crypto.currency.index') }}">{{ __('Currencies') }}</a>
      <a class="collapse-item" href="{{ route('admin.deposits.crypto.index') }}">{{ __('Crypto Deposits') }}</a>
      <a class="collapse-item" href="{{ route('admin.withdraws.crypto.index') }}">{{ __('Crypto Withdraws') }}</a>
    </div>
  </div>
</li>
@endif


@if(getModule('Deposits'))
<li class="nav-item">
  <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#bank" aria-expanded="true" aria-controls="collapseTable">
    <i class="fas fa-piggy-bank"></i>
    <span>{{ __('Deposits') }}</span>
  </a>
  <div id="bank" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
    <div class="bg-white py-2 collapse-inner rounded">
      <a class="collapse-item" href="{{ route('admin.deposits.bank.index') }}">{{ __('Bank Deposit') }}</a>
      <a class="collapse-item" href="{{ route('admin.deposits.index') }}">{{ __('Gateway Deposit') }}</a>
    </div>
  </div>
</li>
@endif

@if(getModule('Withdraw'))
<li class="nav-item">
  <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#withdraw" aria-expanded="true" aria-controls="collapseTable">
    <i class="fas fa-user"></i>
    <span>{{ __('Withdraw') }}</span>
  </a>
  <div id="withdraw" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
    <div class="bg-white py-2 collapse-inner rounded">
      <a class="collapse-item" href="{{ route('admin.withdraw.pending') }}">{{ __('Pending Withdraws') }}
        @if( DB::table('withdrawals')->where('status','pending')->count() > 0)
        <span class="badge badge-sm badge-danger badge-counter">{{ DB::table('withdrawals')->where('status','pending')->count() }}</span>
        @endif
      </a>
      <a class="collapse-item" href="{{ route('admin.withdraw.accepted') }}">{{ __('Accepted Withdraws') }}</a>
      <a class="collapse-item" href="{{ route('admin.withdraw.rejected') }}">{{ __('Rejected Withdraws') }}</a>
    </div>
  </div>
</li>
@endif



@if(getModule('Request Money'))
<li class="nav-item">
  <a class="nav-link" href="{{ route('admin.request.money') }}">
    <i class="fas fa-donate"></i>
    <span>{{ __('Request Money') }}</span>
  </a>
</li>
@endif


@if(getModule('Bank Transfer'))
<li class="nav-item">
  <a class="nav-link " href="{{ route('admin.other.banks.transfer.index') }}">
    <i class="fas fa-exchange-alt"></i>
    <span>{{ __('Bank Transfer') }}</span>
  </a>
</li>
@endif

@if(getModule('Report'))
<li class="nav-item">
  <a class="nav-link collapsed" href="{{ route('admin.report.transaction.index') }}" data-toggle="collapse" data-target="#bankreport" aria-expanded="true" aria-controls="collapseTable">
    <i class="fas fa-flag"></i>
    <span>{{ __('Report') }}</span>
  </a>
  <div id="bankreport" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
    <div class="bg-white py-2 collapse-inner rounded">
      <a class="collapse-item" href="{{ route('admin.report.transaction.index') }}">{{ __('Statistic') }}</a>
      <a class="collapse-item" href="{{ route('admin.report.transaction.summary')}}">{{ __('Summary Fee') }}</a>
    </div>
  </div>
</li>
@endif

@if(getModule('Transactions'))
<li class="nav-item">
  <a class="nav-link" href="{{ route('admin.transactions.index') }}">
    <i class="fas fa-chart-line"></i>
    <span>{{ __('Transactions') }}</span>
  </a>
</li>
@endif

@if(getModule('Notification'))
<li class="nav-item">
  <a class="nav-link" href="{{ route('admin.actionnotification.index') }}">
    <i class="fas fa-bell"></i>
    <span>{{ __('Notification') }}</span>
  </a>
</li>
@endif

@if(getModule('Manage Blog'))
<li class="nav-item">
  <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#blog" aria-expanded="true" aria-controls="collapseTable">
    <i class="fas fa-fw fa-newspaper"></i>
    <span>{{ __('Manage Blog') }}</span>
  </a>
  <div id="blog" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
    <div class="bg-white py-2 collapse-inner rounded">
      <a class="collapse-item" href="{{ route('admin.cblog.index') }}">{{ __('Categories') }}</a>
      <a class="collapse-item" href="{{ route('admin.blog.index') }}">{{ __('Posts') }}</a>
    </div>
  </div>
</li>
@endif

@if(getModule('Support Ticket'))
<li class="nav-item">
  <a class="nav-link" href="{{ route('admin.user.message') }}">
    <i class="fas fa-comment-alt"></i>
    <span>{{ __('Support Ticket') }}</span></a>
</li>
@endif

@if(Auth::guard('admin')->user()->role == 'admin')
<li class="nav-item">
  <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#system_setting" aria-expanded="true" aria-controls="collapseTable">
    <i class="fas fa-fw fa-cogs"></i>
    <span>{{ __('System Settings') }}</span>
  </a>
  <div id="system_setting" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
    <div class="bg-white py-2 collapse-inner rounded">
      @if(getModule('Email Setting'))
      <a class="collapse-item nav-link sub-nav-link" href="#" data-toggle="collapse" data-target="#email_settings" aria-expanded="true" aria-controls="collapseTable">
        <span>{{ __('Email Settings') }}</span>
      </a>
      <div id="email_settings" class="collapse" aria-labelledby="headingTable" data-parent="#system_setting">
        <div class="bg-white py-2 collapse-inner rounded">
          <a class="collapse-item" href="{{ route('admin.mail.index') }}">{{ __('Email Template') }}</a>
          <a class="collapse-item" href="{{ route('admin.mail.config') }}">{{ __('Email Configurations') }}</a>
          <a class="collapse-item" href="{{ route('admin.group.show') }}">{{ __('Group Email') }}</a>
        </div>
      </div>
      @endif

      <a class="collapse-item" href="{{ route('admin.system.accounts') }}">{{ __('System Accounts') }}</a>
      <a class="collapse-item" href="{{ route('admin.gs.currencyapi') }}">{{ __('API Settings') }}</a>

      @if(getModule('Currency Setting'))
      <a class="collapse-item" href="{{ route('admin.currency.index') }}">{{ __('Currencies') }}</a>
      @endif

      @if(getModule('Language Manage'))
      <a class="collapse-item nav-link sub-nav-link" href="#" data-toggle="collapse" data-target="#langs" aria-expanded="true" aria-controls="collapseTable">
        <span>{{ __('Language Settings') }}</span>
      </a>
      <div id="langs" class="collapse" aria-labelledby="headingTable" data-parent="#system_setting">
        <div class="bg-white py-2 collapse-inner rounded">
          <a class="collapse-item" href="{{route('admin.lang.index')}}">{{ __('Website Language') }}</a>
          <a class="collapse-item" href="{{route('admin.tlang.index')}}">{{ __('Admin Panel Language') }}</a>
        </div>
      </div>
      @endif
    </div>
  </div>
</li>
@endif

@if(getModule('General Setting'))
<li class="nav-item">
  <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#general" aria-expanded="true" aria-controls="collapseTable">
    <i class="fas fa-igloo"></i>
    <span>{{ __('General Settings') }}</span>
  </a>
  <div id="general" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
    <div class="bg-white py-2 collapse-inner rounded">
      <a class="collapse-item" href="{{ route('admin.gs.contents') }}">{{ __('Website Contents') }}</a>
      <a class="collapse-item" href="{{ route('admin.gs.logo') }}">{{ __('Logo') }}</a>
      <a class="collapse-item" href="{{ route('admin.gs.fav') }}">{{ __('Favicon') }}</a>
      <a class="collapse-item" href="{{ route('admin.gs.load') }}">{{ __('Loader') }}</a>
      <a class="collapse-item" href="{{ route('admin.gs.breadcumb') }}">{{ __('Breadcumb Banner') }}</a>
      <a class="collapse-item" href="{{ route('admin.gs.footer') }}">{{ __('Footer') }}</a>
      <a class="collapse-item" href="{{ route('admin.gs.error.banner') }}">{{ __('Error Banner') }}</a>
    </div>
  </div>
</li>
@endif


@if(getModule('Home page Setting'))
<li class="nav-item">
  <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#homepage" aria-expanded="true" aria-controls="collapseTable">
    <i class="fas fa-igloo"></i>
    <span>{{ __('Home Page Setting') }}</span>
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
@endif

@if(getModule('Fonts'))
<li class="nav-item">
  <a class="nav-link" href="{{ route('admin.font.index') }}">
    <i class="fas fa-font"></i>
    <span>{{ __('Fonts') }}</span></a>
</li>
@endif

@if(getModule('Menupage Setting'))
<li class="nav-item">
  <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#menu" aria-expanded="true" aria-controls="collapseTable">
    <i class="fas fa-fw fa-edit"></i>
    <span>{{ __('Menu Page Settings') }}</span>
  </a>
  <div id="menu" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
    <div class="bg-white py-2 collapse-inner rounded">
      <a class="collapse-item" href="{{ route('admin.ps.contact') }}">{{ __('Contact Us Page') }}</a>
      <a class="collapse-item" href="{{ route('admin.page.index') }}">{{ __('Other Pages') }}</a>
      <a class="collapse-item" href="{{ route('admin.faq.index') }}">{{ __('FAQ Page') }}</a>
    </div>
  </div>
</li>
@endif

@if(getModule('Seo Tools'))
<li class="nav-item">
  <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#seoTools" aria-expanded="true" aria-controls="collapseTable">
    <i class="fas fa-wrench"></i>
    <span>{{ __('SEO Tools') }}</span>
  </a>
  <div id="seoTools" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
    <div class="bg-white py-2 collapse-inner rounded">
      <a class="collapse-item" href="{{route('admin.seotool.analytics')}}">{{ __('Google Analytics') }}</a>
      <a class="collapse-item" href="{{route('admin.seotool.keywords')}}">{{ __('Website Meta Keywords') }}</a>
      <a class="collapse-item" href="{{route('admin.social.index')}}">{{ __('Social Links') }}</a>
    </div>
  </div>
</li>
@endif

@if(getModule('Sitemaps'))
<li class="nav-item">
  <a class="nav-link" href="{{ route('admin.sitemap.index') }}">
    <i class="fa fa-sitemap"></i>
    <span>{{ __('Sitemaps') }}</span></a>
</li>
@endif

@if(getModule('Subscribers'))
<li class="nav-item">
  <a class="nav-link" href="{{ route('admin.subs.index') }}">
    <i class="fas fa-fw fa-users-cog"></i>
    <span>{{ __('Subscribers') }}</span></a>
</li>
@endif


@if(Auth::guard('admin')->user()->role == 'staff')
<li class="nav-item">
  <a class="nav-link" href="{{ route('admin-staff-telegram') }}">
    <i class="fab fa-telegram"></i>
    <span>{{ __('Staff Telegram') }}</span></a>
</li>
@endif


{{-- @endif --}}
