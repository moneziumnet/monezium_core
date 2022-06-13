


  <!-- <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#customer" aria-expanded="true"
      aria-controls="collapseTable">
      <i class="fas fa-user"></i>
      <span>{{  __('Manage Customers') }}</span>
    </a>
    <div id="customer" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
        <a class="collapse-item" href="{{ route('admin.user.index') }}">{{ __('User List') }}</a>
        <a class="collapse-item" href="{{ route('admin.bank.plan.index') }}">{{ __('Pricing Plan') }}</a>
        <a class="collapse-item" href="{{ route('admin.gs.user.modules') }}">{{ __('User Modules') }}</a>
        <a class="collapse-item" href="{{route('admin.kyc.info','user')}}">{{ __('User KYC Info') }}</a>
        <a class="collapse-item" href="{{route('admin.manage.module')}}">{{ __('User KYC Modules') }}</a>
        <a class="collapse-item" href="{{ route('admin.withdraw.index') }}">{{ __('Withdraw Request') }} @if( DB::table('withdraws')->where('status','pending')->count() > 0)
        <span class="badge badge-sm badge-danger badge-counter">{{ DB::table('withdraws')->where('status','pending')->count() }}</span>@endif</a>
        <a class="collapse-item" href="{{ route('admin-withdraw-method-index') }}">{{ __('WithDraw Method') }}</a>
        <a class="collapse-item" href="{{ route('admin.user.bonus') }}">{{ __('Refferel Bonus') }}</a>
      </div>
    </div>
  </li>

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

  <li class="nav-item">
    <a class="nav-link" href="{{ route('admin.other.banks.index') }}">
      <i class="fas fa-landmark"></i>
      <span>{{ __('Other Banks') }}</span>
    </a>
  </li>
  
  <li class="nav-item">
    <a class="nav-link" href="{{route('admin.manage.charge')}}">
      <i class="fas fa-comments-dollar"></i>
      <span>{{ __('Manage Charges') }}</span>
    </a>
  </li>

  {{-- @if(access('manage escrow') || access('escrow on-hold') || access('escrow disputed')) --}}
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
  {{-- @endif --}}

  <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#moneytransfer" aria-expanded="true" aria-controls="collapseTable">
      <i class="fas fa-exchange-alt"></i>
    <span>{{ __('Money Transfer') }}</span>
  </a>
    <div id="moneytransfer" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
        <a class="collapse-item" href="{{ route('admin.own.banks.transfer.index') }}">{{ __('Own Bank Transfer') }}</a>
        <a class="collapse-item" href="{{ route('admin.other.banks.transfer.index') }}">{{ __('Other Bank Transfer') }}</a>
      </div>
    </div>
  </li>

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

  <li class="nav-item">
    <a class="nav-link" href="{{ route('admin.transactions.index') }}">
      <i class="fas fa-chart-line"></i>
      <span>{{ __('Transactions') }}</span>
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link" href="{{ route('admin.deposits.index') }}">
      <i class="fas fa-piggy-bank"></i>
      <span>{{ __('Deposits') }}</span>
    </a>
  </li> -->

  <!-- <li class="nav-item">
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
  </li> -->

  <!-- <li class="nav-item">{{  __(' Settings') }}</li> -->