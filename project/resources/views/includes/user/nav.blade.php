<div class="navbar-expand-xl">
  <div class="collapse navbar-collapse" id="navbar-menu">
    <div class="navbar navbar-light">
      <div class="container-fluid">
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
                @if(isEnabledUserModule('Incoming') && !(auth()->user()->kyc_status != 1 && in_array('Incoming',$kyc_modules)))
                <a class="dropdown-item" href="{{route('user.depositbank.index')}}">
                    {{__('Incoming')}}
                </a>
                @endif
                @if(isEnabledUserModule('Cards'))
                <a class="dropdown-item" href="{{route('user.card.index')}}">
                    {{__('Cards')}}
                </a>
                @endif
                @if(isEnabledUserModule('External Payments') && !(auth()->user()->kyc_status != 1 && in_array('External Payments',$kyc_modules)))
                <a class="dropdown-item" href="{{route('user.beneficiaries.index')}}">
                  {{__('External Payments')}}
                </a>
                @endif
                @if(isEnabledUserModule('Payment between accounts') && !(auth()->user()->kyc_status != 1 && in_array('Payment between accounts',$kyc_modules)))
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
                @if(isEnabledUserModule('Transactions') && !(auth()->user()->kyc_status != 1 && in_array('Transactions',$kyc_modules)))
                <a class="dropdown-item" href="{{route('user.transaction')}}">
                  {{__('Transactions')}}
                </a>
                @endif
              </div>
            </li>
            @endif

        </ul>
      </div>
    </div>
  </div>
</div>
