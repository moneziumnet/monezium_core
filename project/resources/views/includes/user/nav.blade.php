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
          $kyc_modules = explode(" , ", $gs->module_section);
          @endphp

          @if(isEnabledUserModule('Shop'))
        <li class="nav-item dropdown {{ request()->routeIs('user.shop.index', 'user.shop.order', 'user.campaign.donate', 'user.merchant.product.crypto.pay', 'user.merchant.product.crypto') ? 'active' : '' }}">
        <a class="nav-link" href="{{route('user.shop.index')}}">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
            <i class="fas fa-shopping-bag"></i>
            </span>
            <span class="nav-link-title">
            {{__('Shop')}}
            </span>
        </a>
        </li>
         @endif

         @if(isEnabledUserModule('Loan') && !(auth()->user()->kyc_status != 1 && in_array('Loan',$kyc_modules)))
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

            @if(isEnabledUserModule('Investments') && !(auth()->user()->kyc_status != 1 && in_array('Investments',$kyc_modules)))
            <li class="nav-item dropdown {{ request()->routeIs('user.invest.index') ? 'active' : '' }}">
            <a class="nav-link" href="{{route('user.invest.index')}}">
              <span class="nav-link-icon d-md-none d-lg-inline-block">
                <i class="fas fa-warehouse"></i>
              </span>
              <span class="nav-link-title">
                {{__('Investments ')}}
              </span>
            </a>
            </li>
            @endif

            @if(isEnabledUserModule('Payments') && !(auth()->user()->kyc_status != 1 && in_array('Payments',$kyc_modules)))
            <li class="nav-item dropdown {{ request()->routeIs('user.depositbank.index','user.depositbank.create','user.deposit.index', 'user.deposit.create','user.cryptodeposit.index', 'user.cryptodeposit.create','user.wire.transfer.index', 'user.beneficiaries.index', 'user.beneficiaries.create',  'tranfer.logs.index','user.withdraw.index', 'user.card.index', 'user.money.request.index', 'user.money.request.create', 'user.money.request.details', 'user.cryptowithdraw.create', 'send.money.savedUser', 'user.cryptowithdraw.index', 'send.money.create', 'ownaccounttransfer-index', 'user.exchange.money', 'user.transaction', 'user.other.send', 'user.other.copy') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-hand-holding-usd"></i>
                </span>
                <span class="nav-link-title">
                  {{__('Payments')}}
                </span>
              </a>
              <div class="dropdown-menu">
                @if(isEnabledUserModule('Incoming'))
                <a class="dropdown-item" href="{{route('user.depositbank.index')}}">
                    {{__('Incoming')}}
                </a>
                @endif
                <!-- @if(isEnabledUserModule('Wire Transfer'))
                <a class="dropdown-item" href="{{route('user.wire.transfer.index')}}">
                  {{__('Wire Transfer')}}
                </a>
                @endif -->
                @if(isEnabledUserModule('Cards'))
                <a class="dropdown-item" href="{{route('user.card.index')}}">
                    {{__('Cards')}}
                </a>
                @endif
                @if(isEnabledUserModule('External Payments'))
                <a class="dropdown-item" href="{{route('user.beneficiaries.index')}}">
                  {{__('External Payments')}}
                </a>
                @endif
                @if(isEnabledUserModule('Payment between accounts'))
                <a class="dropdown-item" href="{{ route('ownaccounttransfer-index') }}">
                    {{__('Payment between accounts')}}
                </a>
                @endif
                @if(isEnabledUserModule('Internal Payment') && !(auth()->user()->kyc_status != 1 && in_array('Internal Payment',$kyc_modules)))
                <a class="dropdown-item" href="{{route('send.money.create')}}">
                  {{__('Internal Payment')}}
                </a>
                @endif
                @if(isEnabledUserModule('Request Money') && !(auth()->user()->kyc_status != 1 && in_array('Request Money',$kyc_modules)))
                <a class="dropdown-item" href="{{route('user.money.request.index')}}">
                  {{__('Request Money')}}
                </a>
                @endif

                @if(isEnabledUserModule('Exchange Money') && !(auth()->user()->kyc_status != 1 && in_array('Exchange Money',$kyc_modules)))
                <a class="dropdown-item" href="{{route('user.exchange.money')}}">
                  {{__('Exchange')}}
                </a>
                @endif
                @if(isEnabledUserModule('Transactions'))
                <a class="dropdown-item" href="{{route('user.transaction')}}">
                  {{__('Transactions')}}
                </a>
                @endif
              </div>
            </li>
            @endif

            @if(isEnabledUserModule('Voucher') && !(auth()->user()->kyc_status != 1 && in_array('Voucher',$kyc_modules)))
            <li class="nav-item dropdown {{ request()->routeIs('user.vouchers', 'user.create.voucher', 'user.reedem.voucher','user.reedem.history') ? 'active' : '' }}">
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
            <li class="nav-item dropdown {{ request()->routeIs('user.merchant.index', 'user.merchant.send.money.create', 'user.merchant.money.request.index', 'user.merchant.other.bank', 'user.merchant.shop.index', 'user.merchant.shop.view_product', 'user.merchant.checkout.index', 'user.merchant.product.edit', 'user.merchant.product.index', 'user.merchant.product.order', 'user.merchant.product.order_by_product', 'user.merchant.checkout.transactionhistory', 'user.merchant.campaign.index', 'user.merchant.campaign.edit', 'user.merchant.campaign.donation_list') ? 'active' : '' }}">
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

                <a class="dropdown-item" href="{{route('user.merchant.setting')}}">
                  {{__('Merchant Setting')}}
                </a>

                @if(isEnabledUserModule('Merchant Shop'))
                <a class="dropdown-item" href="{{route('user.merchant.shop.index')}}">
                    {{__('Merchant Shop')}}
                </a>
                @endif

                @if(isEnabledUserModule('Merchant own Account'))
                <a class="dropdown-item" href="{{route('user.merchant.send.money.create')}}">
                    {{__('Payment to own Account')}}
                </a>
                @endif

                @if(isEnabledUserModule('Merchant Request Money'))
                <a class="dropdown-item" href="{{route('user.merchant.money.request.index')}}">
                    {{__('Request Money')}}
                </a>
                @endif

                <!-- <a class="dropdown-item" href="{{route('user.merchant.other.bank')}}">
                  {{__('Other Bank Transfer')}}
                </a> -->

              </div>
            </li>
            @endif

            @if(check_user_type(4) || DB::table('managers')->where('manager_id', auth()->id())->first())
            <li class="nav-item dropdown {{  request()->routeIs('user.referral.index', 'user.manager.create', 'user-pricingplan') ? 'active' : '' }}">
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

            @if(isEnabledUserModule('Invoice') && !(auth()->user()->kyc_status != 1 && in_array('Invoice',$kyc_modules)))
            <li class="nav-item dropdown {{ request()->routeIs('user.invoice.create', 'user.invoice.index', 'user.contract.index', 'user.invoice.invoic_setting', 'user.invoice.incoming.index') ? 'active' : '' }}">
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
                <a class="dropdown-item" href="{{route('user.invoice.invoic_setting')}}">
                    {{__('Invoice Setting')}}
                  </a>
                @if(isEnabledUserModule('Contracts'))
                <a class="dropdown-item" href="{{route('user.contract.index')}}">
                    {{__('Contracts')}}
                </a>
                @endif
              </div>
            </li>
            @endif

            @if(isEnabledUserModule('Escrow') && !(auth()->user()->kyc_status != 1 && in_array('Escrow',$kyc_modules)))
            <li class="nav-item dropdown {{ request()->routeIs('user.escrow.create', 'user.escrow.index', 'user.escrow.pending') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-hands-helping"></i>
                </span>
                <span class="nav-link-title">
                  {{__('Escrow')}}
                </span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{route('user.escrow.index')}}">
                  {{__('My Escrows')}}
                </a>

                <a class="dropdown-item" href="{{route('user.escrow.pending')}}">
                  {{__('Pending Escrows')}}
                </a>
              </div>
            </li>
            @endif

            @if(isEnabledUserModule('ICO') && !(auth()->user()->kyc_status != 1 && in_array('ICO',$kyc_modules)))
            <li class="nav-item dropdown {{ request()->routeIs('user.ico') ? 'active' : '' }}">
              <a class="nav-link" href="{{route('user.ico')}}">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-coins"></i>
                </span>
                <span class="nav-link-title">
                  {{__('ICO')}}
                </span>
              </a>
            </li>
            @endif
        </ul>
      </div>
    </div>
  </div>
</div>
