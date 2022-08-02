@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
        @include('user.deposittab')
      <div class="row align-items-center mt-3">
        <div class="col">
          <h2 class="page-title">
            {{__('Incoming (Bank)')}}
          </h2>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card p-3 p-sm-4 p-lg-5">
                    @includeIf('includes.flash')
                        <div class="form-group mt-3">
                            <label class="form-label required">{{__('Bank')}}</label>
                            <select name="method" id="withmethod" class="form-select" required>
                                <option value="">{{ __('Select Bank') }}</option>
                                @foreach ($banks as $key => $bank)
                                        <option value="{{$bank}}">{{$bank->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Incoming Amount (USD)')}}</label>
                            <input name="amount" id="amount" class="form-control" autocomplete="off" placeholder="{{__('0.0')}}" type="number" step="any" value="{{ old('amount') }}" min="1" required>
                        </div>

                        <div class="form-group mb-3 ">
                            <label class="form-label">{{__('Description')}}</label>
                            <textarea name="details" id="details" class="form-control nic-edit" cols="30" rows="5" placeholder="{{__('Receive account details')}}"></textarea>
                        </div>

                        <div class="form-footer">
                            <button id="submit" class="btn btn-primary w-100">{{__('Submit')}}</button>
                        </div>


                </div>
            </div>
        </div>
    </div>
</div>

  <div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="modal-status bg-primary"></div>
        <div class="modal-body text-center py-4">
        <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
        <h3>@lang('Bank Details')</h3>
        <ul class="list-group mt-2">
            <li class="list-group-item d-flex justify-content-between">@lang('Bank Name')<span id="bank_name"></span></li>
            <li class="list-group-item d-flex justify-content-between">@lang('Bank Address')<span id="bank_address"></span></li>
            <li class="list-group-item d-flex justify-content-between">@lang('Bank Iban')<span id="bank_iban"></span></li>
            <li class="list-group-item d-flex justify-content-between">@lang('Bank Swift')<span id="bank_swift"></span></li>
        </ul>
        </div>
        <form action="{{ route('user.depositbank.store') }}" method="post">
            @csrf
            <div class="modal-body">
              <div class="form-group mt-3">
                <input type="hidden" name="currency_sign" value="$">
                <input type="hidden" id="currencyCode" name="currency_code" value="USD">
                <input type="hidden" name="currency_id" value="1">
                <input type="hidden" name="method" id="modal_method" value="">
                <input type="hidden" name="amount" id="modal_amount" value="">
                <input type="hidden" name="details" id="modal_details" value="">
                <input type="hidden" name="bank" id="modal_bank" value="">
              </div>
            </div>

            <div class="modal-footer">
                <button type="submit" id="submit-btn" class="btn btn-primary">{{ __('Ok') }}</button>
            </div>
        </form>
    </div>
    </div>
</div>


@endsection

@push('js')

  <script type="text/javascript">
  'use strict';
    $('#submit').on('click', function() {
        var pos = $('#withmethod').val();
        console.log(JSON.parse(pos));
        $('#bank_name').text(JSON.parse(pos)['name']);
        $('#bank_address').text(JSON.parse(pos)['address']);
        $('#bank_iban').text(JSON.parse(pos)['iban']);
        $('#bank_swift').text(JSON.parse(pos)['swift']);
        $('#modal_method').val(JSON.parse(pos)['name']);
        $('#modal_bank').val(JSON.parse(pos)['id']);
        $('#modal_amount').val($('#amount').val());
        $('#modal_details').val($('#details').val());
        $('#modal-success').modal('show');

    })
  </script>

@endpush
