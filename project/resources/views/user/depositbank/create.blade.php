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
            <li class="list-group-item d-flex justify-content-between">@lang('Bank Iban')<span id="bank_iban"></span></li>
            <li class="list-group-item d-flex justify-content-between">@lang('Bank Swift')<span id="bank_swift"></span></li>
            <li class="list-group-item d-flex justify-content-between">@lang('Description')<span id="bank_description"></span></li>
        </ul>
        </div>
        <form id="depositbank_gateway" action="{{ route('user.depositbank.store') }}" method="post"  enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="form-group" id="otp_body">
                    <label class="form-label required">{{__('OTP Code')}}</label>
                    <input name="otp_code" id="otp_code" class="form-control" placeholder="{{__('OTP Code')}}" type="text" step="any" value="{{ old('opt_code') }}" required>
                </div>
              <div class="form-group mt-3">
                <input type="hidden" name="currency_sign" value="$">
                <input type="hidden" id="currencyCode" name="currency_code" value="USD">
                <input type="hidden" name="method" id="modal_method" value="">
                <input type="hidden" name="amount" id="modal_amount" value="">
                <input type="hidden" name="currency_id" id="modal_currency" value="">
                <input type="hidden" name="details" id="modal_details" value="">
                <input type="hidden" name="bank" id="modal_bank" value="">
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
        var verify = "{{$user->payment_fa_yn}}";

        var pos = $('#withmethod').val();
        $('#bank_name').text(JSON.parse(pos)['name']);
        $('#bank_address').text(JSON.parse(pos)['address']);
        $('#bank_iban').text(JSON.parse(($('#currency').val()))['iban']);
        $('#bank_swift').text(JSON.parse(($('#currency').val()))['swift']);
        $('#bank_description').text($('#details').val());
        $('#modal_method').val(JSON.parse(pos)['name']);
        $('#modal_bank').val(JSON.parse(pos)['id']);
        $('#modal_amount').val($('#amount').val());
        if ($('#amount').val() >= '{{$other_bank_limit}}') {
            $('#modal_document')[0].files = $('#document')[0].files;
        }
        $('#modal_currency').val(JSON.parse(($('#currency').val()))['currency_id']);
        $('#modal_details').val($('#details').val());
        // $.post("{{ route('user.depositbank.gateway') }}",{id:JSON.parse(pos)['id'],_token:'{{csrf_token()}}'},function (res) {
        //     if(res.keyword == 'railsbank')
        //         {
        //             $('#depositbank_gateway').prop('action','{{ route('user.depositbank.railsbank') }}');
        //         }
        //     if(res.keyword == 'openpayd')
        //         {
        //             $('#depositbank_gateway').prop('action','{{ route('user.depositbank.openpayd') }}');
        //         }

        //      });
             if (verify == 'Y') {
                var url = "{{url('user/sendotp')}}";
                $.get(url,function (res) {
                    console.log(res)
                    if(res=='success') {
                        $('#modal-success').modal('show');
                    }
                    else {
                        alert('The OTP code can not be sent to you.')
                    }
                });
            } else {
                $('#otp_body').remove();
                $('#modal-success').modal('show');
            }
             $('#modal-success').modal('show');
        });
        $('#amount').on('change', function() {

            if ($('#amount').val() >= '{{$other_bank_limit}}') {
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
