<div class="navbar navbar-expand-xl navbar-vertical navbar-dark">
  <div class="collapse navbar-collapse" id="navbar-menu">
    <div class="navbar navbar-dark">
      <div class="container-fluid">
        <ul class="navbar-nav">
          <h1 class="navbar-brand navbar-brand-autodark">
            <a href="{{ route('front.index') }}">
              <img src="{{asset('assets/images/'.$gs->logo)}}" width="110" height="32" alt="Tabler" class="navbar-brand-image">
            </a>
          </h1>
          <li class="mt-3 nav-item">
            <span class="nav-item-header">
              {{__('Navigation')}}
            </span>
          </li>
          <li class="nav-item {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
            <a class="nav-link" href="{{route('user.dashboard')}}">
              <span class="nav-link-icon d-md-none d-lg-inline-block">
                <i class="fas fa-home"></i>
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
                    {{__('Between accounts')}}
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


            <li class="mt-3 nav-item">
              <span class="nav-item-header">
                {{__('Balances')}}
              </span>
            </li>
            @php
              $wallets = DB::table('wallets')->where('user_id',auth()->id())->where('user_type',1)->where('wallet_type', 1)->get();
            @endphp 
            @foreach($wallets as $key => $wallet)
              @php
                $currency = DB::table('currencies')->where('id', $wallet->currency_id)->first();
              @endphp
              <li class="nav-item">
                <span class="nav-link">
                  <span class="nav-link-icon d-md-none d-lg-inline-block">
                    @if ($wallet->currency_id === 1)
                      <span class="flag flag-country-us"></span>
                    @elseif ($wallet->currency_id === 2)
                      <span class="flag flag-country-eu"></span>
                    @elseif ($wallet->currency_id === 3)
                      <span class="flag flag-country-gb"></span>
                    @endif
                  </span>
                  <span class="nav-link-title">
                  {{ amount($wallet->balance) }}  {{$currency->code}}
                  </span>
                </span>
              </li>
            @endforeach
            <!-- <li class="nav-item">
              <a class="nav-link" data-bs-toggle="modal" data-bs-target="#modal-wallet-create">
                <i class="fas fa-plus me-1"></i> {{__(' Open a balance')}}
              </a>
            </li> -->
          </ul>
      </div>
      <!-- <div class="modal modal-blur fade" id="modal-wallet-create" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
          <div class="modal-content">
              <div class="modal-header">
              <h5 class="modal-title">{{('Wallet Create')}}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form action="{{route('user.wallet.create')}}" method="POST" enctype="multipart/form-data">
              @php
                $currencies = DB::table('currencies')->where('type', 1)->get();
              @endphp
                  @csrf
                  <div class="modal-body">
                      <div class="form-group">
                          <label class="form-label required">{{__('Currency')}}</label>
                          <select name="currency_id" class="form-select" required>
                              <option value="">{{ __('Select Currency') }}</option>
                              @foreach ($currencies as $key => $value)
                                      <option value="{{$value->id}}">{{$value->code}}</option>
                              @endforeach
                          </select>
                      </div>
                      <input type="hidden" name="user_id" value="{{auth()->user()->id}}">
                  </div>

                  <div class="modal-footer">
                      <button  id="submit-btn" class="btn btn-primary">{{ __('Create') }}</button>
                  </div>
              </form>
          </div>
        </div>
      </div> -->
    </div>
  </div>
</div>
