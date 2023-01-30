@extends('layouts.admin')

@section('content')
<div class="card">
  <div class="d-sm-flex align-items-center py-3 justify-content-between">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Bank\'s Account') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.subinstitution.banks',$data->ins_id)}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
    <ol class="breadcrumb py-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.subinstitution.index') }}">{{ __('Sub Institutions List') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.subinstitution.banks',$data->ins_id)}}">{{ __('Banks List') }}</a></li>
    </ol>
  </div>
</div>

<div class="row justify-content-center mt-3">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('Show Bank Account') }}</h6>
            </div>

            <div class="card-body">
                <div class="row mb-3">
                    <div class="ml-5 text-right">
                        <button class="btn btn-primary" id="create_account" onclick="CreateAccount()" ><i class="fas fa-plus"></i> {{__('Add New Bank Account')}} </button>
                    </div>
                    @if (count($bank_account) == 0)
                        <div class="col-12 text-center">
                            <h5 class="m-0">{{__('No Account Found')}}</h5>
                        </div>
                    @else
                        <div class="col-12 ">
                            <div class = "row mt-3 ml-2 mr-2">
                                @foreach ($bank_account as $item)
                                <div class="col-xl-4 col-md-6 mb-4">
                                    <div class="card h-100" >
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                        <div class="col mr-2">
                                            <div class="row mb-1 mr-1">
                                                <div class='col font-weight-bold text-gray-900'>{{__($item->iban)}}</div>
                                            </div>
                                            <div class="row mb-1 mr-1">
                                                <div class='col font-weight-bold text-gray-900'>{{__($item->swift)}}</div>
                                            </div>
                                            <div class="row mb-1 mr-1">
                                                <div class='col font-weight-bold text-gray-900'>{{__($item->currency->code)}}</div>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal modal-blur fade" id="modal-success-2" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-status bg-primary"></div>
        <div class="modal-body py-4">
            <div class="text-center">
                <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                <h3>@lang('Bank Account')</h3>
            </div>
            @if ($bank_gateway->keyword == 'clearjunction' || $bank_gateway->keyword == 'swan')
            <div class="text-center mt-3">
                <h5>@lang('Don\'t need System Bank Account in '){{ucwords($bank_gateway->keyword)}}</h5>
            </div>
            @else
                <form id="iban-submit" class="mt-4 mx-3" action="{{isset($bank_gateway) ? $bank_gateway->keyword == 'openpayd' ? route('admin.user.bank.nogateway') : route('admin.subinstitution.banks.account.railsbank.create') : route('admin.user.bank.nogateway')}}" method="POST"  enctype="multipart/form-data" >

                    {{ csrf_field() }}

                    <div class="form-group">
                        <label for="inp-name">{{ __('Currency') }}</label>
                        <select class="form-control" name="currency" id="currency" required>
                        <option value="">{{ __('Select Currency') }}</option>
                        @foreach ($currencylist as $value )
                            <option value="{{$value->id}}">{{ __($value->code) }}</option>
                        @endforeach
                        </select>
                    </div>
                    @if(!isset($bank_gateway) || $bank_gateway->keyword == 'openpayd')
                        <div class="form-group">
                            <label for="inp-name">{{ __('IBAN') }}</label>
                            <input type="text" class="form-control iban-input" name="iban" required />
                            <small class="text-danger iban-validation"></small>
                        </div>
                        <div class="form-group">
                            <label for="inp-name">{{ __('SWIFT') }}</label>
                            <input type="text" pattern="[^()/><\][\\;!|]+" class="form-control" name="swift" required />
                        </div>
                    @endif
                    <input type="hidden" name="subbank" value="{{$data->id}}">
                    <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Create') }}</button>
                </form>
            @endif
        </div>
    </div>
    </div>
  </div>

@endsection

@section('scripts')
<script type="text/javascript">
  'use strict';
  function CreateAccount() {
            $('#modal-success-2').modal('show')
        }
</script>
@endsection
