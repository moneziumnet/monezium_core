<div class="card-header tab-card-header">
    <ul class="nav nav-pills card-header-tabs" id="pills-tab" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{menu('admin.subinstitution.profile')}}" id="information" href="{{route('admin.subinstitution.profile',$data->id) }}" role="button">Information</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('admin.subinstitution.branches')}}" id="banks" href="{{route('admin.subinstitution.branches',$data->id) }}" role="button">Branches</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('admin.subinstitution.banks')}}" id="banks" href="{{route('admin.subinstitution.banks',$data->id) }}" role="button">Banks</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('admin.subinstitution.paymentgateways')}}" id="paymentgateways" href="{{route('admin.subinstitution.paymentgateways',$data->id) }}" role="button">Payment Gateways</a>
        </li>
    </ul>
</div>
