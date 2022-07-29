@extends('layouts.admin')

@section('content')

<div class="card">
    <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Edit KYC Module') }} </h5>
    <ol class="breadcrumb py-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="javascript:;">{{ __('Manage User KYC Module') }}</a></li>
    </ol>
    </div>
</div>

    <div class="card mb-4 mt-3">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">{{ __('Edit User KYC Module') }}</h6>
      </div>

      <div class="card-body">
        <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
        <form class="geniusform" action="{{route('admin.gs.update')}}" method="POST" enctype="multipart/form-data">

            @include('includes.admin.form-both')

            {{ csrf_field() }}

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="module_section[]" value="Loan" {{ $data->sectionCheck('Loan') ? 'checked' : '' }} class="custom-control-input" id="Loan">
                  <label class="custom-control-label" for="Loan">{{__('Loan')}}</label>
                  </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="module_section[]" value="DPS" {{ $data->sectionCheck('DPS') ? 'checked' : '' }} class="custom-control-input" id="DPS">
                  <label class="custom-control-label" for="DPS">{{__('DPS')}}</label>
                  </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="module_section[]" value="FDR" {{ $data->sectionCheck('FDR') ? 'checked' : '' }} class="custom-control-input" id="FDR">
                  <label class="custom-control-label" for="FDR">{{__('FDR')}}</label>
                  </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="module_section[]" value="External Payment" {{ $data->sectionCheck('External Payment') ? 'checked' : '' }} class="custom-control-input" id="External Payment">
                  <label class="custom-control-label" for="External Payment">{{__('External Payment')}}</label>
                  </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="module_section[]" value="Request Money" {{ $data->sectionCheck('Request Money') ? 'checked' : '' }} class="custom-control-input" id="Request Money">
                  <label class="custom-control-label" for="Request Money">{{__('Request Money')}}</label>
                  </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="module_section[]" value="Voucher" {{ $data->sectionCheck('Voucher') ? 'checked' : '' }} class="custom-control-input" id="Voucher">
                  <label class="custom-control-label" for="Voucher">{{__('Voucher')}}</label>
                  </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="module_section[]" value="Invoice" {{ $data->sectionCheck('Invoice') ? 'checked' : '' }} class="custom-control-input" id="Invoice">
                  <label class="custom-control-label" for="Invoice">{{__('Invoice')}}</label>
                  </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="module_section[]" value="Escrow" {{ $data->sectionCheck('Escrow') ? 'checked' : '' }} class="custom-control-input" id="Escrow">
                  <label class="custom-control-label" for="Escrow">{{__('Escrow')}}</label>
                  </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="module_section[]" value="Exchange Money" {{ $data->sectionCheck('Exchange Money') ? 'checked' : '' }} class="custom-control-input" id="Exchange Money">
                  <label class="custom-control-label" for="Exchange Money">{{__('Exchange Money')}}</label>
                  </div>
              </div>
            </div>

          </div>
            


            <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>

        </form>
      </div>
    </div>

@endsection
