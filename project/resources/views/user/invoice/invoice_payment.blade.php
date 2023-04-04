@extends('layouts.user')

@section('title')
   @lang('Invoice Payment')
@endsection

@section('contents')
<div class="container-fluid">
    <div class="page-header d-print-none">
      <div class="row align-items-center mt-3">
        <div class="col">
          <div class="page-pretitle">
            {{__('Overview')}}
          </div>
          <h2 class="page-title">
            {{__('Invoice Payment')}}
          </h2>
        </div>
      </div>
    </div>
</div>
<div class="container-fluid mt-3 mb-3">
    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="d-flex align-items-end">
                                <h2 class="me-3">{{$invoice->type}}</h2>
                                <h4>{{$invoice->number}}</h4>
                            </div>
                        </div>
                        <div class="col-md-5">
                          <ul class="list-group">
                              <li class="list-group-item d-flex justify-content-between">{{__('Name :')}} <span class="font-weight-bold">{{$invoice->invoice_to}}</span> </li>
                              <li class="list-group-item d-flex justify-content-between">{{__('Email :')}} <span class="font-weight-bold">{{$invoice->email}}</span> </li>
                              <li class="list-group-item d-flex justify-content-between">{{__('Amount :')}} <span class="font-weight-bold">{{amount($invoice->final_amount,$invoice->currency->type,2).' '.$invoice->currency->code}}</span> </li>
                              <li class="list-group-item d-flex justify-content-between">{{__('Tax :')}} <span class="font-weight-bold">{{amount($tax_value,$invoice->currency->type,2).' '.$invoice->currency->code}}</span> </li>
                          </ul>
                        </div>
                        <div class="offset-md-2 col-md-5 text-end">
                          <form action="{{route('user.invoice.payment.submit')}}" id="pay_form_submit"  method="post" enctype="multipart/form-data" name="pay_form_submit">
                              @csrf
                              <div class="form-selectgroup row">
                                  <label class="form-selectgroup-item">
                                      <input type="radio" name="payment" value="bank_pay" id="bank_pay" class="form-selectgroup-input select_method" >
                                      <span class="form-selectgroup-label">
                                          <i class="fas fa-credit-card me-2"></i>
                                          @lang('Pay with Bank')</span>
                                  </label>
                                  <label class="form-selectgroup-item">
                                      <input type="radio" name="payment" value="gateway" id="gateway" class="form-selectgroup-input select_method" >
                                      <span class="form-selectgroup-label">
                                          <i class="fas fa-euro-sign me-2"></i>
                                          @lang('Pay with gateways')</span>
                                  </label>
                                  <label class="form-selectgroup-item">
                                      <input type="radio" name="payment" value="crypto" id="crypto" class="form-selectgroup-input select_method" >
                                      <span class="form-selectgroup-label">
                                          <i class="fas fa-coins me-2"></i>
                                          @lang('Pay with Crypto')</span>
                                  </label>
                                  <label class="form-selectgroup-item">
                                  <input type="radio" name="payment" value="wallet" id="wallet" class="form-selectgroup-input select_method" checked>
                                  <span class="form-selectgroup-label">
                                      <i class="fas fa-wallet me-2"></i>
                                      @lang('Pay with customer wallet')
                                      </span>
                                  </label>

                              </div>
                              <div class="form-group ms-5 mt-5 text-start" id="bank_part" style="display: none">
                                  <label class="form-label">{{__('Bank Account')}}</label>
                                  <select name="bank_account" id="bank_account" class="form-control">
                                    @if(count($bankaccounts) != 0)
                                        <option value="">{{__('Select')}}</option>
                                        @foreach($bankaccounts as $account)
                                            <option value="{{$account->id}}" data-data="{{$account}}" data-bank="{{$account->subbank}}" data-user="{{$account->user->company_name ?? $account->user->name}}">
                                              {{$account->subbank->name}}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="">{{__('There is no bank account for this currency.')}}</option>
                                    @endif
                                  </select>
                              </div>
                              <div id="bank_account_part" style="display: none;">
                                  <div class="form-group ms-5 mt-2 text-start" >
                                      <label class="form-label">{{__('Receiver Name')}}</label>
                                      <input name="receiver_name" id="receiver_name" class="form-control shadow-none col-md-4"  type="text" readonly>
                                  </div >
                                  <div class="form-group ms-5 mt-2 text-start" >
                                      <label class="form-label">{{__('Bank Name')}}</label>
                                      <input name="bank_name" id="bank_name" class="form-control shadow-none col-md-4"  type="text" readonly>
                                  </div >
                                  <div class="form-group ms-5 mt-2 text-start" >
                                      <label class="form-label">{{__('Bank Address')}}</label>
                                      <input name="bank_address" id="bank_address" class="form-control shadow-none col-md-4"  type="text" readonly>
                                  </div >
                                  <div class="form-group ms-5 mt-2 text-start" >
                                      <label class="form-label">{{__('Bank IBAN')}}</label>
                                      <input name="bank_iban" id="bank_iban" class="form-control shadow-none col-md-4"  type="text" readonly>
                                  </div >
                                  <div class="form-group ms-5 mt-2 text-start" >
                                      <label class="form-label">{{__('Bank SWIFT')}}</label>
                                      <input name="bank_swift" id="bank_swift" class="form-control shadow-none col-md-4"  type="text" readonly>
                                  </div >
                              </div>

                              <input type="hidden" name="invoice_id" value="{{$invoice->id}}">
                              <input type="hidden" name="deposit_no" id="deposit_no" />

                              <div class="mt-4" id="default_pay" style="display: block;">
                                  <button type="submit" class="btn btn-primary btn-block" id="btn-pay">{{__('Pay')}} <i class="ms-2 fas fa-long-arrow-alt-right"></i></button>
                              </div>
                              <div class="mt-4 ms-5" id="crypto_pay" style="display: none;">
                                  @foreach($cryptolist as $currency)
                                      <button name="link_pay_submit" value="{{$currency->id}}" class="col btn btn-primary w-100 mb-2 crypto-submit"> {{__('Pay with ')}}{{$currency->curr_name}} - {{$currency->code}}</button>
                                  @endforeach
                              </div>
                          </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal modal-blur fade" id="modal-details" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-status bg-primary"></div>
            <div class="modal-body text-center py-4">
            <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
            <h3>@lang('Payment Details')</h3>
            <p class="bank_details"></p>
            <ul class="list-group details-list mt-2">
                <li class="list-group-item">@lang('Receiver Name')<span id="detail_user_name"></span></li>
                <li class="list-group-item">@lang('Bank Name')<span id="detail_bank_name"></span></li>
                <li class="list-group-item">@lang('Bank Address')<span id="detail_bank_address"></span></li>
                <li class="list-group-item">@lang('Bank IBAN')<span id="detail_bank_iban"></span></li>
                <li class="list-group-item">@lang('Bank SWIFT')<span id="detail_bank_swift"></span></li>
                <li class="list-group-item">@lang('Total Price')<span id="detail_total_price"></span></li>
                <li class="list-group-item">@lang('Deposit No')<span id="detail_deposit_no"></span></li>
            </ul>
            <span class="btn btn-primary w-100 mt-3" id="payment_submit">Submit</span>
            </div>
        </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
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
    $('.select_method').on('click', function() {
        if ($(this).attr('id') == 'bank_pay') {
            $('#pay_form_submit').attr('action', "{{route('user.invoice.payment.submit')}}");
            $('#pay_form_submit').attr('method', "POST");
            document.getElementById("crypto_pay").style.display = "none";
            document.getElementById("default_pay").style.display = "block";
            $("#bank_account").prop('required',true);
            document.getElementById("bank_part").style.display = "block";
        }
        else if ($(this).attr('id') == 'crypto') {
            $('#pay_form_submit').attr('action',"{{route('user.invoice.payment.crypto', $invoice->id)}}");
            $('#pay_form_submit').attr('method', "GET");
            document.getElementById("crypto_pay").style.display = "block";
            document.getElementById("default_pay").style.display = "none";
            $("#bank_account").prop('required',false);
            $("#description").prop('required',false);
            document.getElementById('bank_account_part').style.display = "none";
            document.getElementById("bank_part").style.display = "none";
        }
        else {
            $('#pay_form_submit').attr('action', "{{route('user.invoice.payment.submit')}}");
            $('#pay_form_submit').attr('method', "POST");
            document.getElementById("crypto_pay").style.display = "none";
            document.getElementById("default_pay").style.display = "block";
            $("#bank_account").prop('required',false);
            $("#description").prop('required',false);
            document.getElementById('bank_account_part').style.display = "none";
            document.getElementById("bank_part").style.display = "none";
        }
    })
    $('#bank_account').on('change', function() {
        var selected = $('#bank_account option:selected').data('data');
        var bank = $('#bank_account option:selected').data('bank');
        var user = $('#bank_account option:selected').data('user');
        if(selected){
            $('#receiver_name').val(user);
            $('#bank_name').val(bank.name);
            $('#bank_address').val(bank.address);
            $('#bank_iban').val(selected.iban);
            $('#bank_swift').val(selected.swift);
            document.getElementById('bank_account_part').style.display = "block";
        } else{
            $("#description").prop('required',false);
            document.getElementById('bank_account_part').style.display = "none";
        }
    })

    $('#btn-pay').on('click', function(e) {
        var payment_type = $('input[name=payment]:checked', '#pay_form_submit').val();
        if(payment_type == "bank_pay" && document.getElementById('pay_form_submit').checkValidity()) {
            e.preventDefault();
            $('#modal-details').modal('show');
            $('#detail_user_name').html($('#receiver_name').val());
            $('#detail_bank_name').html($('#bank_name').val());
            $('#detail_bank_address').html($('#bank_address').val());
            $('#detail_bank_iban').html($('#bank_iban').val());
            $('#detail_bank_swift').html($('#bank_swift').val());
            $('#deposit_no').val(generateRandomString(12));
            $('#detail_deposit_no').text($('#deposit_no').val());
            $('#detail_total_price').html("{{$invoice->currency->symbol}}" + "{{$invoice->final_amount + $tax_value}}");
        }
    });
    $('#payment_submit').on('click', function () {
        if(document.getElementById('pay_form_submit').checkValidity()){
            $('#pay_form_submit').submit();
        }
    });
    $('.crypto-submit').on('click', function() {
        if(document.getElementById('pay_form_submit').checkValidity()){
            $('#pay_form_submit').submit();
        }
    });
</script>

@endpush
