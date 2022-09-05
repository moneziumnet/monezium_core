<div class="card-header tab-card-header">
    <ul class="nav nav-pills card-header-tabs" id="pills-tab" role="tablist">
        <li class="nav-item mr-1">
            <a class="nav-link {{menu('user.invoice.incoming.index')}} {{menu('user.invoice.incoming.edit')}} {{menu('user.invoice.incoming.view')}}" id="information" href="{{route('user.invoice.incoming.index')}}" role="button" >{{__('Incomig Invoice')}}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('user.invoice.index')}} {{menu('user.invoice.edit')}} {{menu('user.invoice.create')}} {{menu('user.invoice.view')}}" id="accounts" href="{{route('user.invoice.index') }}" role="button">{{__('Outgoing Invoice')}}</a>
        </li>
    </ul>
</div>
