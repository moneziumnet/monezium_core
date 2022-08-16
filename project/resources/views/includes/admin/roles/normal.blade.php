@if(Auth::guard('admin')->user()->tenant_id)

  @if(getModule('Sub Institutions management'))
    <li class="nav-item">
    <a class="nav-link" href="{{ route('admin.subinstitution.index') }}">
      <i class="fas fa-chart-line"></i>
      <span>{{ __('Manage Sub Institutions') }}</span>
    </a>
  </li>

  @endif

  @if(getModule('Manage Customers'))
  <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#customer" aria-expanded="true"
      aria-controls="collapseTable">
      <i class="fas fa-user"></i>
      <span>{{  __('Manage Customers') }}</span>
    </a>
    <div id="customer" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
        <a class="collapse-item" href="{{ route('admin.user.index') }}">{{ __('User List') }}</a>
        <a class="collapse-item" href="{{ route('admin.bank.plan.index') }}">{{ __('Pricing Plan') }}</a>
        <!-- <a class="collapse-item" href="{{ route('admin.gs.user.modules') }}">{{ __('User Modules') }}</a> -->
         <a class="collapse-item" href="{{route('admin.kyc.info','user')}}">{{ __('User KYC Info') }}</a>
        <a class="collapse-item" href="{{route('admin.manage.module')}}">{{ __('User KYC Modules') }}</a>
        <a class="collapse-item" href="{{ route('admin.other.banks.index') }}">{{ __('Other Banks') }}</a>
        <!-- <a class="collapse-item" href="{{ route('admin.user.bonus') }}">{{ __('Supervisor Fee') }}</a> -->
      </div>
    </div>
  </li>
  @endif

  {{-- @if(getModule('Contract Management'))
    <li class="nav-item">
    <a class="nav-link" href="{{route('admin.contract.management')}}">
        <i class="fas fa-chart-line"></i>
        <span>{{ __('Manage Contract') }}</span>
    </a>
    </li>
   @endif --}}

  @if(getModule('Loan Management'))
  <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#loan" aria-expanded="true" aria-controls="collapseTable">
      <i class="fas fa-cash-register"></i>
    <span>{{ __('Loan Management') }}</span>
  </a>
    <div id="loan" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
        <a class="collapse-item" href="{{ route('admin.loan.plan.index') }}">{{ __('Loan Plans') }}</a>
        <a class="collapse-item" href="{{ route('admin.loan.index') }}">{{ __('All Loans') }}</a>
        <a class="collapse-item" href="{{ route('admin.loan.pending') }}">{{ __('Pending Loan') }}</a>
        <a class="collapse-item" href="{{ route('admin.loan.running') }}">{{ __('Running Loan') }}</a>
        <a class="collapse-item" href="{{ route('admin.loan.completed') }}">{{ __('Paid Loan') }}</a>
        <a class="collapse-item" href="{{ route('admin.loan.rejected') }}">{{ __('Rejected Loan') }}</a>
      </div>
    </div>
  </li>
  @endif

  @if(getModule('DPS Management'))
  <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#dps" aria-expanded="true" aria-controls="collapseTable">
      <i class="fas fa-warehouse"></i>
    <span>{{ __('DPS Management') }}</span>
    </a>
    <div id="dps" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
        <a class="collapse-item" href="{{ route('admin.dps.plan.index') }}">{{ __('Dps Plans') }}</a>
        <a class="collapse-item" href="{{ route('admin.dps.index') }}">{{ __('All Dps') }}</a>
        <a class="collapse-item" href="{{ route('admin.dps.running') }}">{{ __('Running Dps') }}</a>
        <a class="collapse-item" href="{{ route('admin.dps.matured') }}">{{ __('Matured Dps') }}</a>
      </div>
    </div>
  </li>
  @endif

  @if(getModule('FDR Management'))
  <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#fdr" aria-expanded="true" aria-controls="collapseTable">
      <i class="fas fa-user-shield"></i>
    <span>{{ __('FDR Management') }}</span>
  </a>
    <div id="fdr" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
        <a class="collapse-item" href="{{ route('admin.fdr.plan.index') }}">{{ __('Fdr Plans') }}</a>
        <a class="collapse-item" href="{{ route('admin.fdr.index') }}">{{ __('All Fdr') }}</a>
        <a class="collapse-item" href="{{ route('admin.fdr.running') }}">{{ __('Running Fdr') }}</a>
        <a class="collapse-item" href="{{ route('admin.fdr.closed') }}">{{ __('Closed Fdr') }}</a>
      </div>
    </div>
  </li>
  @endif


  @if(getModule('Management Withdraw'))
  <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#withdraw" aria-expanded="true" aria-controls="collapseTable">
      <i class="fas fa-user"></i>
    <span>{{ __('Management Withdraw') }}</span>
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

  @if(getModule('Manage Escrow'))
  <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#escrow" aria-expanded="true" aria-controls="collapseTable">
      <i class="fas fa-hands-helping"></i>
    <span>{{ __('Manage Escrow') }} @if (isset($disputed) && $disputed > 0) <small class="badge badge-primary mr-4">!</small> @endif</span>
    </a>
    <div id="escrow" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
        {{-- @if (access('manage escrow')) --}}
        <a class="collapse-item" href="{{ route('admin.escrow.manage') }}">{{ __('All Escrow') }}</a>
        {{-- @endif
        @if (access('manage on-hold')) --}}
        <a class="collapse-item" href="{{ route('admin.escrow.onHold') }}">{{ __('On-hold Escrow') }}</a>
        {{-- @endif
        @if (access('manage disputed')) --}}
        <a class="collapse-item {{isset($disputed) && $disputed > 0 ? 'beep beep-sidebar':''}}" href="{{ route('admin.escrow.disputed') }}">{{ __('Disputed Escrows') }}</a>
        {{-- @endif --}}
      </div>
    </div>
  </li>
  @endif

  @if(getModule('Money Transfer'))
  <li class="nav-item">
    <a class="nav-link " href="{{ route('admin.other.banks.transfer.index') }}" >
      <i class="fas fa-exchange-alt"></i>
    <span>{{ __('Bank Transfer') }}</span>
  </a>
  </li>
  @endif

  @if(getModule('Wire Transfer'))
  <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#wiretransfer" aria-expanded="true" aria-controls="collapseTable">
      <i class="fas fa-wallet"></i>
    <span>{{ __('Wire Transfer') }}</span>
  </a>
    <div id="wiretransfer" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
        <a class="collapse-item" href="{{ route('admin.wire.transfer.banks.index') }}">{{ __('Wire Transfer Bank') }}</a>
        <a class="collapse-item" href="{{ route('admin.wire.transfer.index') }}">{{ __('Wire Transfers') }}</a>
      </div>
    </div>
  </li>
  @endif

  @if(getModule('Request Money'))
  <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#requestmoney" aria-expanded="true" aria-controls="collapseTable">
      <i class="fas fa-donate"></i>
    <span>{{ __('Request Money') }}</span>
  </a>
    <div id="requestmoney" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
        <a class="collapse-item" href="{{ route('admin.request.money') }}">{{ __('All Money Request') }}</a>
        <a class="collapse-item" href="{{ route('admin.request.setting.create') }}">{{ __('Money Request Setting') }}</a>
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

  <!-- @if(getModule('Manage Blog'))
  <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#blog" aria-expanded="true"
      aria-controls="collapseTable">
      <i class="fas fa-fw fa-newspaper"></i>
      <span>{{  __('Manage Blog') }}</span>
    </a>
    <div id="blog" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
        <a class="collapse-item" href="{{ route('admin.cblog.index') }}">{{ __('Categories') }}</a>
        <a class="collapse-item" href="{{ route('admin.blog.index') }}">{{ __('Posts') }}</a>
      </div>
    </div>
  </li>
  @endif -->

  @if(getModule('General Setting'))
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
        <a class="collapse-item" href="{{ route('admin.gs.footer') }}">{{ __('Footer') }}</a>
        <a class="collapse-item" href="{{ route('admin.gs.error.banner') }}">{{ __('Error Banner') }}</a>
      </div>
    </div>
  </li>
  @endif

  @if(getModule('Homepage Manage'))
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
  @endif

  @if(getModule('Email Setting'))
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
  @endif

  @if(getModule('Message'))
  <li class="nav-item">
    <a class="nav-link" href="{{ route('admin.user.message') }}">
      <i class="fas fa-comment-alt"></i>
      <span>{{ __('Messages') }}</span></a>
  </li>
  @endif

  @if(getModule('Currency Setting'))
  <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#payment_gateways" aria-expanded="true"
      aria-controls="collapseTable">
      <i class="fas fa-fw fa-newspaper"></i>
      <span>{{  __('Currency Settings') }}</span>
    </a>
    <div id="payment_gateways" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
        <a class="collapse-item" href="{{ route('admin.currency.index') }}">{{ __('Currencies') }}</a>
      </div>
    </div>
  </li>
  @endif


  @if(getModule('Manage KYC Form'))
  <li class="nav-item">
    <a class="nav-link" href="{{route('admin.manage.kyc.user','user')}}">
      <i class="fas fa-child"></i>
      <span>{{ __('Manage KYC Form') }}</span></a>
  </li>
  @endif

  @if(getModule('Language Manage'))
  <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#langs" aria-expanded="true"
      aria-controls="collapseTable">
      <i class="fas fa-language"></i>
      <span>{{  __('Language Settings') }}</span>
    </a>
    <div id="langs" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
        <a class="collapse-item" href="{{route('admin.lang.index')}}">{{ __('Website Language') }}</a>
        <a class="collapse-item" href="{{route('admin.tlang.index')}}">{{ __('Admin Panel Language') }}</a>
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
  </li>
  @endif

  @if(getModule('Seo Tools'))
  <li class="nav-item">
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


@endif


