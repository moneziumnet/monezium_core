<div class="card-header tab-card-header">
    <ul class="nav nav-pills card-header-tabs" id="pills-tab" role="tablist">
        @if (isEnabledUserModule('External Payments'))
            <li class="nav-item mr-3">
                <a class="nav-link {{menu('user.withdraw.index')}} {{menu('user.withdraw.create')}} {{menu('user.withdraw.details')}}"  href="{{route('user.withdraw.index') }}" role="button" >{{__("Withdrawal")}}</a>
            </li>
        @endif
        <li class="nav-item">
            <a class="nav-link {{menu('user.beneficiaries.index')}} {{menu('user.beneficiaries.create')}} {{menu('user.beneficiaries.show')}} {{menu('user.other.send')}} {{menu('user.other.copy')}} {{menu('user.beneficiaries.edit')}}"   href="{{route('user.beneficiaries.index') }}" role="button">{{__("External Payments")}}</a>
        </li>
        @if (isEnabledUserModule('Crypto'))
            <li class="nav-item">
                <a class="nav-link {{menu('user.cryptowithdraw.index')}} {{menu('user.cryptowithdraw.create')}} "   href="{{route('user.cryptowithdraw.index') }}" role="button">{{__("Withdraw Crypto")}}</a>
            </li>
        @endif
    </ul>
</div>
