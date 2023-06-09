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
                  <input type="checkbox" name="module_section[]" value="Payments" {{ $data->sectionCheck('Payments') ? 'checked' : '' }} class="custom-control-input" id="Payments">
                  <label class="custom-control-label" for="Payments">{{__('Payments')}}</label>
                  </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="module_section[]" value="Incoming" {{ $data->sectionCheck('Incoming') ? 'checked' : '' }} class="custom-control-input" id="Incoming">
                  <label class="custom-control-label" for="Incoming">{{__('Incoming')}}</label>
                  </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="module_section[]" value="External Payments" {{ $data->sectionCheck('External Payments') ? 'checked' : '' }} class="custom-control-input" id="External Payments">
                  <label class="custom-control-label" for="External Payments">{{__('External Payments')}}</label>
                  </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="module_section[]" value="Payment between accounts" {{ $data->sectionCheck('Payment between accounts') ? 'checked' : '' }} class="custom-control-input" id="Payment between accounts">
                  <label class="custom-control-label" for="Payment between accounts">{{__('Payment between accounts')}}</label>
                  </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="module_section[]" value="Internal Payment" {{ $data->sectionCheck('Internal Payment') ? 'checked' : '' }} class="custom-control-input" id="Internal Payment">
                  <label class="custom-control-label" for="Internal Payment">{{__('Internal Payment')}}</label>
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
                  <input type="checkbox" name="module_section[]" value="Exchange Money" {{ $data->sectionCheck('Exchange Money') ? 'checked' : '' }} class="custom-control-input" id="Exchange Money">
                  <label class="custom-control-label" for="Exchange Money">{{__('Exchange Money')}}</label>
                  </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="module_section[]" value="Transactions" {{ $data->sectionCheck('Transactions') ? 'checked' : '' }} class="custom-control-input" id="Transactions">
                  <label class="custom-control-label" for="Transactions">{{__('Transactions')}}</label>
                  </div>
              </div>
            </div>
          </div>
            


            <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>

        </form>
      </div>
    </div>

@endsection
