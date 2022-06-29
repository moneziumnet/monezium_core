@extends('layouts.admin')
@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ $data->name }}</h5>
    <ol class="breadcrumb py-0 m-0">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.user.index') }}">{{ __('User List') }}</a></li>
    </ol>
  </div>
</div>


<div class="row mt-3">
  <div class="col-lg-12">
    <div class="card mt-1 tab-card">
      @include('admin.user.profiletab')

      <div class="tab-content" id="myTabContent">
        @php
        $currency = defaultCurr();
        @endphp
        @include('includes.admin.form-success')
        <div class="tab-pane fade show p-3 active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
        <div class="card-body">
        <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
        <form class="geniusform" action="{{ route('admin-user-updatemodules',$data->id) }}" method="POST" enctype="multipart/form-data">

            @include('includes.admin.form-both')

            {{ csrf_field() }}

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Loan" {{ $data->sectionCheck('Loan') ? 'checked' : '' }} class="custom-control-input" id="Loan">
                  <label class="custom-control-label" for="Loan">{{__('Loan')}}</label>
                  </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="DPS" {{ $data->sectionCheck('DPS') ? 'checked' : '' }} class="custom-control-input" id="DPS">
                  <label class="custom-control-label" for="DPS">{{__('DPS')}}</label>
                  </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="FDR" {{ $data->sectionCheck('FDR') ? 'checked' : '' }} class="custom-control-input" id="FDR">
                  <label class="custom-control-label" for="FDR">{{__('FDR')}}</label>
                  </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Request Money" {{ $data->sectionCheck('Request Money') ? 'checked' : '' }} class="custom-control-input" id="Request Money">
                  <label class="custom-control-label" for="Request Money">{{__('Request Money')}}</label>
                  </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Wire Transfer" {{ $data->sectionCheck('Wire Transfer') ? 'checked' : '' }} class="custom-control-input" id="Wire Transfer">
                  <label class="custom-control-label" for="Wire Transfer">{{__('Wire Transfer')}}</label>
                  </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Transfer" {{ $data->sectionCheck('Transfer') ? 'checked' : '' }} class="custom-control-input" id="Transfer">
                  <label class="custom-control-label" for="Transfer">{{__('Transfer')}}</label>
                  </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Deposit" {{ $data->sectionCheck('Deposit') ? 'checked' : '' }} class="custom-control-input" id="Deposit">
                  <label class="custom-control-label" for="Deposit">{{__('Deposit')}}</label>
                  </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Withdraw" {{ $data->sectionCheck('Withdraw') ? 'checked' : '' }} class="custom-control-input" id="Withdraw">
                  <label class="custom-control-label" for="Withdraw">{{__('Withdraw')}}</label>
                  </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Voucher" {{ $data->sectionCheck('Voucher') ? 'checked' : '' }} class="custom-control-input" id="Voucher">
                  <label class="custom-control-label" for="Voucher">{{__('Voucher')}}</label>
                  </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Invoice" {{ $data->sectionCheck('Invoice') ? 'checked' : '' }} class="custom-control-input" id="Invoice">
                  <label class="custom-control-label" for="Invoice">{{__('Invoice')}}</label>
                  </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Escrow" {{ $data->sectionCheck('Escrow') ? 'checked' : '' }} class="custom-control-input" id="Escrow">
                  <label class="custom-control-label" for="Escrow">{{__('Escrow')}}</label>
                  </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Exchange Money" {{ $data->sectionCheck('Exchange Money') ? 'checked' : '' }} class="custom-control-input" id="Exchange Money">
                  <label class="custom-control-label" for="Exchange Money">{{__('Exchange Money')}}</label>
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
<!--Row-->
@endsection
