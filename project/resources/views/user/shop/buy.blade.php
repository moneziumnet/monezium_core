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
                            <input type="radio" name="payment" value="gateway" class="form-selectgroup-input" >
                            <span class="form-selectgroup-label">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-credit-card" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <rect x="3" y="5" width="18" height="14" rx="3"></rect>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                    <line x1="7" y1="15" x2="7.01" y2="15"></line>
                                    <line x1="11" y1="15" x2="13" y2="15"></line>
                                </svg>
                                @lang('Pay with gateways')</span>
                        </label>
                        <label class="form-selectgroup-item">
                        <input type="radio" name="payment" value="wallet" class="form-selectgroup-input" checked="">
                        <span class="form-selectgroup-label">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-wallet" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12"></path>
                                <path d="M20 12v4h-4a2 2 0 0 1 0 -4h4"></path>
                            </svg>
                            @lang('Pay with customer wallet')
                            </span>
                        </label>

                    </div>

                    <div class="form-group ms-5 mt-5" >
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


