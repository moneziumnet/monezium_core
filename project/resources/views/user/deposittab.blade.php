<div class="card-header tab-card-header">
    <ul class="nav nav-pills card-header-tabs" id="pills-tab" role="tablist">
        <li class="nav-item mr-3">
            <a class="nav-link {{menu('user.depositbank.index')}} {{menu('user.depositbank.create')}}" id="information" href="{{route('user.depositbank.index') }}" role="button" >Bank</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('user.deposit.index')}} {{menu('user.deposit.create')}}" id="accounts" href="{{route('user.deposit.index') }}" role="button">Payment Gateway</a>
        </li>
    </ul>
</div>
