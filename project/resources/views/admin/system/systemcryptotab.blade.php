<div class="card-header tab-card-header">
    <ul class="nav nav-pills card-header-tabs" id="pills-tab" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{menu('admin.system.accounts')}} {{ menu('admin.system.account.transactions')}}" id="information" href="{{route('admin.system.accounts') }}" role="button" >System Account</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('admin.system.crypto.api')}}" id="information" href="{{route('admin.system.crypto.api', 'kraken') }}" role="button" >Kraken</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('admin.system.crypto.binance.api')}}" id="information" href="{{route('admin.system.crypto.binance.api') }}" role="button" >Binance</a>
        </li>


    </ul>
</div>
