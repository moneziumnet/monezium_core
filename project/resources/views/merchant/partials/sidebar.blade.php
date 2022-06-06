  <li class="nav-item">
    <a class="nav-link" href="{{route('merchant.qr')}}">
      <i class="fas fa-qrcode"></i>
      <span>{{ __('QR Code') }}</span>
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link" href="{{route('merchant.api.key.form')}}">
      <i class="fas fa-key"></i>
      <span>{{ __('API Access Key') }}</span>
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link" href="{{route('merchant.transactions')}}">
      <i class="fas fa-exchange-alt"></i>
      <span>{{ __('Transactions') }}</span>
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#withdraw" aria-expanded="true" aria-controls="collapseTable">
      <i class="fas fa-university"></i>
    <span>{{ __('Withdraw') }}</span>
  </a>
    <div id="withdraw" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
        <a class="collapse-item" href="{{route('merchant.withdraw.form')}}">{{ __('Withdraw Money') }}</a>
        <a class="collapse-item" href="{{route('merchant.withdraw.history')}}">{{ __('Withdraw History') }}</a>
      </div>
    </div>
  </li>
  
  <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#setting" aria-expanded="true" aria-controls="collapseTable">
      <i class="fas fa-user"></i>
    <span>{{ __('Setting') }}</span>
  </a>
    <div id="setting" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
      <div class="bg-white py-2 collapse-inner rounded">
        <a class="collapse-item" href="{{route('merchant.profile.setting')}}">{{ __('Profile Setting') }}</a>
        <a class="collapse-item" href="{{route('merchant.change.password')}}">{{ __('Change Password') }}</a>
        <a class="collapse-item" href="{{route('merchant.two.step')}}">{{ __('Two Step Security') }}</a>
        <a class="collapse-item" href="{{route('merchant.ticket.index')}}">{{ __('Support Ticket') }}</a>
      </div>
    </div>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="{{route('merchant.logout')}}">
      <i class="fas fa-sign-out-alt"></i>
      <span>{{ __('Log Out') }}</span>
    </a>
  </li>
  
  