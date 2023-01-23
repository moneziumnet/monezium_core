@extends('layouts.user')

@push('css')
<style>
    .document {
        display:none;
        }

        </style>
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

                        <div class="form-group mt-3">
                            <label class="form-label required">{{__('Currency')}}</label>
                            <select name="currency" id="currency" class="form-select" required>
                                <option value="">{{ __('Select Currency') }}</option>
                            </select>
                        </div>

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Incoming Amount')}}</label>
                            <input name="amount" id="amount" class="form-control" autocomplete="off" placeholder="{{__('0.0')}}" type="number" step="any" value="{{ old('amount') }}" min="1" required>
                        </div>

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label document" id="document_label">{{__('Document')}}</label>
                            <input class= "document" name="document" id="document" class="form-control" autocomplete="off" type="file" accept=".xls,.xlsx,.pdf,.jpg,.png ">
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
            <li class="list-group-item d-flex justify-content-between">@lang('Bank IBAN')<span id="bank_iban"></span></li>
            <li class="list-group-item d-flex justify-content-between">@lang('Bank SWIFT')<span id="bank_swift"></span></li>
            <li class="list-group-item d-flex justify-content-between">@lang('Description')<span id="bank_description"></span></li>
        </ul>
        </div>
        <form id="depositbank_gateway" action="{{ route('user.depositbank.store') }}" method="post"  enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="form-group" id="otp_body">
                    <label class="form-label required">{{__('OTP Code')}}</label>
                    <input name="otp_code" id="otp_code" class="form-control" placeholder="{{__('OTP Code')}}" type="text" pattern="[^()/><\][;!|]+" value="{{ old('opt_code') }}" required>
                </div>
              <div class="form-group mt-3">
                <input type="hidden" name="currency_sign" value="$">
                <input type="hidden" id="currencyCode" name="currency_code" value="USD">
                <input type="hidden" name="method" id="modal_method" value="">
                <input type="hidden" name="amount" id="modal_amount" value="">
                <input type="hidden" name="currency_id" id="modal_currency" value="">
                <input type="hidden" name="details" id="modal_details" value="">
                <input type="hidden" name="bank" id="modal_bank" value="">
                <input type="hidden" name="deposit_no" id="deposit_no" />
                <input name="document" id="modal_document" type="file" style="display: none;" accept=".xls,.xlsx,.pdf,.jpg,.png">
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
    const characters ='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    function generateRandomString(length) {
        let result = ' ';
        const charactersLength = characters.length;
        for ( let i = 0; i < length; i++ ) {
            result += characters.charAt(Math.floor(Math.random() * charactersLength));
        }
        return result;
    }

    $('#withmethod').on('change', function() {
        var pos = $('#withmethod').val();

        var url = `${mainurl}/user/bank/deposit/bankcurrency/${JSON.parse(pos)['id']}`;
        $.get(url, function(res) {
            let _optionHtml = '<option value="">Select Currency</option>';
            $.each(res, function(i, item) {
                console.log(JSON.stringify(item))
                _optionHtml += '<option value=\'' + JSON.stringify(item) + '\'>' + item.currency.code + '</option>';
            });
            $('select#currency').html(_optionHtml);
        })
    })

    $('#submit').on('click', function() {
        var verify = "{{$user->paymentCheck('Bank Incoming')}}";

        var pos = $('#withmethod').val();
        $('#bank_name').text(JSON.parse(pos)['name']);
        $('#bank_address').text(JSON.parse(pos)['address']);
        $('#bank_iban').text(JSON.parse(($('#currency').val()))['iban']);
        $('#bank_swift').text(JSON.parse(($('#currency').val()))['swift']);
        $('#deposit_no').val(generateRandomString(12));
        $('#bank_description').text($('#details').val() + " / " + $('#deposit_no').val());
        $('#modal_method').val(JSON.parse(pos)['name']);
        $('#modal_bank').val(JSON.parse(pos)['id']);
        $('#modal_amount').val($('#amount').val());
        if ($('#amount').val() >= parseFloat('{{$other_bank_limit}}')) {
            $('#modal_document')[0].files = $('#document')[0].files;
        }
        $('#modal_currency').val(JSON.parse(($('#currency').val()))['currency_id']);
        $('#modal_details').val($('#details').val());
        if (verify) {
            var url = "{{url('user/sendotp')}}";
            $.get(url,function (res) {
                console.log(res)
                if(res=='success') {
                    $('#modal-success').modal('show');
                }
                else {
                    toastr.options = { "closeButton" : true, "progressBar" : true }
                    toastr.error('The OTP code can not be sent to you.');
                }
            });
        } else {
            $('#otp_body').remove();
            $('#modal-success').modal('show');
        }
        $('#modal-success').modal('show');
    });
    $('#amount').on('change', function() {

        if ($('#amount').val() >= parseFloat('{{$other_bank_limit}}')) {
            document.getElementById("document").style.display = "block";
            document.getElementById("document_label").style.display = "block";
        }
        else {
            document.getElementById("document").style.display = "none";
            document.getElementById("document_label").style.display = "none";
        }
    });

  </script>

@endpush
