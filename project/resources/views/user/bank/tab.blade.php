<div class="card-header tab-card-header mb-3">
    <ul class="nav nav-pills card-header-tabs" id="pills-tab" role="tablist">
        @foreach ($bankaccounts as $item)
        <li class="nav-item">
            <a class="nav-link
                    {{ menu('user.bank.transaction.index') }}
                " href="{{ route('user.bank.transaction.index') }}"
                role="button">
                {{ __('Bank transaction') }}
            </a>
        </li>
        @endforeach
    </ul>
</div>
