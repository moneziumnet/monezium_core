@extends('layouts.admin')

@section('content')

<div class="card">
    <div class="d-sm-flex align-items-center justify-content-between py-3">
        <h5 class=" mb-0 text-gray-800 pl-3">{{ __('User Modules') }} </h5>
        <ol class="breadcrumb py-0 m-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="javascript:;">{{ __('User Modules') }}</a></li>
        </ol>
    </div>
</div>

    <div class="card mb-4 mt-3">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">{{ __('User Modules') }}</h6>
      </div>

      <div class="card-body">
        <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
        <form class="geniusform" action="{{route('admin.gs.update')}}" method="POST" enctype="multipart/form-data">

            @include('includes.admin.form-both')

            {{ csrf_field() }}

          <div class="row">
          @if(Auth::guard('admin')->user()->sectionCheck('Loan Management'))
            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="user_module[]" value="Loan" {{ $data->moduleCheck('Loan') ? 'checked' : '' }} class="custom-control-input" id="Loan">
                  <label class="custom-control-label" for="Loan">{{__('Loan')}}</label>
                  </div>
              </div>
            </div>
            @endif
            @if(Auth::guard('admin')->user()->sectionCheck('DPS Management'))
            <div class="col-md-6">
                <div class="form-group">
                  <div class="custom-control custom-switch">
                    <input type="checkbox" name="user_module[]" value="DPS" {{ $data->moduleCheck('DPS') ? 'checked' : '' }} class="custom-control-input" id="DPS">
                    <label class="custom-control-label" for="DPS">{{__('DPS')}}</label>
                    </div>
                </div>
            </div>
            @endif
            @if(Auth::guard('admin')->user()->sectionCheck('FDR Management'))
            <div class="col-md-6">
                <div class="form-group">
                  <div class="custom-control custom-switch">
                    <input type="checkbox" name="user_module[]" value="FDR" {{ $data->moduleCheck('FDR') ? 'checked' : '' }} class="custom-control-input" id="FDR">
                    <label class="custom-control-label" for="FDR">{{__('FDR')}}</label>
                    </div>
                </div>
            </div>
            @endif
            @if(Auth::guard('admin')->user()->sectionCheck('Request Money'))
            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="user_module[]" value="Request Money" {{ $data->moduleCheck('Request Money') ? 'checked' : '' }} class="custom-control-input" id="Request Money">
                  <label class="custom-control-label" for="Request Money">{{__('Request Money')}}</label>
                  </div>
              </div>
            </div>
            @endif
            @if(Auth::guard('admin')->user()->sectionCheck('Incoming'))
            <div class="col-md-6">
                <div class="form-group">
                  <div class="custom-control custom-switch">
                    <input type="checkbox" name="user_module[]" value="Incoming" {{ $data->moduleCheck('Incoming') ? 'checked' : '' }} class="custom-control-input" id="Incoming">
                    <label class="custom-control-label" for="Incoming">{{__('Incoming')}}</label>
                    </div>
                </div>
            </div>
            @endif

            @if(Auth::guard('admin')->user()->sectionCheck('Bank Transfer'))
            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="user_module[]" value="Bank Transfer" {{ $data->moduleCheck('Bank Transfer') ? 'checked' : '' }} class="custom-control-input" id="Bank Transfer">
                  <label class="custom-control-label" for="Transfer">{{__('Bank Transfer')}}</label>
                  </div>
              </div>
            </div>
            @endif
            @if(Auth::guard('admin')->user()->sectionCheck('Withdraw'))
            <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="user_module[]" value="Withdraw" {{ $data->moduleCheck('Withdraw') ? 'checked' : '' }} class="custom-control-input" id="Withdraw">
                  <label class="custom-control-label" for="Withdraw">{{__('Withdraw')}}</label>
                  </div>
              </div>
            </div>
            @endif
            
            <div class="col-md-6">
                <div class="form-group">
                  <div class="custom-control custom-switch">
                    <input type="checkbox" name="user_module[]" value="Pricing Plan" {{ $data->moduleCheck('Pricing Plan') ? 'checked' : '' }} class="custom-control-input" id="pricing_plan">
                    <label class="custom-control-label" for="pricing_plan">{{__('Pricing Plan')}}</label>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                  <div class="custom-control custom-switch">
                    <input type="checkbox" name="user_module[]" value="Voucher" {{ $data->moduleCheck('Voucher') ? 'checked' : '' }} class="custom-control-input" id="voucher">
                    <label class="custom-control-label" for="voucher">{{__('Voucher')}}</label>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                  <div class="custom-control custom-switch">
                    <input type="checkbox" name="user_module[]" value="Invoice" {{ $data->moduleCheck('Invoice') ? 'checked' : '' }} class="custom-control-input" id="invoice">
                    <label class="custom-control-label" for="invoice">{{__('Invoice')}}</label>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                  <div class="custom-control custom-switch">
                    <input type="checkbox" name="user_module[]" value="Escrow" {{ $data->moduleCheck('Escrow') ? 'checked' : '' }} class="custom-control-input" id="escrow1">
                    <label class="custom-control-label" for="escrow1">{{__('Escrow')}}</label>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                  <div class="custom-control custom-switch">
                    <input type="checkbox" name="user_module[]" value="Exchange Money" {{ $data->moduleCheck('Exchange Money') ? 'checked' : '' }} class="custom-control-input" id="exchange-money">
                    <label class="custom-control-label" for="exchange-money">{{__('Exchange Money')}}</label>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                  <div class="custom-control custom-switch">
                    <input type="checkbox" name="user_module[]" value="More" {{ $data->moduleCheck('More') ? 'checked' : '' }} class="custom-control-input" id="more">
                    <label class="custom-control-label" for="more">{{__('More')}}</label>
                    </div>
                </div>
            </div>
          </div>
            

            <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>

        </form>
      </div>
    </div>

@endsection
