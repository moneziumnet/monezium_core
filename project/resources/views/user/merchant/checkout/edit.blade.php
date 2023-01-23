@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      @include('user.merchant.tab')
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Edit Checkout')}}
          </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">

              <a href="{{ route('user.merchant.checkout.index') }}" class="btn btn-primary d-sm-inline-block">
                  <i class="fas fa-backward me-1"></i> {{__('Merchant Checkout List')}}
              </a>
            </div>
          </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card p-5">
                    <form action="{{route('user.merchant.checkout.update', $data->id)}}" method="POST" enctype="multipart/form-data">
                        {{csrf_field()}}
                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Title')}}</label>
                            <input name="name" id="name" class="form-control" placeholder="{{__('Title')}}" type="text" pattern="[^()/><\][;!|]+" value="{{$data->name}}" required>
                        </div>

                        <div class="form-group mb-3">
                          <label class="form-label">{{__('Description')}}</label>
                          <input name="description" id="description" class="form-control" placeholder="{{__('Description')}}" value="{{$data->description}}" type="text" >
                        </div>

                        <div class="form-group mb-3">
                          <label class="form-label required">{{__('Select Shop')}}</label>
                          <select name="shop_id" id="shop_id" class="form-control" required>
                            <option value="">Select</option>
                            @foreach($shops as $shop)
                            <option value="{{$shop->id}}" {{$data->shop_id == $shop->id ? 'selected' : ''}}>{{$shop->name}}</option>
                            @endforeach
                          </select>
                        </div>


                          <div class="form-group mb-3 mt-3">
                              <label class="form-label required">{{__('Select Currency')}}</label>
                              <select name="currency_id" id="currency_id" class="form-control" required>
                                  <option value="">Select</option>
                                  @foreach($currencylist as $currency)
                                  <option value="{{$currency->id}}" {{$data->currency_id == $currency->id ? 'selected' : ''}}>{{$currency->code}}</option>
                                  @endforeach
                              </select>
                          </div>


                        <div class="form-group mb-3">
                          <label class="form-label">{{__('Amount')}}</label>
                          <input name="amount" id="amount" class="form-control" placeholder="{{__('Amount')}}" value="{{$data->amount}}" type="number" step="any" >
                        </div>

                        <div class="form-group mb-3">
                          <label class="form-label ">{{__('Payment Link')}}</label>
                          <input name="ref_id" id="ref_id" class="form-control" placeholder="{{__('abc.....')}}" value="{{$data->ref_id}}" type="text" readonly>
                        </div>
                        <input type="hidden" name="user_id" value="{{auth()->id()}}">

                        <div class="modal-footer">
                            <button  type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                        </div>


                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@push('js')
<script>
  'use strict';
</script>
@endpush
