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
          $kyc_modules = explode(" , ", $gs->module_section);
          $count = count($modules);
          @endphp

          @if ($count < 8)
          @if (in_array('Loan',$modules) && !(auth()->user()->kyc_status != 1 && in_array('Loan',$kyc_modules)))
          <li class="nav-item dropdown {{ request()->routeIs('user.loans.plan') ? 'active' : '' }}">
            <a class="nav-link" href="{{route('user.loans.plan')}}">
              <span class="nav-link-icon d-md-none d-lg-inline-block">
                <i class="fas fa-cash-register"></i>
              </span>
              <span class="nav-link-title">
                {{__('Loan')}}
              </span>
            </a>
            </li>
            @endif

            @if (in_array('Investments',$modules) && !(auth()->user()->kyc_status != 1 && in_array('Investments',$kyc_modules)))
            <li class="nav-item dropdown {{ request()->routeIs('user.invest.index') ? 'active' : '' }}">
            <a class="nav-link" href="{{route('user.invest.index')}}">
              <span class="nav-link-icon d-md-none d-lg-inline-block">
                <i class="fas fa-cash-register"></i>
              </span>
              <span class="nav-link-title">
                {{__('Investments ')}}
              </span>
            </a>
            </li>
            @endif

            @if (in_array('Payments',$modules) && !(auth()->user()->kyc_status != 1 && in_array('Payments',$kyc_modules)))
            <li class="nav-item dropdown {{ request()->routeIs('user.deposit.index', 'user.wire.transfer.index', 'user.other.bank', 'user.beneficiaries.index', 'tranfer.logs.index','user.withdraw.index') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-hand-holding-usd"></i>
                </span>
                <span class="nav-link-title">
                  {{__('Payments')}}
                </span>
              </a>
              <div class="dropdown-menu">
                @if (in_array('Incoming',$modules))
                <a class="dropdown-item" href="{{route('user.depositbank.index')}}">
                    {{__('Incoming')}}
                </a>
                @endif
                @if (in_array('Withdraw',$modules))
                <a class="dropdown-item" href="{{route('user.withdraw.index')}}">
                  {{__('Withdraw')}}
                </a>
                @endif
                @if (in_array('Wire Transfer',$modules))
                <a class="dropdown-item" href="{{route('user.wire.transfer.index')}}">
                  {{__('Wire Transfer')}}
                </a>
                @endif
                @if (in_array('Bank Transfer',$modules))
                <a class="dropdown-item" href="{{route('user.other.bank')}}">
                  {{__('External Payments')}}
                </a>
                <a class="dropdown-item" href="{{route('user.beneficiaries.index')}}">
                  {{__('Beneficiary Manage')}}
                </a>
                <a class="dropdown-item" href="{{ route('tranfer.logs.index') }}">
                  {{__('Transfer History')}}
                </a>
                @endif
                <a class="dropdown-item" href="{{ route('ownaccounttransfer-index') }}">
                    {{__('Own Money Transfer')}}
                </a>
              </div>
            </li>
            @endif

            @if (in_array('Request Money',$modules) && !(auth()->user()->kyc_status != 1 && in_array('Request Money',$kyc_modules)))
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
            @endif


            @if (in_array('Exchange Money',$modules) && !(auth()->user()->kyc_status != 1 && in_array('Exchange Money',$kyc_modules)))
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

            @if (in_array('Voucher',$modules) && !(auth()->user()->kyc_status != 1 && in_array('Voucher',$kyc_modules)))
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

            @if(check_user_type(3))
            <li class="nav-item dropdown {{ request()->routeIs('user.merchant.index') || request()->routeIs('user.merchant.send.money.create') || request()->routeIs('user.merchant.money.request.index') || request()->routeIs('user.merchant.other.bank') || request()->routeIs('user.merchant.shop.index') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-users"></i>
                </span>
                <span class="nav-link-title">
                  {{__('Merchant')}}
                </span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{route('user.merchant.index')}}">
                  {{__('Merchant')}}
                </a>

                <a class="dropdown-item" href="{{route('user.merchant.shop.index')}}">
                    {{__('Merchant Shop')}}
                  </a>

                <a class="dropdown-item" href="{{route('user.merchant.send.money.create')}}">
                    {{__('Send Money')}}
                </a>

                <a class="dropdown-item" href="{{route('user.merchant.money.request.index')}}">
                    {{__('Send Request Money')}}
                </a>

                @if (in_array('Bank Transfer',$modules))
                <a class="dropdown-item" href="{{route('user.merchant.other.bank')}}">
                  {{__('Other Bank Transfer')}}
                </a>
                @endif
              </div>
            </li>
            @endif

            @if(check_user_type(4) || DB::table('managers')->where('manager_id', auth()->id())->first())
            <li class="nav-item dropdown {{  request()->routeIs('user.referral.index') ? 'active' : '' }}">
              <a class="nav-link" href="{{route('user.referral.index')}}" >
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-box"></i>
                </span>
                <span class="nav-link-title">
                    @if(check_user_type(4))
                        {{__('Supervisor')}}
                    @elseif(DB::table('managers')->where('manager_id', auth()->id())->first())
                        {{__('Manager')}}
                    @endif
                </span>
              </a>
            </li>
            @endif

            @if (in_array('Invoice',$modules) && !(auth()->user()->kyc_status != 1 && in_array('Invoice',$kyc_modules)))
            <li class="nav-item dropdown {{ request()->routeIs('user.invoice.create') || request()->routeIs('user.invoice.index') || request()->routeIs('user.contract.index') ? 'active' : '' }}">
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
                <a class="dropdown-item" href="{{route('user.contract.index')}}">
                    {{__('Contracts')}}
                </a>


              </div>
            </li>
            @endif

            @if (in_array('Escrow',$modules) && !(auth()->user()->kyc_status != 1 && in_array('Escrow',$kyc_modules)))
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

            @if (in_array('Loan',$modules) && !(auth()->user()->kyc_status != 1 && in_array('Loan',$kyc_modules)))
            <li class="nav-item dropdown {{ request()->routeIs('user.loans.plan') ? 'active' : '' }}">
            <a class="nav-link" href="{{route('user.loans.plan')}}">
              <span class="nav-link-icon d-md-none d-lg-inline-block">
                <i class="fas fa-cash-register"></i>
              </span>
              <span class="nav-link-title">
                {{__('Loan')}}
              </span>
            </a>
            </li>
            @endif

            @if (in_array('Investments',$modules) && !(auth()->user()->kyc_status != 1 && in_array('Investments',$kyc_modules)))
            <li class="nav-item dropdown {{ request()->routeIs('user.invest.index') ? 'active' : '' }}">
            <a class="nav-link" href="{{route('user.invest.index')}}">
              <span class="nav-link-icon d-md-none d-lg-inline-block">
                <i class="fas fa-cash-register"></i>
              </span>
              <span class="nav-link-title">
                {{__('Investments ')}}
              </span>
            </a>
            </li>
            @endif

            @if (in_array('Payments',$modules) && !(auth()->user()->kyc_status != 1 && in_array('Payments',$kyc_modules)))
            <li class="nav-item dropdown {{ request()->routeIs('user.deposit.index', 'user.wire.transfer.index', 'user.other.bank', 'user.beneficiaries.index', 'tranfer.logs.index','user.withdraw.index') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-hand-holding-usd"></i>
                </span>
                <span class="nav-link-title">
                  {{__('Payments')}}
                </span>
              </a>
              <div class="dropdown-menu">
                @if (in_array('Incoming',$modules))
                <a class="dropdown-item" href="{{route('user.depositbank.index')}}">
                    {{__('Incoming')}}
                </a>
                @endif
                @if (in_array('Withdraw',$modules))
                <a class="dropdown-item" href="{{route('user.withdraw.index')}}">
                  {{__('Withdraw')}}
                </a>
                @endif
                @if (in_array('Wire Transfer',$modules))
                <a class="dropdown-item" href="{{route('user.wire.transfer.index')}}">
                  {{__('Wire Transfer')}}
                </a>
                @endif
                @if (in_array('Bank Transfer',$modules))
                <a class="dropdown-item" href="{{route('user.other.bank')}}">
                  {{__('External Payments')}}
                </a>
                <a class="dropdown-item" href="{{route('user.beneficiaries.index')}}">
                  {{__('Beneficiary Manage')}}
                </a>
                <a class="dropdown-item" href="{{ route('tranfer.logs.index') }}">
                  {{__('Transfer History')}}
                </a>
                @endif
                <a class="dropdown-item" href="{{ route('ownaccounttransfer-index') }}">
                    {{__('Own Money Transfer')}}
                </a>
              </div>
            </li>
            @endif

            @if (in_array('Request Money',$modules)&& !(auth()->user()->kyc_status != 1 && in_array('Request Money',$kyc_modules)))
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

            @if (in_array('Voucher',$modules) && !(auth()->user()->kyc_status != 1 && in_array('Voucher',$kyc_modules)))
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

            @if(check_user_type(3))
            <li class="nav-item dropdown {{ request()->routeIs('user.merchant.index') || request()->routeIs('user.merchant.send.money.create') || request()->routeIs('user.merchant.money.request.index') || request()->routeIs('user.merchant.other.bank') || request()->routeIs('user.merchant.shop.index') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-users"></i>
                </span>
                <span class="nav-link-title">
                  {{__('Merchant')}}
                </span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{route('user.merchant.index')}}">
                  {{__('Merchant')}}
                </a>

                <a class="dropdown-item" href="{{route('user.merchant.shop.index')}}">
                    {{__('Merchant Shop')}}
                  </a>

                <a class="dropdown-item" href="{{route('user.merchant.send.money.create')}}">
                    {{__('Send Money')}}
                </a>

                <a class="dropdown-item" href="{{route('user.merchant.money.request.index')}}">
                    {{__('Send Request Money')}}
                </a>

                @if (in_array('Bank Transfer',$modules))
                <a class="dropdown-item" href="{{route('user.merchant.other.bank')}}">
                  {{__('Other Bank Transfer')}}
                </a>
                @endif
              </div>
            </li>
            @endif

            @if(check_user_type(4) || DB::table('managers')->where('manager_id', auth()->id())->first())
            <li class="nav-item dropdown {{  request()->routeIs('user.referral.index') ? 'active' : '' }}">
              <a class="nav-link" href="{{route('user.referral.index')}}" >
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-box"></i>
                </span>
                <span class="nav-link-title">
                    @if(check_user_type(4))
                        {{__('Supervisor')}}
                    @elseif(DB::table('managers')->where('manager_id', auth()->id())->first())
                        {{__('Manager')}}
                    @endif
                </span>
              </a>
            </li>
            @endif

            @if (in_array('Invoice',$modules) && !(auth()->user()->kyc_status != 1 && in_array('Invoice',$kyc_modules)))
            <li class="nav-item dropdown {{ request()->routeIs('user.invoice.create') || request()->routeIs('user.invoice.index') || request()->routeIs('user.contract.index') ? 'active' : '' }}">
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
                <a class="dropdown-item" href="{{route('user.contract.index')}}">
                    {{__('Contracts')}}
                </a>


              </div>
            </li>
            @endif

            @if (in_array('Escrow',$modules) && !(auth()->user()->kyc_status != 1 && in_array('Escrow',$kyc_modules)))
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
