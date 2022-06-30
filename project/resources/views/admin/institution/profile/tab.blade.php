<div class="card-header tab-card-header">
    <ul class="nav nav-pills card-header-tabs" id="pills-tab" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{menu('admin.institution.profile')}}" id="information" href="{{route('admin.institution.profile',$data->id) }}" role="button">Information</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('admin.institution.contacts')}}" id="banks" href="{{route('admin.institution.contacts',$data->id) }}" role="button">Contacts</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('admin.institution.modules')}}" id="banks" href="{{route('admin.institution.modules',$data->id) }}" role="button">Modules</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('admin.institution.documents')}}" id="banks" href="{{route('admin.institution.documents',$data->id) }}" role="button">Documents</a>
        </li>
    </ul>
</div>
