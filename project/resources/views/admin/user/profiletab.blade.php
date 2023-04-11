@if(Auth::guard('admin')->user()->role === 'admin')
<div class="card-header tab-card-header">
    <ul class="nav nav-pills card-header-tabs" id="pills-tab" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-profile')}}" id="information" href="{{route('admin-user-profile',$data->id) }}" role="button" >Information</a>
        </li>
        <li class="nav-item">
            <a class="nav-link
                {{menu('admin-user-accounts')}}
                {{menu('admin-wallet-internal')}}
                {{menu('admin-wallet-external')}}
                {{menu('admin-wallet-between')}}
                {{menu('admin-wallet-transactions')}}" id="accounts" href="{{route('admin-user-accounts',$data->id) }}" role="button">Accounts</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-documents')}} {{menu('admin-user.view-document')}} {{menu('admin-user.createfile')}}" id="documents" href="{{route('admin-user-documents',$data->id) }}" role="button" >Documents</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-settings')}}" id="settings" href="{{route('admin-user-settings',$data->id) }}" role="button" >Settings</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-pricingplan')}} {{menu('admin-user-pricingplan-supervisor')}} {{menu('admin-user-pricingplan-manager')}}" id="pricingplan" href="{{route('admin-user-pricingplan',$data->id) }}" role="button" >Pricing Plan</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-transactions')}} {{menu('admin-user.transaction-edit')}}" id="transactions" href="{{route('admin-user-transactions',$data->id) }}" role="button" >Transactions</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-banks')}}" id="banks" href="{{route('admin-user-banks',$data->id) }}" role="button" >Banks</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-modules')}}" id="modules" href="{{route('admin-user-modules',$data->id) }}" role="button" >Modules</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-bank-account')}}" id="modules" href="{{route('admin-user-bank-account',$data->id) }}" role="button" >IBANs</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('admin.contract.management')}} {{menu('admin.aoa.index')}}" id="modules" href="{{route('admin.contract.management',$data->id) }}" role="button" >Contract</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('admin.merchant.shop.index')}} " id="modules" href="{{route('admin.merchant.shop.index',$data->id) }}" role="button" >Merchant Shop</a>
        </li>
        @if(getModule('KYC Management'))
            <li class="nav-item">
                <a class="nav-link {{menu('admin.user.kycinfo')}}" id="modules" href="{{route('admin.user.kycinfo',$data->id) }}" role="button" >AML/KYC</a>
            </li>
        @endif
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-beneficiary')}} {{menu('admin-user-beneficiary-create')}} {{menu('admin-user-beneficiary-edit')}}" id="modules" href="{{route('admin-user-beneficiary',$data->id) }}" role="button" >Beneficiaries</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-layer')}} " id="modules" href="{{route('admin-user-layer',$data->id) }}" role="button" >Layer</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-login-history')}} " id="modules" href="{{route('admin-user-login-history',$data->id) }}" role="button" >Login History</a>
        </li>
    </ul>
</div>
@elseif(Auth::guard('admin')->user()->role === 'staff')
<div class="card-header tab-card-header">
    <ul class="nav nav-pills card-header-tabs" id="pills-tab" role="tablist">
        @if(getModule('Infromation'))
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-profile')}}" id="information" href="{{route('admin-user-profile',$data->id) }}" role="button" >Information</a>
        </li>
        @endif
        @if(getModule('Accounts'))
        <li class="nav-item">
            <a class="nav-link
                {{menu('admin-user-accounts')}}
                {{menu('admin-wallet-internal')}}
                {{menu('admin-wallet-external')}}
                {{menu('admin-wallet-between')}}
                {{menu('admin-wallet-transactions')}}" id="accounts" href="{{route('admin-user-accounts',$data->id) }}" role="button">Accounts</a>
        </li>
        @endif
        @if(getModule('Documents'))
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-documents')}} {{menu('admin-user.view-document')}} {{menu('admin-user.createfile')}}" id="documents" href="{{route('admin-user-documents',$data->id) }}" role="button" >Documents</a>
        </li>
        @endif
        @if(getModule('Setting'))
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-settings')}}" id="settings" href="{{route('admin-user-settings',$data->id) }}" role="button" >Settings</a>
        </li>
        @endif
        @if(getModule('Pricing Plan'))
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-pricingplan')}} {{menu('admin-user-pricingplan-supervisor')}} {{menu('admin-user-pricingplan-manager')}}" id="pricingplan" href="{{route('admin-user-pricingplan',$data->id) }}" role="button" >Pricing Plan</a>
        </li>
        @endif
        @if(getModule('Customer Transactions'))
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-transactions')}} {{menu('admin-user.transaction-edit')}}" id="transactions" href="{{route('admin-user-transactions',$data->id) }}" role="button" >Transactions</a>
        </li>
        @endif
        @if(getModule('Banks'))
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-banks')}}" id="banks" href="{{route('admin-user-banks',$data->id) }}" role="button" >Banks</a>
        </li>
        @endif
        @if(getModule('Modules'))
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-modules')}}" id="modules" href="{{route('admin-user-modules',$data->id) }}" role="button" >Modules</a>
        </li>
        @endif
        @if(getModule('IBAN'))
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-bank-account')}}" id="modules" href="{{route('admin-user-bank-account',$data->id) }}" role="button" >IBANs</a>
        </li>
        @endif
        @if(getModule('Contract'))
        <li class="nav-item">
            <a class="nav-link {{menu('admin.contract.management')}} {{menu('admin.aoa.index')}}" id="modules" href="{{route('admin.contract.management',$data->id) }}" role="button" >Contract</a>
        </li>
        @endif
        @if(getModule('Merchant Shop'))
        <li class="nav-item">
            <a class="nav-link {{menu('admin.merchant.shop.index')}} " id="modules" href="{{route('admin.merchant.shop.index',$data->id) }}" role="button" >Merchant Shop</a>
        </li>
        @endif
        @if(getModule('AML/KYC'))
        <li class="nav-item">
            <a class="nav-link {{menu('admin.user.kycinfo')}}" id="modules" href="{{route('admin.user.kycinfo',$data->id) }}" role="button" >AML/KYC</a>
        </li>
        @endif
        @if(getModule('Beneficiary'))
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-beneficiary')}} {{menu('admin-user-beneficiary-create')}} {{menu('admin-user-beneficiary-edit')}}" id="modules" href="{{route('admin-user-beneficiary',$data->id) }}" role="button" >Beneficiaries</a>
        </li>
        @endif
        @if(getModule('Layer'))
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-layer')}} " id="modules" href="{{route('admin-user-layer',$data->id) }}" role="button" >Layer</a>
        </li>
        @endif
        @if(getModule('Login History'))
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-login-history')}} " id="modules" href="{{route('admin-user-login-history',$data->id) }}" role="button" >Login History</a>
        </li>
        @endif
    </ul>
</div>
@endif


