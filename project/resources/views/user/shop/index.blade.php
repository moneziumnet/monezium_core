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
          {{__('Shop Product List')}}
        </h2>
      </div>
    </div>
  </div>
</div>


<div class="page-body">
  <div class="container-xl mt-3 mb-3">
      <div class="row row-cards">
          <div class="row justify-content " style="max-height: 1600px;overflow-y: scroll;">
                  @if (count($products) == 0)
                      <h3 class="text-center py-5">{{__('No Shop Product Data Found')}}</h3>
                  @else
                  @foreach($products as $key=>$val)
                    <div class="col-lg-3 mb-3">
                        <div class="card">
                            <img class="back-preview-image"
                                @php
                                    $image=DB::table('product_images')->whereProductId($val->id)->first();
                                @endphp
                                src="{{asset('assets/images')}}/{{$image->image}}"
                            alt="Image placeholder">
                            <!-- Card body -->
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-8">
                                    </div>
                                    <div class="col-4 text-end">
                                    <a class="mr-0" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-chevron-circle-down"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-left">
                                        <a class="dropdown-item" href="{{route('user.shop.order', $val->id)}}"><i class="fas fa-shopping-cart me-2"></i>{{__('Order')}}</a>
                                    </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-12">
                                    <h5 class="h4 mb-2 font-weight-bolder">{{__('Product Name: ')}}{{$val->name}}</h5>
                                    <h5 class="mb-1">{{__('Shop: ')}} {{$val->shop->name}}</h5>
                                    <h5 class="mb-1">{{__('Category: ')}} {{$val->category->name}}</h5>
                                    <h5 class="mb-1">{{__('Amount: ')}} {{$val->currency->symbol}}{{$val->amount}}</h5>
                                    <h5 class="mb-1">{{__('Sold: ')}} {{$val->sold ?? '0'}}{{__('/')}}{{$val->sold+$val->quantity}}</h5>
                                    <h5 class="mb-3">{{__('Currenct Stock:')}} {{$val->quantity}}</h5>
                                    @if($val->status==1)
                                        <span class="badge badge-pill bg-success"><i class="fas fa-check"></i> {{__('Active')}}</span>
                                    @else
                                        <span class="badge badge-pill bg-danger"><i class="fas fa-ban"></i> {{__('Disabled')}}</span>
                                    @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                  @endforeach
                  @endif
              </div>
      </div>
  </div>
</div>

@endsection


