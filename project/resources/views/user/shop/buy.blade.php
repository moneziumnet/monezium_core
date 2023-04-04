@extends('layouts.user')

@push('css')

@endpush



@section('contents')
<div class="container-fluid">
  <div class="page-header d-print-none">
    <div class="row align-items-center">
      <div class="col">
        <div class="page-pretitle">
          {{__('Overview')}}
        </div>
        <h2 class="page-title">
          {{__('Shopping')}}
        </h2>
      </div>
      <div class="col-auto ms-auto d-print-none">

            <a class="btn btn-primary d-sm-inline-block" href="{{route('user.shop.index')}}">
              <i class="fas fa-backward me-1"></i> {{__('Back')}}
            </a>
      </div>
    </div>
  </div>
</div>


<div class="page-body">
    <div class="container-fluid">
        <div class="card card-lg">
          <div class="card-body">
            <div class="row">
              <div class="col-md-3">
                <div class="card">
                    <img class="back-preview-image"
                        @php
                            $image=DB::table('product_images')->whereProductId($data->id)->first();
                        @endphp
                        src="{{asset('assets/images')}}/{{$image->image}}"
                    alt="Image placeholder">
                    <!-- Card body -->
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-12">
                            <h5 class="h4 mb-2 font-weight-bolder">{{__('Product Name: ')}}{{$data->name}}</h5>
                            <h5 class="mb-1">{{__('Shop: ')}} {{$data->shop->name}}</h5>
                            <h5 class="mb-1">{{__('Category: ')}} {{$data->category->name}}</h5>
                            <h5 class="mb-1">{{__('Amount: ')}} {{$data->currency->symbol}}{{$data->amount}}</h5>
                            <h5 class="mb-1">{{__('Sold: ')}} {{$data->sold ?? '0'}}{{__('/')}}{{$data->sold+$data->quantity}}</h5>
                            <h5 class="mb-3">{{__('Currenct Stock:')}} {{$data->quantity}}</h5>
                            </div>
                        </div>
                    </div>
                </div>
              </div>
              <div class="col-md-3">
              </div>
              <div class="col-md-5 text-end ">
                <form action="{{route('user.merchant.product.pay')}}" id="form_submit" method="post" enctype="multipart/form-data">
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
                        <label class="form-label required">{{__('Bank Account')}}</label>
                        <select name="bank_account" id="bank_account" class="form-control">
                            @if(count($bankaccounts) != 0)
                            <option value="">{{__('Select')}}</option>
                              @foreach($bankaccounts as $account)
                                  <option value="{{$account->id}}" data-data="{{$account}}" data-bank="{{$account->subbank}}" data-user="{{$account->user->company_name ?? $account->user->name}}">{{$account->subbank->name}}</option>

                              @endforeach
                            @else
                            <option value="">{{__('There is no bank account for this currency.')}}</option>

                            @endif
                          </select>
                    </div>
                    <input type="hidden" name="deposit_no" id="deposit_no" />
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
                        <div class="form-group ms-5 mt-2 text-start" >
                            <label class="form-label required">{{__('Description')}}</label>
                            <input name="description" id="description" class="form-control shadow-none col-md-4"  type="text">
                        </div >
                    </div>

                    <div class="form-group ms-5 mt-5 text-start" >
                            <label class="form-label">{{__('Quantity')}}</label>
                            <input name="quantity" id="quantity" class="form-control shadow-none col-md-4"  type="number" min="1" max="{{$data->quantity}}" required>
                    </div >
                    <input type="hidden" name="product_id" value="{{$data->id}}">

                    <div class="mt-4" id="default_pay" style="display: block;">
                        <button type="submit" class="btn btn-primary btn-block" id="btn-pay">{{__('Pay')}} <i class="ms-2 fas fa-long-arrow-alt-right"></i></button>
                    </div>
                    <div class="ms-5 mt-4" id="crypto_pay" style="display: none;">
                        @foreach($cryptolist as $currency)
                        <div>
                            <button name="link_pay_submit" value="{{$currency->id}}" class="crypto-submit btn btn-primary w-100 btn-block mb-2"> {{__('Pay with ')}}{{$currency->curr_name}} - {{$currency->code}}</button>
                        </div>
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
              <li class="list-group-item">@lang('Quantity')<span id="detail_quantity"></span></li>
              <li class="list-group-item">@lang('Total Price')<span id="detail_total_price"></span></li>
              <li class="list-group-item">@lang('Description')<span id="detail_bank_details"></span></li>
          </ul>
          <button class="btn btn-primary w-100 mt-3" id="payment_submit">Submit</button>
          </div>
      </div>
    </div>
</div>

@endsection

@push('js')
<script type="text/javascript">
"use strict";

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
        $('#form_submit').attr('action', "{{route('user.merchant.product.pay')}}");
        $('#form_submit').attr('method', "POST");
        document.getElementById("crypto_pay").style.display = "none";
        document.getElementById("default_pay").style.display = "block";
        $("#bank_account").prop('required',true);
        document.getElementById("bank_part").style.display = "block";
    }
    else if ($(this).attr('id') == 'crypto') {
        $('#form_submit').attr('action',"{{route('user.merchant.product.crypto.pay', $data->id)}}");
        $('#form_submit').attr('method', "GET");
        document.getElementById("crypto_pay").style.display = "block";
        document.getElementById("default_pay").style.display = "none";
        $("#bank_account").prop('required',false);
        $("#description").prop('required',false);
        document.getElementById('bank_account_part').style.display = "none";
        document.getElementById("bank_part").style.display = "none";
    }
    else {
        $('#form_submit').attr('action', "{{route('user.merchant.product.pay')}}");
        $('#form_submit').attr('method', "POST");
        document.getElementById("crypto_pay").style.display = "none";
        document.getElementById("default_pay").style.display = "block";
        $("#bank_account").prop('required',false);
        $("#description").prop('required',false);
        document.getElementById('bank_account_part').style.display = "none";
        document.getElementById("bank_part").style.display = "none";
    }
})
$(document).ready(function() {
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
        $("#description").prop('required',true);
            document.getElementById('bank_account_part').style.display = "block";
        } else{
        $("#description").prop('required',false);
            document.getElementById('bank_account_part').style.display = "none";
        }
    })
    $('#btn-pay').on('click', function(e) {
        var payment_type = $('input[name=payment]:checked', '#form_submit').val();
        if(payment_type == "bank_pay" && document.getElementById('form_submit').checkValidity()) {
            e.preventDefault();
            $('#modal-details').modal('show');
            $('#detail_user_name').html($('#receiver_name').val());
            $('#detail_bank_name').html($('#bank_name').val());
            $('#detail_bank_address').html($('#bank_address').val());
            $('#detail_bank_iban').html($('#bank_iban').val());
            $('#detail_bank_swift').html($('#bank_swift').val());
            $('#detail_quantity').html($('#quantity').val());
            $('#detail_total_price').html("{{$data->currency->symbol}}" + $('#quantity').val() * "{{$data->amount}}");
            $('#deposit_no').val(generateRandomString(12))
            $('#detail_bank_details').html($('#description').val() + " / " + $('#deposit_no').val());
        }
    });
    $('#payment_submit').on('click', function () {
        if(document.getElementById('form_submit').checkValidity()) {
            $('#form_submit').submit();
        }
    });
    $('.crypto-submit').on('click', function() {
        if(document.getElementById('form_submit').checkValidity()) {
            $('#form_submit').submit();
        }
    });
})
</script>
@endpush


