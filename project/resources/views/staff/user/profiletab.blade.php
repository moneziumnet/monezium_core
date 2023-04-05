<div class="card-header tab-card-header">
    <ul class="nav nav-pills card-header-tabs" id="pills-tab" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{menu('staff-user-profile')}}" id="information" href="{{route('staff-user-profile',$data->id) }}" role="button" >{{__('Information')}}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('staff.user.kycinfo')}}" id="modules" href="{{route('staff.user.kycinfo',$data->id)}}" role="button" >{{__('AML/KYC')}}</a>
        </li>
    </ul>
</div>
