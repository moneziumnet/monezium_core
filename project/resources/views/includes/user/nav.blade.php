<div class="navbar-expand-xl">
  <div class="collapse navbar-collapse" id="navbar-menu">
    <div class="navbar navbar-light">
      <div class="container-xl">
        <ul class="navbar-nav">
          <li class="nav-item {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
            <a class="nav-link" href="{{route('user.dashboard')}}">
              <span class="nav-link-icon d-md-none d-lg-inline-block">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                  <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                  <polyline points="5 12 3 12 12 3 21 12 19 12" />
                  <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
                  <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" />
                </svg>
              </span>
              <span class="nav-link-title">
                @lang('Home')
              </span>
            </a>
          </li>

          @php
          $modules = explode(" , ", auth()->user()->section);
          $count = count($modules);
          @endphp

          @if ($count < 8)
          @if (in_array('Loan',$modules)) <li class="nav-item dropdown {{ request()->routeIs('user.loans.plan') || request()->routeIs('user.loans.index') || request()->routeIs('user.loans.pending') || request()->routeIs('user.loans.paid') || request()->routeIs('user.loans.rejected') ? 'active' : '' }}">
            <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
              <span class="nav-link-icon d-md-none d-lg-inline-block">
                <i class="fas fa-cash-register"></i>
              </span>
              <span class="nav-link-title">
                {{__('Loan')}}
              </span>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="{{route('user.loans.plan')}}">
                {{__('Loan Plan')}}
              </a>

              <a class="dropdown-item" href="{{route('user.loans.index')}}">
                {{__('All Loans')}}
              </a>

              <a class="dropdown-item" href="{{route('user.loans.pending')}}">
                {{__('Pending Loans')}}
              </a>

              <a class="dropdown-item" href="{{route('user.loans.running')}}">
                {{__('Running Loans')}}
              </a>

              <a class="dropdown-item" href="{{route('user.loans.paid')}}">
                {{__('Paid Loans')}}
              </a>

              <a class="dropdown-item" href="{{route('user.loans.rejected')}}">
                {{__('Rejected Loans')}}
              </a>
            </div>
            </li>
            @endif

            @if (in_array('DPS',$modules))
            <li class="nav-item dropdown {{ request()->routeIs('user.dps.plan') || request()->routeIs('user.dps.index') || request()->routeIs('user.dps.running') || request()->routeIs('user.dps.matured') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-warehouse"></i>
                </span>
                <span class="nav-link-title">
                  {{__('DPS')}}
                </span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{route('user.dps.plan')}}">
                  {{__('Dps Plan')}}
                </a>

                <a class="dropdown-item" href="{{route('user.dps.index')}}">
                  {{__('All dps')}}
                </a>

                <a class="dropdown-item" href="{{route('user.dps.running')}}">
                  {{__('Running dps')}}
                </a>

                <a class="dropdown-item" href="{{route('user.dps.matured')}}">
                  {{__('Matured dps')}}
                </a>

              </div>
            </li>
            @endif

            @if (in_array('FDR',$modules))
            <li class="nav-item dropdown {{ request()->routeIs('user.fdr.plan') || request()->routeIs('user.fdr.index') || request()->routeIs('user.fdr.running') || request()->routeIs('user.fdr.closed') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-user-shield"></i>
                </span>
                <span class="nav-link-title">
                  {{__('FDR')}}
                </span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{route('user.fdr.plan')}}">
                  {{__('Fdr Plan')}}
                </a>

                <a class="dropdown-item" href="{{route('user.fdr.index')}}">
                  {{__('All Fdr')}}
                </a>

                <a class="dropdown-item" href="{{route('user.fdr.running')}}">
                  {{__('Running Fdr')}}
                </a>

                <a class="dropdown-item" href="{{route('user.fdr.closed')}}">
                  {{__('Closed Fdr')}}
                </a>
              </div>
            </li>
            @endif

            @if (in_array('External Payment',$modules))
            <li class="nav-item dropdown {{ request()->routeIs('user.deposit.index', 'user.wire.transfer.index', 'user.other.bank', 'user.beneficiaries.index', 'tranfer.logs.index','user.withdraw.index') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-hand-holding-usd"></i>
                </span>
                <span class="nav-link-title">
                  {{__('External payment')}}
                </span>
              </a>
              <div class="dropdown-menu">
                @if (in_array('Withdraw',$modules))
                <a class="dropdown-item" href="{{route('user.withdraw.index')}}">
                  {{__('Withdraw')}}
                </a>
                @endif
                @if (in_array('Deposit',$modules))
                <a class="dropdown-item" href="{{route('user.deposit.index')}}">
                  {{__('Deposit')}}
                </a>
                @endif
                @if (in_array('Wire Transfer',$modules))
                <a class="dropdown-item" href="{{route('user.wire.transfer.index')}}">
                  {{__('Wire Transfer')}}
                </a>
                @endif
                @if (in_array('Bank Transfer',$modules))
                <a class="dropdown-item" href="{{route('user.other.bank')}}">
                  {{__('Other Bank Transfer')}}
                </a>
                <a class="dropdown-item" href="{{route('user.beneficiaries.index')}}">
                  {{__('Beneficiary Manage')}}
                </a>
                <a class="dropdown-item" href="{{ route('tranfer.logs.index') }}">
                  {{__('Transfer History')}}
                </a>
                @endif
              </div>
            </li>
            @endif

            {{-- @if (in_array('Request Money',$modules)) --}}
            <li class="nav-item dropdown {{ request()->routeIs('user.money.request.index','user.request.money.receive', 'send.money.create') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-file-signature"></i>
                </span>
                <span class="nav-link-title">
                  {{__('Request Money')}}
                </span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{route('send.money.create')}}">
                  {{__('Send Money')}}
                </a>
                <a class="dropdown-item" href="{{route('user.money.request.index')}}">
                  {{__('Send Request Money')}}
                </a>
                <a class="dropdown-item" href="{{route('user.request.money.receive')}}">
                  {{__('Receive Request Money')}}
                </a>
              </div>
            </li>
            {{-- @endif --}}


            @if (in_array('Exchange Money',$modules))
            <li class="nav-item {{ request()->routeIs('user.exchange.money') ? 'active' : '' }}">
              <a class="nav-link" href="{{route('user.exchange.money')}}">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-exchange-alt"></i>
                </span>
                <span class="nav-link-title">
                  {{__('Exchange')}}
                </span>
              </a>
            </li>
            @endif

            @if (in_array('Voucher',$modules))
            <li class="nav-item dropdown {{ request()->routeIs('user.vouchers', 'user.reedem.voucher','user.reedem.history') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-file-signature"></i>
                </span>
                <span class="nav-link-title">
                  {{__('Vouchers')}}
                </span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{route('user.vouchers')}}">
                  {{__('My Vouchers')}}
                </a>

                <a class="dropdown-item" href="{{route('user.create.voucher')}}">
                  {{__('Create Voucher')}}
                </a>

                <a class="dropdown-item" href="{{route('user.reedem.voucher')}}">
                  {{__('Redeem Voucher')}}
                </a>

                <a class="dropdown-item" href="{{route('user.reedem.history')}}">
                  {{__('Redeemed History')}}
                </a>
              </div>
            </li>
            @endif

            @if(check_user_type(4))
            <li class="nav-item dropdown {{ request()->routeIs('user.merchant.qr') || request()->routeIs('user.merchant.api.key.form') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-users"></i>
                </span>
                <span class="nav-link-title">
                  {{__('Merchant')}}
                </span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{route('user.merchant.qr')}}">
                  {{__('QR Code')}}
                </a>

                <a class="dropdown-item" href="{{route('user.merchant.api.key.form')}}">
                  {{__('API Access Key')}}
                </a>

              </div>
            </li>
            @endif

            @if(check_user_type(3))
            <li class="nav-item dropdown {{ request()->routeIs('user.referral.invite-user') || request()->routeIs('user.referral.index') || request()->routeIs('user.referral.commissions') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-box"></i>
                </span>
                <span class="nav-link-title">
                  {{__('Supervisor')}}
                </span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{route('user.referral.invite-user')}}">
                  {{__('Invite User')}}
                </a>

                <a class="dropdown-item" href="{{route('user.referral.index')}}">
                  {{__('Referred Users')}}
                </a>

                <a class="dropdown-item" href="{{route('user.referral.commissions')}}">
                  {{__('Referral Commissions')}}
                </a>

              </div>
            </li>
            @endif

            @if (in_array('Invoice',$modules))
            <li class="nav-item dropdown {{ request()->routeIs('user.invoice.create') || request()->routeIs('user.invoice.index') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-file-invoice"></i>
                </span>
                <span class="nav-link-title">
                  {{__('Invoice')}}
                </span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{route('user.invoice.index')}}">
                  {{__('Invoices')}}
                </a>
                <a class="dropdown-item" href="{{route('user.invoice.create')}}">
                  {{__('Create Invoice')}}
                </a>


              </div>
            </li>
            @endif

            @if (in_array('Escrow',$modules))
            <li class="nav-item dropdown {{ request()->routeIs('user.escrow.create') || request()->routeIs('user.escrow.index') || request()->routeIs('user.escrow.pending') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-hands-helping"></i>
                </span>
                <span class="nav-link-title">
                  {{__('Escrow')}}
                </span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{route('user.escrow.create')}}">
                  {{__('Make Escrow')}}
                </a>
                <a class="dropdown-item" href="{{route('user.escrow.index')}}">
                  {{__('My Escrow')}}
                </a>

                <a class="dropdown-item" href="{{route('user.escrow.pending')}}">
                  {{__('Pending Escrows')}}
                </a>
              </div>
            </li>
            @endif
            <li class="nav-item dropdown {{ request()->routeIs('user.transaction') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-suitcase"></i>
                </span>
                <span class="nav-link-title">
                  {{__('More')}}
                </span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{route('user.transaction')}}">
                  {{__('Transactions')}}
                </a>
              </div>
            </li>
            @else
            @if (in_array('Loan',$modules))
            <li class="nav-item dropdown {{ request()->routeIs('user.loans.plan') || request()->routeIs('user.loans.index') || request()->routeIs('user.loans.pending') || request()->routeIs('user.loans.paid') || request()->routeIs('user.loans.rejected') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-cash-register"></i>
                </span>
                <span class="nav-link-title">
                  {{__('Loan')}}
                </span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{route('user.loans.plan')}}">
                  {{__('Loan Plan')}}
                </a>

                <a class="dropdown-item" href="{{route('user.loans.index')}}">
                  {{__('All Loans')}}
                </a>

                <a class="dropdown-item" href="{{route('user.loans.pending')}}">
                  {{__('Pending Loans')}}
                </a>

                <a class="dropdown-item" href="{{route('user.loans.running')}}">
                  {{__('Running Loans')}}
                </a>

                <a class="dropdown-item" href="{{route('user.loans.paid')}}">
                  {{__('Paid Loans')}}
                </a>

                <a class="dropdown-item" href="{{route('user.loans.rejected')}}">
                  {{__('Rejected Loans')}}
                </a>
              </div>
            </li>
            @endif

            @if (in_array('DPS',$modules))
            <li class="nav-item dropdown {{ request()->routeIs('user.dps.plan') || request()->routeIs('user.dps.index') || request()->routeIs('user.dps.running') || request()->routeIs('user.dps.matured') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-warehouse"></i>
                </span>
                <span class="nav-link-title">
                  {{__('DPS')}}
                </span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{route('user.dps.plan')}}">
                  {{__('Dps Plan')}}
                </a>

                <a class="dropdown-item" href="{{route('user.dps.index')}}">
                  {{__('All dps')}}
                </a>

                <a class="dropdown-item" href="{{route('user.dps.running')}}">
                  {{__('Running dps')}}
                </a>

                <a class="dropdown-item" href="{{route('user.dps.matured')}}">
                  {{__('Matured dps')}}
                </a>

              </div>
            </li>
            @endif

            @if (in_array('FDR',$modules))
            <li class="nav-item dropdown {{ request()->routeIs('user.fdr.plan') || request()->routeIs('user.fdr.index') || request()->routeIs('user.fdr.running') || request()->routeIs('user.fdr.closed') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-user-shield"></i>
                </span>
                <span class="nav-link-title">
                  {{__('FDR')}}
                </span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{route('user.fdr.plan')}}">
                  {{__('Fdr Plan')}}
                </a>

                <a class="dropdown-item" href="{{route('user.fdr.index')}}">
                  {{__('All Fdr')}}
                </a>

                <a class="dropdown-item" href="{{route('user.fdr.running')}}">
                  {{__('Running Fdr')}}
                </a>

                <a class="dropdown-item" href="{{route('user.fdr.closed')}}">
                  {{__('Closed Fdr')}}
                </a>
              </div>
            </li>
            @endif

            @if (in_array('External Payment',$modules))
            <li class="nav-item dropdown {{ request()->routeIs('user.deposit.index', 'user.wire.transfer.index', 'user.other.bank', 'user.beneficiaries.index', 'tranfer.logs.index','user.withdraw.index') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-hand-holding-usd"></i>
                </span>
                <span class="nav-link-title">
                  {{__('External payment')}}
                </span>
              </a>
              <div class="dropdown-menu">
                @if (in_array('Withdraw',$modules))
                <a class="dropdown-item" href="{{route('user.withdraw.index')}}">
                  {{__('Withdraw')}}
                </a>
                @endif
                @if (in_array('Deposit',$modules))
                <a class="dropdown-item" href="{{route('user.deposit.index')}}">
                  {{__('Deposit (Payment Gateway)')}}
                </a>
                <a class="dropdown-item" href="{{route('user.depositbank.index')}}">
                    {{__('Deposit (Bank)')}}
                  </a>
                @endif
                @if (in_array('Wire Transfer',$modules))
                <a class="dropdown-item" href="{{route('user.wire.transfer.index')}}">
                  {{__('Wire Transfer')}}
                </a>
                @endif
                @if (in_array('Bank Transfer',$modules))
                <a class="dropdown-item" href="{{route('user.other.bank')}}">
                  {{__('Other Bank Transfer')}}
                </a>
                <a class="dropdown-item" href="{{route('user.beneficiaries.index')}}">
                  {{__('Beneficiary Manage')}}
                </a>
                <a class="dropdown-item" href="{{ route('tranfer.logs.index') }}">
                  {{__('Transfer History')}}
                </a>
                @endif
              </div>
            </li>
            @endif

            @if (in_array('Request Money',$modules))
            <li class="nav-item dropdown {{ request()->routeIs('user.money.request.index','user.request.money.receive', 'send.money.create') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-file-signature"></i>
                </span>
                <span class="nav-link-title">
                  {{__('Request')}}
                </span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{route('send.money.create')}}">
                  {{__('Send Money')}}
                </a>
                <a class="dropdown-item" href="{{route('user.money.request.index')}}">
                  {{__('Send Request Money')}}
                </a>
                <a class="dropdown-item" href="{{route('user.request.money.receive')}}">
                  {{__('Receive Request Money')}}
                </a>
              </div>
            </li>
            @endif

            @if (in_array('Voucher',$modules))
            <li class="nav-item dropdown {{ request()->routeIs('user.vouchers', 'user.reedem.voucher','user.reedem.history') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-file-signature"></i>
                </span>
                <span class="nav-link-title">
                  {{__('Vouchers')}}
                </span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{route('user.vouchers')}}">
                  {{__('My Vouchers')}}
                </a>

                <a class="dropdown-item" href="{{route('user.create.voucher')}}">
                  {{__('Create Voucher')}}
                </a>

                <a class="dropdown-item" href="{{route('user.reedem.voucher')}}">
                  {{__('Redeem Voucher')}}
                </a>

                <a class="dropdown-item" href="{{route('user.reedem.history')}}">
                  {{__('Redeemed History')}}
                </a>
              </div>
            </li>
            @endif

            @if(check_user_type(4))
            <li class="nav-item dropdown {{ request()->routeIs('user.merchant.qr') || request()->routeIs('user.merchant.api.key.form') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-users"></i>
                </span>
                <span class="nav-link-title">
                  {{__('Merchant')}}
                </span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{route('user.merchant.qr')}}">
                  {{__('QR Code')}}
                </a>

                <a class="dropdown-item" href="{{route('user.merchant.api.key.form')}}">
                  {{__('API Access Key')}}
                </a>

              </div>
            </li>
            @endif

            @if(check_user_type(3))
            <li class="nav-item dropdown {{ request()->routeIs('user.referral.invite-user') || request()->routeIs('user.referral.index') || request()->routeIs('user.referral.commissions') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-box"></i>
                </span>
                <span class="nav-link-title">
                  {{__('Supervisor')}}
                </span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{route('user.referral.invite-user')}}">
                  {{__('Invite User')}}
                </a>

                <a class="dropdown-item" href="{{route('user.referral.index')}}">
                  {{__('Referred Users')}}
                </a>

                <a class="dropdown-item" href="{{route('user.referral.commissions')}}">
                  {{__('Referral Commissions')}}
                </a>

              </div>
            </li>
            @endif

            @if (in_array('Invoice',$modules))
            <li class="nav-item dropdown {{ request()->routeIs('user.invoice.create') || request()->routeIs('user.invoice.index') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-file-invoice"></i>
                </span>
                <span class="nav-link-title">
                  {{__('Invoice')}}
                </span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{route('user.invoice.index')}}">
                  {{__('Invoices')}}
                </a>
                <a class="dropdown-item" href="{{route('user.invoice.create')}}">
                  {{__('Create Invoice')}}
                </a>


              </div>
            </li>
            @endif

            @if (in_array('Escrow',$modules))
            <li class="nav-item dropdown {{ request()->routeIs('user.escrow.create') || request()->routeIs('user.escrow.index') || request()->routeIs('user.escrow.pending') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-hands-helping"></i>
                </span>
                <span class="nav-link-title">
                  {{__('Escrow')}}
                </span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{route('user.escrow.create')}}">
                  {{__('Make Escrow')}}
                </a>
                <a class="dropdown-item" href="{{route('user.escrow.index')}}">
                  {{__('My Escrow')}}
                </a>

                <a class="dropdown-item" href="{{route('user.escrow.pending')}}">
                  {{__('Pending Escrows')}}
                </a>
              </div>
            </li>
            @endif

            <li class="nav-item dropdown {{ request()->routeIs('user.exchange.money','user.transaction') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-suitcase"></i>
                </span>
                <span class="nav-link-title">
                  {{__('More')}}
                </span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{route('user.exchange.money')}}">
                  {{__('Exchange')}}
                </a>
                <a class="dropdown-item" href="{{route('user.transaction')}}">
                  {{__('Transactions')}}
                </a>
              </div>
            </li>
            @endif
        </ul>
      </div>
    </div>
  </div>
</div>
