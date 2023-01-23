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

            <a class="btn btn-primary d-sm-inline-block" href="{{route('user.shop.order', $product->id)}}">
              <i class="fas fa-backward me-1"></i> {{__('Back')}}
            </a>
      </div>
    </div>
  </div>
</div>


            <div class="page-body">
            <div class="container-xl">
                <div class="row row-cards">
                    <div class="col-12">
                        <div class="card p-5">
                                @includeIf('includes.flash')
                                <form action="{{route('user.merchant.product.crypto.pay', $product->id)}}" method="GET" id="pay_form" enctype="multipart/form-data">
                                    <div class="text-center">
                                        <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                                        <div class="col">
                                            <h2>{{$product->name}}
                                            </h2>
                                            <div class="page-pretitle">
                                            {{$product->description}}
                                            </div>
                                        </div>
                                        <h3></h3>
                                    </div>
                                    <div class="form-group mb-3 mt-3">
                                        <label class="form-label required">{{__('Amount')}}({{$product->currency->code}})</label>
                                        @if ($product->amount == 0)
                                        <input name="amount" id="amount" class="form-control" autocomplete="off"  type="number" step="any" value="{{ $product->amount }}" required>
                                        @else
                                        <input name="amount" id="amount" class="form-control" autocomplete="off"  type="text" value="{{ $product->amount }}" readonly required>
                                        @endif
                                    </div>
                                    <div class="form-group mb-3 mt-3">
                                        <label class="form-label required">{{__('Quantity')}}({{__('Total quantity:')}}{{$product->quantity}})</label>
                                        <input name="quantity" id="quantity" class="form-control" autocomplete="off"  type="number" value="" max="{{$product->quantity}}" required>
                                    </div>
                                    <input type="hidden" name="product_id" value="{{$product->id}}">
                                    <div class="form-footer">
                                        @foreach($cryptolist as $currency)
                                        <button type="submit" id="submit" name="link_pay_submit" value="{{$currency->id}}" class="btn btn-primary w-100">{{$currency->curr_name}} - {{$currency->code}}</button>
                                        <h3></h3>
                                    @endforeach
                                    </div>
                                </form>
                        </div>
                    </div>
                </div>
            </div>
            </div>


@endsection
