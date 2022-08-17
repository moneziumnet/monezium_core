<div class="card-header tab-card-header">
    <ul class="nav nav-pills card-header-tabs" id="pills-tab" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-profile')}}" id="information" href="{{route('admin-user-profile',$data->id) }}" role="button" >Information</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-accounts')}}" id="accounts" href="{{route('admin-user-accounts',$data->id) }}" role="button">Accounts</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-documents')}} {{menu('admin-user.createfile')}}" id="documents" href="{{route('admin-user-documents',$data->id) }}" role="button" >Documents</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-settings')}}" id="settings" href="{{route('admin-user-settings',$data->id) }}" role="button" >Settings</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-pricingplan')}}" id="pricingplan" href="{{route('admin-user-pricingplan',$data->id) }}" role="button" >Pricing Plan</a>
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
            <a class="nav-link {{menu('admin-user-bank-account')}}" id="modules" href="{{route('admin-user-bank-account',$data->id) }}" role="button" >Bank Account</a>
        </li>
    </ul>
</div>
