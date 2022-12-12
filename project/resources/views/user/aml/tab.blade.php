<div class="card-header tab-card-header mb-3">
    <ul class="nav nav-pills card-header-tabs" id="pills-tab" role="tablist">
        <li class="nav-item">
            <a class="nav-link 
                    {{ menu('user.aml.kyc') }}
                " href="{{ route('user.aml.kyc') }}"
                role="button">
                {{ __('Required') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link 
                    {{ menu('user.aml.kyc.history') }}
                "
                href="{{ route('user.aml.kyc.history') }}" role="button">
                {{ __('History') }}
            </a>
        </li>
    </ul>
</div>
