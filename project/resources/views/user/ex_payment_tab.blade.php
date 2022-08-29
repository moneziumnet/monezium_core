<div class="card-header tab-card-header">
    <ul class="nav nav-pills card-header-tabs" id="pills-tab" role="tablist">
        <li class="nav-item mr-3">
            <a class="nav-link {{menu('user.withdraw.index')}} {{menu('user.withdraw.create')}} {{menu('user.withdraw.details')}}"  href="{{route('user.withdraw.index') }}" role="button" >Withdrawal</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('user.other.bank')}} {{menu('user.other.send')}}"  href="{{route('user.other.bank') }}" role="button">External Payments</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('user.beneficiaries.index')}} {{menu('user.beneficiaries.create')}} {{menu('user.beneficiaries.show')}}"   href="{{route('user.beneficiaries.index') }}" role="button">Beneficiaries</a>
        </li>
        @php
            $modules = explode(" , ", auth()->user()->section);
        @endphp
        @if (in_array('Crypto',$modules))
            <li class="nav-item">
                <a class="nav-link {{menu('user.cryptowithdraw.index')}} {{menu('user.cryptowithdraw.create')}} "   href="{{route('user.cryptowithdraw.index') }}" role="button">Withdraw Crypto</a>
            </li>
        @endif
    </ul>
</div>
