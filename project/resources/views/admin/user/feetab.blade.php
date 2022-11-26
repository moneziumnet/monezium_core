<div class="card-header tab-card-header">
    <ul class="nav nav-pills card-header-tabs" id="pills-tab" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-pricingplan')}}" id="general" href="{{route('admin-user-pricingplan',$data->id) }}" role="button">General/Admin</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-pricingplan-supervisor')}}" id="supervisor" href="{{route('admin-user-pricingplan-supervisor',$data->id) }}" role="button" >Supervisor</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('admin-user-pricingplan-manager')}}" id="manager" href="{{route('admin-user-pricingplan-manager',$data->id) }}" role="button" >Manager</a>
        </li>
    </ul>
</div>
