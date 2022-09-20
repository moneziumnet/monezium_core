<div class="card-header tab-card-header mb-3">
    <ul class="nav nav-pills card-header-tabs" id="pills-tab" role="tablist">
        <li class="nav-item">
            <a class="nav-link 
                    {{ menu('user.merchant.shop.index') }}
                    {{ menu('user.merchant.shop.create') }}
                    {{ menu('user.merchant.shop.edit') }}
                    {{ menu('user.merchant.shop.view_product') }}
                " href="{{ route('user.merchant.shop.index') }}"
                role="button">
                {{ __('Merchant Shop') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link 
                    {{ menu('user.merchant.product.index') }}
                    {{ menu('user.merchant.product.edit') }}
                    {{ menu('user.merchant.product.order_by_product') }}
                "
                href="{{ route('user.merchant.product.index') }}" role="button">
                {{ __('Merchant Product') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link 
                    {{ menu('user.merchant.product.order') }}
                "
                href="{{ route('user.merchant.product.order') }}" role="button">
                {{ __('Merchant Product Order') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link 
                    {{ menu('user.merchant.checkout.index') }}
                    {{ menu('user.merchant.checkout.edit') }}
                "
                href="{{ route('user.merchant.checkout.index') }}" role="button">
                {{ __('Merchant Checkout') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ menu('user.merchant.checkout.transactionhistory') }}"
                href="{{ route('user.merchant.checkout.transactionhistory') }}" role="button">
                {{ __('Merchant Transaciton') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link 
                {{ menu('user.merchant.campaign.index') }}
                {{ menu('user.merchant.campaign.edit') }}
                {{ menu('user.merchant.campaign.donation_list') }}
            "
                href="{{ route('user.merchant.campaign.index') }}" role="button">
                {{ __('Merchant Campaign') }}
            </a>
        </li>
    </ul>
</div>
