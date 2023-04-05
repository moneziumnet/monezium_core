

<li class="nav-item">
  <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#customer" aria-expanded="false" aria-controls="collapseTable">
    <i class="fas fa-user"></i>
    <span>{{ __('Manage Customers') }}</span>
  </a>
  <div id="customer" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
    <div class="bg-white py-2 collapse-inner rounded">
      <a class="collapse-item" href="#">{{ __('User List') }}</a>
      <a class="collapse-item" href="#">{{ __('Pricing Plan') }}</a>
    </div>
  </div>
</li>

<li class="nav-item">
  <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#kyc_manage" aria-expanded="true" aria-controls="collapseTable">
    <i class="fas fa-child"></i>
    <span>{{ __('AML/KYC Management') }}</span>
  </a>
  <div id="kyc_manage" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
    <div class="bg-white py-2 collapse-inner rounded">
      <a class="collapse-item" href="#">{{ __('KYC Form') }}</a>
      <a class="collapse-item" href="#">{{ __('KYC Modules') }}</a>
    </div>
  </div>
</li>


