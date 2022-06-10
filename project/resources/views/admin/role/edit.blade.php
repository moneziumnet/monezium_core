@extends('layouts.admin')

@section('content')

<div class="card">
    <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Edit Role') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.role.index')}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
    <ol class="breadcrumb py-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="javascript:;">{{ __('Roles') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.role.index') }}">{{ __('Module management') }}</a></li>
        <li class="breadcrumb-item"><a href="{{route('admin.role.edit',$data->id)}}">{{ __('Edit Role') }}</a></li>
    </ol>
    </div>
</div>

<div class="row justify-content-center mt-3">
  <div class="col-md-10">
    <!-- Form Basic -->
    <div class="card mb-4">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">{{ __('Edit Role Form') }}</h6>
      </div>

      <div class="card-body">
        <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
        <form class="geniusform" action="{{route('admin.role.update',$data->id)}}" method="POST" enctype="multipart/form-data">

            @include('includes.admin.form-both')

            {{ csrf_field() }}

            <div class="form-group">
              <label for="inp-name">{{ __('Role Name') }}</label>
              <input type="text" class="form-control" id="inp-name" name="name"  placeholder="{{ __('Enter Role Name') }}" value="{{$data->name}}" required>
          </div>

          <div class="row">
            <!-- <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Menu Builder" {{ $data->sectionCheck('Menu Builder') ? 'checked' : '' }} class="custom-control-input" id="menu_builder">
                  <label class="custom-control-label" for="menu_builder">{{__('Menu Builder')}}</label>
                  </div>
              </div>
            </div> -->
            <!-- <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Institutions management" {{ $data->sectionCheck('Institutions management') ? 'checked' : '' }} class="custom-control-input" id="manage_staff">
                  <label class="custom-control-label" for="manage_staff">{{__('Institutions management')}}</label>
                  </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Module management" {{ $data->sectionCheck('Module management') ? 'checked' : '' }} class="custom-control-input" id="manage_roles">
                  <label class="custom-control-label" for="manage_roles">{{__('Module management')}}</label>
                  </div>
              </div>
            </div> -->

            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Manage Customers" {{ $data->sectionCheck('Manage Customers') ? 'checked' : '' }} class="custom-control-input" id="manage_customers">
                  <label class="custom-control-label" for="manage_customers">{{__('Manage Customers')}}</label>
                  </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Loan Management" {{ $data->sectionCheck('Loan Management') ? 'checked' : '' }} class="custom-control-input" id="loan_management">
                  <label class="custom-control-label" for="loan_management">{{__('Loan Management')}}</label>
                  </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="DPS Management" {{ $data->sectionCheck('DPS Management') ? 'checked' : '' }} class="custom-control-input" id="dps_management">
                  <label class="custom-control-label" for="dps_management">{{__('DPS Management')}}</label>
                  </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="FDR Management" {{ $data->sectionCheck('FDR Management') ? 'checked' : '' }} class="custom-control-input" id="fdr_management">
                  <label class="custom-control-label" for="fdr_management">{{__('FDR Management')}}</label>
                  </div>
              </div>
            </div>

            <!-- <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Other Banks" {{ $data->sectionCheck('Other Banks') ? 'checked' : '' }} class="custom-control-input" id="other_banks">
                  <label class="custom-control-label" for="other_banks">{{__('Other Banks')}}</label>
                  </div>
              </div>
            </div> -->

            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Manage Charges" {{ $data->sectionCheck('Manage Charges') ? 'checked' : '' }} class="custom-control-input" id="manage_charges">
                  <label class="custom-control-label" for="manage_charges">{{__('Manage Charges')}}</label>
                  </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Manage Escrow" {{ $data->sectionCheck('Manage Escrow') ? 'checked' : '' }} class="custom-control-input" id="manage_escrow">
                  <label class="custom-control-label" for="manage_escrow">{{__('Manage Escrow')}}</label>
                  </div>
              </div>
            </div>


            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Money Transfer" {{ $data->sectionCheck('Money Transfer') ? 'checked' : '' }} class="custom-control-input" id="money_transfer">
                  <label class="custom-control-label" for="money_transfer">{{__('Money Transfer')}}</label>
                  </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Wire Transfer" {{ $data->sectionCheck('Wire Transfer') ? 'checked' : '' }} class="custom-control-input" id="wire_transfer">
                  <label class="custom-control-label" for="wire_transfer">{{__('Wire Transfer')}}</label>
                  </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Request Money" {{ $data->sectionCheck('Request Money') ? 'checked' : '' }} class="custom-control-input" id="request_money">
                  <label class="custom-control-label" for="request_money">{{__('Request Money')}}</label>
                  </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Transactions" {{ $data->sectionCheck('Transactions') ? 'checked' : '' }} class="custom-control-input" id="transactions">
                  <label class="custom-control-label" for="transactions">{{__('Transactions')}}</label>
                  </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Deposits" {{ $data->sectionCheck('Deposits') ? 'checked' : '' }} class="custom-control-input" id="Deposits">
                  <label class="custom-control-label" for="Deposits">{{__('Deposits')}}</label>
                  </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Payment Setting" {{ $data->sectionCheck('Payment Setting') ? 'checked' : '' }} class="custom-control-input" id="payment_setting">
                  <label class="custom-control-label" for="payment_setting">{{__('Payment Setting')}}</label>
                  </div>
              </div>
            </div>

            <!-- <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Manage Blog" {{ $data->sectionCheck('Manage Blog') ? 'checked' : '' }} class="custom-control-input" id="manage_blog">
                  <label class="custom-control-label" for="manage_blog">{{__('Manage Blog')}}</label>
                  </div>
              </div>
            </div> -->

            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="General Setting" {{ $data->sectionCheck('General Setting') ? 'checked' : '' }} class="custom-control-input" id="general_setting">
                  <label class="custom-control-label" for="general_setting">{{__('General Setting')}}</label>
                  </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Homepage Manage" {{ $data->sectionCheck('Homepage Manage') ? 'checked' : '' }} class="custom-control-input" id="homepage_manage">
                  <label class="custom-control-label" for="homepage_manage">{{__('Homepage Manage')}}</label>
                  </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Email Setting" {{ $data->sectionCheck('Email Setting') ? 'checked' : '' }} class="custom-control-input" id="email_setting">
                  <label class="custom-control-label" for="email_setting">{{__('Email Setting')}}</label>
                  </div>
              </div>
            </div>

            <!-- <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Message" {{ $data->sectionCheck('Message') ? 'checked' : '' }} class="custom-control-input" id="Message">
                  <label class="custom-control-label" for="Message">{{__('Message')}}</label>
                  </div>
              </div>
            </div> -->

            <!-- <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Manage KYC Form" {{ $data->sectionCheck('Manage KYC Form') ? 'checked' : '' }} class="custom-control-input" id="manage_kyc">
                  <label class="custom-control-label" for="manage_kyc">{{__('Manage KYC Form')}}</label>
                  </div>
              </div>
            </div> -->
            
            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Language Manage" {{ $data->sectionCheck('Language Manage') ? 'checked' : '' }} class="custom-control-input" id="language_setting">
                  <label class="custom-control-label" for="language_setting">{{__('Language Manage')}}</label>
                  </div>
              </div>
            </div>

            <!-- <div class="col-md-6">
                <div class="form-group">
                  <div class="custom-control custom-switch">
                    <input type="checkbox" name="section[]" value="Fonts" {{ $data->sectionCheck('Institutions management') ? 'checked' : '' }} class="custom-control-input" id="font">
                    <label class="custom-control-label" for="font">{{__('Fonts')}}</label>
                    </div>
                </div>
            </div> -->

            <!-- <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Menupage Setting" {{ $data->sectionCheck('Menupage Setting') ? 'checked' : '' }} class="custom-control-input" id="menupage_setting">
                  <label class="custom-control-label" for="menupage_setting">{{__('Menupage Setting')}}</label>
                  </div>
              </div>
            </div> -->

            <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>

        </form>
      </div>
    </div>
  </div>

</div>
<!--Row-->

@endsection
