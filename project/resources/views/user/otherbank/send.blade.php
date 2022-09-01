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
    @include('user.ex_payment_tab')
      <div class="row align-items-center mt-3">
        <div class="col">
          <div class="page-pretitle">
            {{__('Overview')}}
          </div>
          <h2 class="page-title">
            {{__('External Payments')}}
          </h2>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-md-5">
                <div class="card p-3 p-lg-4">
                    <table class="table table-transparent table-responsive">
                        <thead>

                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <p class="strong mb-1">{{__('Minimum Amount')}}</p>
                                </td>
                                <td class="text-end"> {{ showprice($data->bank->min_limit,$currency) }}</td>
                            </tr>

                            <tr>
                                <td>
                                    <p class="strong mb-1">{{__('Maximum Amount')}}</p>
                                </td>
                                <td class="text-end">{{ showprice($data->bank->max_limit,$currency) }}</td>
                            </tr>

                            <tr>
                                <td>
                                    <p class="strong mb-1">{{__('Daily Amount Limit')}}</p>
                                </td>
                                <td class="text-end">{{ showprice($data->bank->daily_maximum_limit,$currency) }}</td>
                            </tr>

                            <tr>
                                <td>
                                    <p class="strong mb-1">{{__('Daily Monthly Limit')}}</p>
                                </td>
                                <td class="text-end">{{ showprice($data->bank->monthly_maximum_limit,$currency) }}</td>
                            </tr>

                            <tr>
                                <td>
                                    <p class="strong mb-1">{{__('Daily Limit')}}</p>
                                </td>
                                <td class="text-end">{{ $data->bank->daily_total_transaction }}</td>
                            </tr>

                            <tr>
                                <td>
                                    <p class="strong mb-1">{{__('Monthly Limit')}}</p>
                                </td>
                                <td class="text-end">{{ $data->bank->monthly_total_transaction }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card p-3 p-lg-4">
                    @includeIf('includes.flash')
                    <form action="{{route('user.other.send.store')}}" method="POST" id="otherbank_form" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="other_bank_id" value="{{ $data->other_bank_id }}">
                        <input type="hidden" name="beneficiary_id" value="{{ $data->id }}">
                        <div class="form-group mb-3">
                            <label class="form-label required">{{__('Bank Name')}}</label>
                            <input name="bank_name" id="bank_name" class="form-control" autocomplete="off" placeholder="{{__('Wells Fargo')}}" type="text" value="{{ $data->bank->title }}" min="1" required readonly>
                        </div>

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Beneficiary Name')}}</label>
                            <input name="account_name" id="account_name" class="form-control" autocomplete="off" placeholder="{{__('Jhon Doe')}}" type="text" value="{{ $data->account_name }}" required readonly>
                        </div>

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Beneficiary Address')}}</label>
                            <input name="address" id="address" class="form-control" autocomplete="off" placeholder="{{__('Beneficiary Address')}}" type="text" value="{{ $data->address }}" min="1" required readonly>
                        </div>

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Bank Address')}}</label>
                            <input name="bank_address" id="bank_address" class="form-control" autocomplete="off" placeholder="{{__('Bank Address')}}" type="text" value="{{ $data->bank_address }}" min="1" required readonly>
                        </div>

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('SWIFT/BIC')}}</label>
                            <input name="swift_bic" id="swift_bic" class="form-control" autocomplete="off" placeholder="{{__('SWIFT/BIC')}}" type="text" value="{{ $data->swift_bic }}" min="1" required readonly>
                        </div>

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Account/IBAN')}}</label>
                            <input name="account_iban" id="account_iban" class="form-control" autocomplete="off" placeholder="{{__('Jhon Doe')}}" type="text" value="{{ $data->account_iban }}" min="1" required readonly>
                        </div>

                        <div class="form-group mt-3">
                            <label class="form-label required">{{__('Your Bank Account')}}</label>
                            <select name="subbank" id="withmethod" class="form-select" required>
                                <option value="">{{ __('Select Bank') }}</option>
                                @foreach ($banks as $key => $bank)
                                        <option value="{{$bank->id}}">{{$bank->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mt-3">
                            <label class="form-label required">{{__('Currency')}}</label>
                            <select name="currency_id" id="currency" class="form-select" required>
                                <option value="">{{ __('Select Currency') }}</option>
                            </select>
                        </div>

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Amount')}}</label>
                            <input name="amount" id="amount" class="form-control" autocomplete="off" placeholder="{{__('0.0')}}" type="number" step="any" value="{{ old('amount') }}" min="1" required>
                        </div>

                        <div class="form-group mt-3">
                            <label class="form-label required">{{__('Payment Type')}}</label>
                            <select name="payment_type" id="payment_type" class="form-select" required>
                                <option value="">{{ __('Select Payment Type') }}</option>
                                <option value="SWIFT">{{__('Swift')}}</option>
                                <option value="SEPA">{{__('SEPA')}}</option>
                                <option value="SEPA_INSTANT">{{__('SEPA_INSTANT')}}</option>
                            </select>
                        </div>

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label document" id="document_label">{{__('Document')}}</label>
                            <input class= "document" name="document" id="document" class="form-control" autocomplete="off" type="file" accept=".xls,.xlsx,.pdf,.jpg,.png">
                        </div>

                        <div class="form-group mt-3">
                            <label class="form-label required">{{__('Description')}}</label>
                            <textarea name="des" id="des" class="form-control" autocomplete="off" placeholder="{{__('Please input description')}}" type="text" required></textarea>
                        </div>
                        <input name="otp" id="otp" type="hidden" value="">

                        <div class="form-footer">
                            <button type="submit" id="form_submit" class="btn btn-primary w-100">{{__('Submit')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-blur fade" id="modal-verify" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{('OTP')}}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
            <div class="modal-body">
              <div class="form-group">
                <label class="form-label required">{{__('OTP Code')}}</label>
                <input name="otp_code" id="otp_code" class="form-control" placeholder="{{__('OTP Code')}}" type="text" step="any" value="{{ old('opt_code') }}" required>
              </div>

            </div>

            <div class="modal-footer">
                <button  id="submit-btn" class="btn btn-primary">{{ __('Verify') }}</button>
            </div>
      </div>
    </div>
  </div>

@endsection

@push('js')
<script type="text/javascript">
    'use strict';
    $('#amount').on('change', function() {

        if ($('#amount').val() >= '{{$other_bank_limit}}') {
            document.getElementById("document").style.display = "block";
            document.getElementById("document_label").style.display = "block";
        }
        else {
            document.getElementById("document").style.display = "none";
            document.getElementById("document_label").style.display = "none";
        }
    })

    $('#withmethod').on('change', function() {
        var pos = $('#withmethod').val();

        var url = `${mainurl}/user/bank/deposit/bankcurrency/${pos}`;
        $.get(url, function(res) {
            let _optionHtml = '<option value="">Select Currency</option>';
            $.each(res, function(i, item) {
                console.log(JSON.stringify(item))
                _optionHtml += '<option value=\'' + item.currency.id + '\'>' + item.currency.code + '</option>';
            });
            $('select#currency').html(_optionHtml);
        })
    })

    $(document).ready(function(){
        $('#form_submit').on('click', function(event){
            var verify = "{{$user->payment_fa_yn}}";
            event.preventDefault();
            if (verify == 'Y') {
                var url = "{{url('user/sendotp')}}";
                $.get(url,function (res) {
                    console.log(res)
                    if(res=='success') {
                        $('#modal-verify').modal('show');
                    }
                    else {
                        alert('The OTP code can not be sent to you.')
                    }
                });
            } else {
                $("#otherbank_form").submit();
            }
        })
        $('#submit-btn').on('click', function(){
            if($('#otp_code').val()) {

                $('#otp').val($('#otp_code').val());
                $("#otherbank_form").submit();
            }
        })
    });
</script>

@endpush

