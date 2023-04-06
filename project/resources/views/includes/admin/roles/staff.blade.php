
<li class="nav-item">
  <a class="nav-link" href="{{ route('admin.user.index') }}">
    <i class="fas fa-user"></i>
    <span>{{ __('Manage Customers') }}</span>
  </a>
</li>

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