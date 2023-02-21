<div class="card-header tab-card-header mb-3">
    <ul class="nav nav-pills card-header-tabs" id="pills-tab" role="tablist">
        @foreach ($bankaccounts as $key => $item)
        @if ($key == 0)
            <li class="nav-item">
                <a class="nav-link
                        {{ menu('user.bank.transaction.index') }}
                    " href="{{ route('user.bank.transaction.index') }}"
                    role="button">
                    {{ __($item->iban) }}
                </a>
            </li>
        @else
        <li class="nav-item">
            <a class="nav-link
                    {{ menu('user.bank.transaction.account.index') }}
                " href="{{ route('user.bank.transaction.account.index', $item->id) }}"
                role="button">
                {{ __($item->iban) }}
            </a>
        </li>
        @endif
        @endforeach
    </ul>
</div>
