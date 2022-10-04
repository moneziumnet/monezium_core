<div class="card-header tab-card-header">
    <ul class="nav nav-pills card-header-tabs" id="pills-tab" role="tablist">
        @php
            $modules = explode(" , ", auth()->user()->section);
        @endphp
        @if (in_array('External Payments',$modules))
            <li class="nav-item mr-3">
                <a class="nav-link {{menu('user.withdraw.index')}} {{menu('user.withdraw.create')}} {{menu('user.withdraw.details')}}"  href="{{route('user.withdraw.index') }}" role="button" >Withdrawal</a>
            </li>
        @endif
        <li class="nav-item">
            <a class="nav-link {{menu('user.beneficiaries.index')}} {{menu('user.beneficiaries.create')}} {{menu('user.beneficiaries.show')}} {{menu('user.other.send')}} {{menu('user.other.copy')}} {{menu('user.beneficiaries.edit')}}"   href="{{route('user.beneficiaries.index') }}" role="button">External Payments</a>
        </li>
        @if (in_array('Crypto',$modules))
            <li class="nav-item">
                <a class="nav-link {{menu('user.cryptowithdraw.index')}} {{menu('user.cryptowithdraw.create')}} "   href="{{route('user.cryptowithdraw.index') }}" role="button">Withdraw Crypto</a>
            </li>
        @endif
    </ul>
</div>
