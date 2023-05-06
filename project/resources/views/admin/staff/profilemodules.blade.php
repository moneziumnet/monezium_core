@extends('layouts.admin')
@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __($data->name) }} <a class="btn btn-primary btn-rounded btn-sm ml-3" href="{{route('admin.staff.index')}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
    <ol class="breadcrumb py-0 m-0">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.staff.index') }}">{{ __('Staff Management') }}</a></li>
    </ol>
  </div>
</div>


<div class="row mt-3">
  <div class="col-lg-12">
    <div class="card mt-1 tab-card">

      <div class="tab-content" id="myTabContent">
        @php
        $currency = defaultCurr();
        @endphp
        @include('includes.admin.form-success')
        <div class="tab-pane fade show active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
          <div class="card-body">
            <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
                <form class="geniusform" action="{{ route('admin-staff-updatemodules',$data->id) }}" method="POST" enctype="multipart/form-data">

                @include('includes.admin.form-both')

                {{ csrf_field() }}
                <div class="d-flex flex-row align-items-center justify-content-between">
                    <h4 class="m-0 font-weight-bold">{{__('Main Module')}}</h6>
                </div>
                <hr>
                <div class="row pl-2">
                    <div class="col-md-6">
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" name="section[]" value="Sub Institutions management" {{ $data->sectionCheck('Sub Institutions management') ? 'checked' : '' }} class="custom-control-input" id="manage_ins">
                          <label class="custom-control-label" for="manage_ins">{{__('Sub Institutions management')}}</label>
                        </div>
                      </div>
                    </div>
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
                          <input type="checkbox" name="section[]" value="Manage Pricing Plan" {{ $data->sectionCheck('Manage Pricing Plan') ? 'checked' : '' }} class="custom-control-input" id="manage_pricing_plan">
                          <label class="custom-control-label" for="manage_pricing_plan">{{__('Manage Pricing Plan')}}</label>
                        </div>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" name="section[]" value="KYC Management" {{ $data->sectionCheck('KYC Management') ? 'checked' : '' }} class="custom-control-input" id="manage_kyc">
                          <label class="custom-control-label" for="manage_kyc">{{__('AML/KYC Management')}}</label>
                        </div>
                      </div>
                    </div>
    
                    <div class="col-md-6">
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" name="section[]" value="Crypto Management" {{ $data->sectionCheck('Crypto Management') ? 'checked' : '' }} class="custom-control-input" id="crypto_management">
                          <label class="custom-control-label" for="crypto_management">{{__('Crypto Management')}}</label>
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
    
                    <div class="col-md-6">
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" name="section[]" value="ICO Management" {{ $data->sectionCheck('ICO Management') ? 'checked' : '' }} class="custom-control-input" id="manage_ico">
                          <label class="custom-control-label" for="manage_ico">{{__('ICO Management')}}</label>
                        </div>
                      </div>
                    </div>
    
                    <div class="col-md-6">
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" name="section[]" value="Report" {{ $data->sectionCheck('Report') ? 'checked' : '' }} class="custom-control-input" id="manage_report">
                          <label class="custom-control-label" for="manage_report">{{__('Report')}}</label>
                        </div>
                      </div>
                    </div>
    
                    <div class="col-md-6">
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" name="section[]" value="Deposits" {{ $data->sectionCheck('Deposits') ? 'checked' : '' }} class="custom-control-input" id="management_deposits">
                          <label class="custom-control-label" for="management_deposits">{{__('Deposits')}}</label>
                        </div>
                      </div>
                    </div>
    
                    <div class="col-md-6">
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" name="section[]" value="Withdraw" {{ $data->sectionCheck('Withdraw') ? 'checked' : '' }} class="custom-control-input" id="management_withdraw">
                          <label class="custom-control-label" for="management_withdraw">{{__('Withdraw')}}</label>
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
                          <input type="checkbox" name="section[]" value="Bank Transfer" {{ $data->sectionCheck('Bank Transfer') ? 'checked' : '' }} class="custom-control-input" id="money_transfer">
                          <label class="custom-control-label" for="money_transfer">{{__('Bank Transfer')}}</label>
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
                          <input type="checkbox" name="section[]" value="Notification" {{ $data->sectionCheck('Notification') ? 'checked' : '' }} class="custom-control-input" id="notification">
                          <label class="custom-control-label" for="notification">{{__('Notification')}}</label>
                        </div>
                      </div>
                    </div>
    
                    <div class="col-md-6">
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" name="section[]" value="Currency Setting" {{ $data->sectionCheck('Currency Setting') ? 'checked' : '' }} class="custom-control-input" id="currency_setting">
                          <label class="custom-control-label" for="currency_setting">{{__('Currency Setting')}}</label>
                        </div>
                      </div>
                    </div>
    
                    <div class="col-md-6">
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" name="section[]" value="Manage Blog" {{ $data->sectionCheck('Manage Blog') ? 'checked' : '' }} class="custom-control-input" id="manage_blog">
                          <label class="custom-control-label" for="manage_blog">{{__('Manage Blog')}}</label>
                        </div>
                      </div>
                    </div>
    
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
                          <input type="checkbox" name="section[]" value="Home page Setting" {{ $data->sectionCheck('Home page Setting') ? 'checked' : '' }} class="custom-control-input" id="homepage_manage">
                          <label class="custom-control-label" for="homepage_manage">{{__('Home page Setting')}}</label>
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
    
                    <div class="col-md-6">
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" name="section[]" value="Crowdfunding" {{ $data->sectionCheck('Crowdfunding') ? 'checked' : '' }} class="custom-control-input" id="crowdfunding">
                          <label class="custom-control-label" for="crowdfunding">{{__('Crowdfunding')}}</label>
                        </div>
                      </div>
                    </div>
    
                    <div class="col-md-6">
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" name="section[]" value="Support Ticket" {{ $data->sectionCheck('Support Ticket') ? 'checked' : '' }} class="custom-control-input" id="support_ticket">
                          <label class="custom-control-label" for="support_ticket">{{__('Support Ticket')}}</label>
                        </div>
                      </div>
                    </div>
    
                    <div class="col-md-6">
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" name="section[]" value="Language Manage" {{ $data->sectionCheck('Language Manage') ? 'checked' : '' }} class="custom-control-input" id="language_setting">
                          <label class="custom-control-label" for="language_setting">{{__('Language Manage')}}</label>
                        </div>
                      </div>
                    </div>
                </div>
                <div class="d-flex flex-row align-items-center justify-content-between mt-3">
                    <h4 class="m-0 font-weight-bold">{{__('Customer Profile Module')}}</h6>
                </div>
                <hr>
                <div class="row pl-2">
                    <div class="col-md-6">
                        <div class="form-group">
                          <div class="custom-control custom-switch">
                            <input type="checkbox" name="section[]" value="Information" {{ $data->sectionCheck('Information') ? 'checked' : '' }} class="custom-control-input" id="customer_inf">
                            <label class="custom-control-label" for="customer_inf">{{__('Information')}}</label>
                          </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <div class="custom-control custom-switch">
                            <input type="checkbox" name="section[]" value="Accounts" {{ $data->sectionCheck('Accounts') ? 'checked' : '' }} class="custom-control-input" id="customer_wallet">
                            <label class="custom-control-label" for="customer_wallet">{{__('Accounts')}}</label>
                          </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <div class="custom-control custom-switch">
                            <input type="checkbox" name="section[]" value="Documents" {{ $data->sectionCheck('Documents') ? 'checked' : '' }} class="custom-control-input" id="customer_doc">
                            <label class="custom-control-label" for="customer_doc">{{__('Documents')}}</label>
                          </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <div class="custom-control custom-switch">
                            <input type="checkbox" name="section[]" value="Setting" {{ $data->sectionCheck('Setting') ? 'checked' : '' }} class="custom-control-input" id="customer_setting">
                            <label class="custom-control-label" for="customer_setting">{{__('Setting')}}</label>
                          </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <div class="custom-control custom-switch">
                            <input type="checkbox" name="section[]" value="Pricing Plan" {{ $data->sectionCheck('Pricing Plan') ? 'checked' : '' }} class="custom-control-input" id="customer_price">
                            <label class="custom-control-label" for="customer_price">{{__('Pricing Plan')}}</label>
                          </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <div class="custom-control custom-switch">
                            <input type="checkbox" name="section[]" value="Customer Transactions" {{ $data->sectionCheck('Customer Transactions') ? 'checked' : '' }} class="custom-control-input" id="customer_transaction">
                            <label class="custom-control-label" for="customer_transaction">{{__('Customer Transactions')}}</label>
                          </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <div class="custom-control custom-switch">
                            <input type="checkbox" name="section[]" value="Banks" {{ $data->sectionCheck('Banks') ? 'checked' : '' }} class="custom-control-input" id="customer_banks">
                            <label class="custom-control-label" for="customer_banks">{{__('Banks')}}</label>
                          </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <div class="custom-control custom-switch">
                            <input type="checkbox" name="section[]" value="Modules" {{ $data->sectionCheck('Modules') ? 'checked' : '' }} class="custom-control-input" id="customer_modules">
                            <label class="custom-control-label" for="customer_modules">{{__('Modules')}}</label>
                          </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <div class="custom-control custom-switch">
                            <input type="checkbox" name="section[]" value="IBAN" {{ $data->sectionCheck('IBAN') ? 'checked' : '' }} class="custom-control-input" id="customer_iban">
                            <label class="custom-control-label" for="customer_iban">{{__('IBAN')}}</label>
                          </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <div class="custom-control custom-switch">
                            <input type="checkbox" name="section[]" value="Contract" {{ $data->sectionCheck('Contract') ? 'checked' : '' }} class="custom-control-input" id="customer_contract">
                            <label class="custom-control-label" for="customer_contract">{{__('Contract')}}</label>
                          </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <div class="custom-control custom-switch">
                            <input type="checkbox" name="section[]" value="Merchant Shop" {{ $data->sectionCheck('Merchant Shop') ? 'checked' : '' }} class="custom-control-input" id="customer_shop">
                            <label class="custom-control-label" for="customer_shop">{{__('Merchant Shop')}}</label>
                          </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <div class="custom-control custom-switch">
                            <input type="checkbox" name="section[]" value="AML/KYC" {{ $data->sectionCheck('AML/KYC') ? 'checked' : '' }} class="custom-control-input" id="customer_kyc">
                            <label class="custom-control-label" for="customer_kyc">{{__('AML/KYC')}}</label>
                          </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <div class="custom-control custom-switch">
                            <input type="checkbox" name="section[]" value="Beneficiary" {{ $data->sectionCheck('Beneficiary') ? 'checked' : '' }} class="custom-control-input" id="customer_bene">
                            <label class="custom-control-label" for="customer_bene">{{__('Beneficiary')}}</label>
                          </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <div class="custom-control custom-switch">
                            <input type="checkbox" name="section[]" value="Layer" {{ $data->sectionCheck('Layer') ? 'checked' : '' }} class="custom-control-input" id="customer_layer">
                            <label class="custom-control-label" for="customer_layer">{{__('Layer')}}</label>
                          </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <div class="custom-control custom-switch">
                            <input type="checkbox" name="section[]" value="Login History" {{ $data->sectionCheck('Login History') ? 'checked' : '' }} class="custom-control-input" id="customer_login">
                            <label class="custom-control-label" for="customer_login">{{__('Login History')}}</label>
                          </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" name="section[]" value="Customer Balance" {{ $data->sectionCheck('Customer Balance') ? 'checked' : '' }} class="custom-control-input" id="customer_balance">
                          <label class="custom-control-label" for="customer_balance">{{__('Customer Balance')}}</label>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" name="section[]" value="Dashboard" {{ $data->sectionCheck('Dashboard') ? 'checked' : '' }} class="custom-control-input" id="dashboard">
                          <label class="custom-control-label" for="dashboard">{{__('Dashboard')}}</label>
                        </div>
                      </div>
                    </div>
                </div>
                <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>
                </form>
        </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!--Row-->
@endsection
