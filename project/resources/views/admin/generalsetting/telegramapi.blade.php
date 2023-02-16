@extends('layouts.admin')

@section('content')

<div class="card">
    <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Telegram API') }}</h5>
        <ol class="breadcrumb py-0 m-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.gs.ibanapi') }}">{{ __('API Settings') }}</a></li>
        </ol>
    </div>
</div>

  <div class="card mb-4 mt-3">
    @include('admin.system.systemapitab')

    <div class="p-3">
        <div class="card mt-3 p-3">
            <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);">
            </div>
            <form class="geniusform ms-2" action="{{ route('admin.gs.update') }}" method="POST" enctype="multipart/form-data">

                @include('includes.admin.form-both')

                {{ csrf_field() }}

                <div class="row">
                      <div class="col-md-12">
                        <label for="inp-section">{{  __('Telegram Section')  }}</label>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <div class="custom-control custom-switch">
                            <input type="checkbox" name="telegram_section[]" value="Bank Transfer" {{ $data->telegram_section_check('Bank Transfer') ? 'checked' : '' }} class="custom-control-input" id="bank_transfer">
                            <label class="custom-control-label" for="bank_transfer">{{__('Bank Transfer')}}</label>
                          </div>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="form-group">
                          <div class="custom-control custom-switch">
                            <input type="checkbox" name="telegram_section[]" value="Deposit Bank" {{ $data->telegram_section_check('Deposit Bank') ? 'checked' : '' }} class="custom-control-input" id="deposit_bank">
                            <label class="custom-control-label" for="deposit_bank">{{__('Deposit Bank')}}</label>
                          </div>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="form-group">
                          <div class="custom-control custom-switch">
                            <input type="checkbox" name="telegram_section[]" value="Merchant Shop" {{ $data->telegram_section_check('Merchant Shop') ? 'checked' : '' }} class="custom-control-input" id="merchant_shop">
                            <label class="custom-control-label" for="merchant_shop">{{__('Merchant Shop')}}</label>
                          </div>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="form-group">
                          <div class="custom-control custom-switch">
                            <input type="checkbox" name="telegram_section[]" value="AML/KYC" {{ $data->telegram_section_check('AML/KYC') ? 'checked' : '' }} class="custom-control-input" id="aml">
                            <label class="custom-control-label" for="aml">{{__('AML/KYC')}}</label>
                          </div>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="form-group">
                          <div class="custom-control custom-switch">
                            <input type="checkbox" name="telegram_section[]" value="Loan" {{ $data->telegram_section_check('Loan') ? 'checked' : '' }} class="custom-control-input" id="Loan">
                            <label class="custom-control-label" for="Loan">{{__('Loan')}}</label>
                          </div>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="form-group">
                          <div class="custom-control custom-switch">
                            <input type="checkbox" name="telegram_section[]" value="Dps" {{ $data->telegram_section_check('Dps') ? 'checked' : '' }} class="custom-control-input" id="Dps">
                            <label class="custom-control-label" for="Dps">{{__('Dps')}}</label>
                          </div>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="form-group">
                          <div class="custom-control custom-switch">
                            <input type="checkbox" name="telegram_section[]" value="Fdr" {{ $data->telegram_section_check('Fdr') ? 'checked' : '' }} class="custom-control-input" id="Fdr">
                            <label class="custom-control-label" for="Fdr">{{__('Fdr')}}</label>
                          </div>
                        </div>
                      </div>

                      <div class="col-md-6">
                          <div class="form-group">
                            <div class="custom-control custom-switch">
                              <input type="checkbox" name="telegram_section[]" value="ICO" {{ $data->telegram_section_check('ICO') ? 'checked' : '' }} class="custom-control-input" id="ico">
                              <label class="custom-control-label" for="ico">{{__('ICO')}}</label>
                            </div>
                          </div>
                      </div>

                      <div class="col-md-6">
                        <div class="form-group">
                          <div class="custom-control custom-switch">
                            <input type="checkbox" name="telegram_section[]" value="Escrow" {{ $data->telegram_section_check('Escrow') ? 'checked' : '' }} class="custom-control-input" id="Escrow">
                            <label class="custom-control-label" for="Escrow">{{__('Escrow')}}</label>
                          </div>
                        </div>
                      </div>

                      <div class="col-md-6">
                          <div class="form-group">
                            <div class="custom-control custom-switch">
                              <input type="checkbox" name="telegram_section[]" value="Campaign" {{ $data->telegram_section_check('Campaign') ? 'checked' : '' }} class="custom-control-input" id="campaign">
                              <label class="custom-control-label" for="campaign">{{__('Campaign')}}</label>
                            </div>
                          </div>
                      </div>

                      <div class="col-md-6">
                        <div class="form-group">
                          <div class="custom-control custom-switch">
                            <input type="checkbox" name="telegram_section[]" value="Donation" {{ $data->telegram_section_check('Donation') ? 'checked' : '' }} class="custom-control-input" id="donation">
                            <label class="custom-control-label" for="donation">{{__('Donation')}}</label>
                          </div>
                        </div>
                      </div>

                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="inp-key">{{  __('Token')  }}</label>
                            <input type="text"  class="form-control" id="inp-key" name="telegram_token"  placeholder="{{ __('Telegram Token') }}" value="{{ $gs->telegram_token }}">
                        </div>
                    </div>
                </div>
                <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>

            </form>
        </div>
    </div>
  </div>

@endsection
