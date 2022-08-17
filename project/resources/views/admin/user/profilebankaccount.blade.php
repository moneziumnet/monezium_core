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
    <div class="card  tab-card">
      @include('admin.user.profiletab')
      <div class="tab-content" id="myTabContent">
          @php
        $currency = defaultCurr();
        @endphp
        @include('includes.admin.form-success')
        <div class="tab-pane fade show active" id="modules" role="tabpanel" aria-labelledby="modules-tab">

            <div class="card-body mt-3">
                <div class="card">
                    <div class="row mb-3">
                        <div class=" ml-5 text-right mt-3">
                            <button class="btn btn-primary" id="create_account" onclick="CreateAccount()" ><i class="fas fa-plus"></i> {{__('Add New Bank Account')}} </button>
                        </div>
                        <div class="col-12 ">
                            <div class = "row mt-3 ml-2 mr-2">
                                @if (count($bankaccount) != 0)

                                    @foreach ($bankaccount as $value )
                                    <div class="col-xl-3 col-md-6 mb-4">
                                        <div class="card h-100" >
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                            <div class="col mr-2">
                                                <div class="row mb-1 mr-1">
                                                    <div class='col font-weight-bold text-gray-900'>{{__($value->iban)}}</div>
                                                    <div class='col font-weight-bold text-gray-900'>{{__($value->swift)}}</div>
                                                </div>
                                                <div class="row mb-1 mr-1">
                                                    <div class="col font-weight-bold text-gray-800"> {{__($value->subbank->name)}} </div>
                                                    <div class='col font-weight-bold text-gray-900'>{{__($value->currency->code)}}</div>
                                                </div>
                                            </div>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <h4 class="ml-3 text-center py-5">{{__('No Bank Account')}}</h3>
                                @endif


                            </div>
                        </div>
                    </div>

                </div>
             </div>
        </div>
     </div>
    </div>
  </div>
</div>

        {{-- Account MODAL --}}
        <div class="modal modal-blur fade" id="modal-success-2" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-status bg-primary"></div>
                <div class="modal-body text-center py-4">
                <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                <h3>@lang('Bank Account')</h3>
                <form class="bankaccount  mt-4 mr-3" action="" method="POST" >

                    {{ csrf_field() }}

                        <div class="form-group">
                        <label for="inp-name">{{ __('SubInstitions Bank') }}</label>
                            <select class="form-control" name="subbank" id="subbank" required>
                            <option value="">{{ __('Select SubInstitions Bank') }}</option>
                            @foreach ($subbank as $bank )
                                <option value="{{$bank->id}}">{{ __($bank->name) }}</option>
                            @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="inp-name">{{ __('Currency') }}</label>
                                <select class="form-control" name="currency" id="currency" required>
                                <option value="">{{ __('Select Currency') }}</option>
                                @foreach ($currencylist as $value )
                                    <option value="{{$value->id}}">{{ __($value->code) }}</option>
                                @endforeach
                                </select>
                        </div>
                        <input type="hidden" name="user" value="{{$data->id}}">
                        <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Create') }}</button>
                    </form>
                </div>
            </div>
            </div>
          </div>
        {{-- Account MODAL ENDS --}}


      @endsection


      @section('scripts')

      <script type="text/javascript">
          "use strict";
        $('#subbank').on('change', function() {
            $.post("{{ route('admin-user-bank-gateway') }}",{id:$('#subbank').val(),_token:'{{csrf_token()}}'},function (res) {
            console.log(res);
                if(res.keyword == 'railsbank')
                    {
                        $('.bankaccount').prop('action','{{ route('admin.user.bank.railsbank') }}');
                    }

                if(res.keyword == 'openpayd')
                    {
                        $('.bankaccount').prop('action','{{ route('admin.user.bank.openpayd') }}');
                    }
             });
        })
        function CreateAccount() {
            $('#modal-success-2').modal('show')
        }


      </script>

      @endsection
