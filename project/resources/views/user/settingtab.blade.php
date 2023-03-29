<div class="card-header tab-card-header mb-3">
    <ul class="nav nav-pills card-header-tabs" id="pills-tab" role="tablist">
        <li class="nav-item mr-3">
            <a class="nav-link {{menu('user.module.view')}}" id="user_module" href="{{route('user.module.view') }}" role="button" >{{__('Module')}}</a>
        </li>
		<li class="nav-item mr-3">
            <a class="nav-link {{menu('user.securityform')}}" id="user_security" href="{{route('user.securityform') }}" role="button" >{{__('Security')}}</a>
        </li>
		<li class="nav-item mr-3">
            <a class="nav-link {{menu('user.package.index')}} {{menu('user.package.subscription')}}" id="user_package" href="{{route('user.package.index') }}" role="button" >{{__('Pricing Plan')}}</a>
        </li>
		<li class="nav-item mr-3">
            <a class="nav-link {{menu('user.pincode.index')}}" id="user_pincode" href="{{route('user.pincode.index') }}" role="button" >{{__('Pincode')}}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{menu('user.login.activity')}}" id="user_activity" href="{{route('user.login.activity') }}" role="button">{{__('Login Activity')}}</a>
        </li>
		<li class="nav-item">
            <a class="nav-link {{menu('user.change.password.form')}}" id="user_password" href="{{route('user.change.password.form') }}" role="button">{{__('Change Password')}}</a>
        </li>
    </ul>
</div>