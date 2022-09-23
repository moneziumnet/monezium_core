@extends('layouts.user')

@push('css')

@endpush



@section('contents')
<div class="container-xl">
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
    <div class="container-xl">
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
                <form action="{{route('user.merchant.product.pay')}}" method="post" enctype="multipart/form-data">
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
                                <i class="fas fa-dollar-sign me-2"></i>
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
                                  <option value="{{$account->id}}" data-data="{{$account}}" data-bank="{{$account->subbank}}" data-user="{{$account->user->name}}">{{$account->subbank->name}}</option>

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
                        <div class="form-group ms-5 mt-2 text-start" >
                            <label class="form-label required">{{__('Description')}}</label>
                            <input name="description" id="description" class="form-control shadow-none col-md-4"  type="text" required>
                        </div >
                    </div>

                    <div class="form-group ms-5 mt-5 text-start" >
                            <label class="form-label">{{__('Quantity')}}</label>
                            <input name="quantity" id="quantity" class="form-control shadow-none col-md-4"  type="number" min="1" max="{{$data->quantity}}" required>
                    </div >
                    <input type="hidden" name="product_id" value="{{$data->id}}">

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary btn-block">{{__('Pay')}} <i class="ms-2 fas fa-long-arrow-alt-right"></i></button>
                    </div>
                </form>
              </div>
            </div>

          </div>
        </div>
    </div>
</div>

@endsection
@push('js')
<script type="text/javascript">
"use strict";
$('.select_method').on('click', function() {
    if ($(this).attr('id') == 'bank_pay') {
        $("#bank_account").prop('required',true);
        document.getElementById("bank_part").style.display = "block";
    }
    else {
        $("#bank_account").prop('required',false);
        $("#description").prop('required',false);
        document.getElementById('bank_account_part').style.display = "none";
        document.getElementById("bank_part").style.display = "none";
    }
    if ($(this).attr('id') == 'crypto') {
        window.location.href = "{{route('user.merchant.product.crypto', $data->id)}}"
    }
})
$('#bank_account').on('change', function() {
    console.log('test');
    var selected = $('#bank_account option:selected').data('data');
    var bank = $('#bank_account option:selected').data('bank');
    var user = $('#bank_account option:selected').data('user');
    if(selected){
       $('#receiver_name').val(user);
       $('#bank_name').val(bank.name);
       $('#bank_address').val(bank.address);
       $('#bank_iban').val(selected.iban);
       $('#bank_swift').val(selected.swift);
       $('#bank_swift').val(selected.swift);
       $("#description").prop('required',true);
        document.getElementById('bank_account_part').style.display = "block";
    } else{
       $("#description").prop('required',false);
        document.getElementById('bank_account_part').style.display = "none";
    }
})
</script>
@endpush


