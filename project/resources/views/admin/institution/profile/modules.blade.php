@extends('layouts.admin')
@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Profile of Institution') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.institution.index')}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
    <h5 class=" mb-0 text-gray-800 pl-3">{{ $data->name }}</h5>
    <ol class="breadcrumb py-0 m-0">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.institution.index') }}">{{ __('Institutions List') }}</a></li>

    </ol>
  </div>
</div>

<div class="row mt-3">
  <div class="col-lg-12">
    <div class="card mt-1 tab-card">
      @include('admin.institution.profile.tab')

      <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show p-3 active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
          <div class="card-body">
            <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
            <form class="geniusform" action="{{route('admin.institution.moduleupdate',$data->id)}}" method="POST" enctype="multipart/form-data">
            @include('includes.admin.form-both')
              {{ csrf_field() }}
              <div class="row">
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
                      <input type="checkbox" name="section[]" value="Management Withdraw" {{ $data->sectionCheck('Management Withdraw') ? 'checked' : '' }} class="custom-control-input" id="management_withdraw">
                      <label class="custom-control-label" for="management_withdraw">{{__('Management Withdraw')}}</label>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <div class="custom-control custom-switch">
                      <input type="checkbox" name="section[]" value="Management Deposit" {{ $data->sectionCheck('Management Deposit') ? 'checked' : '' }} class="custom-control-input" id="management_deposit">
                      <label class="custom-control-label" for="management_deposit">{{__('Management Deposit')}}</label>
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
                      <input type="checkbox" name="section[]" value="Currency Setting" {{ $data->sectionCheck('Currency Setting') ? 'checked' : '' }} class="custom-control-input" id="currency_setting">
                      <label class="custom-control-label" for="currency_setting">{{__('Currency Setting')}}</label>
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

                <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Manage KYC Form" {{ $data->sectionCheck('Manage KYC Form') ? 'checked' : '' }} class="custom-control-input" id="manage_kyc">
                  <label class="custom-control-label" for="manage_kyc">{{__('Manage KYC Form')}}</label>
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
              <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>

@endsection
@section('scripts')
<script type="text/javascript">
</script>
@endsection
