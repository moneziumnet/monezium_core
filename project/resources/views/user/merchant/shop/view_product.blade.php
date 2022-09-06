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
          {{__('Merchant Product List')}}
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
                      <h3 class="text-center py-5">{{__('No Merchant Shop Data Found')}}</h3>
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
                                    <p class="text-sm  mb-2"><a class="btn-icon-clipboard" data-clipboard-text="" title="Copy">{{__('COPY LINK')}} <i class="fas fa-link text-xs"></i></a></p>
                                    </div>
                                    <div class="col-4 text-end">
                                    <a class="mr-0" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-chevron-circle-down"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-left">
                                        <a class="dropdown-item" href="{{route('user.merchant.product.edit', $val->id)}}"><i class="fas fa-pencil-alt me-2"></i>{{__('Edit')}}</a>
                                        {{-- <a class="dropdown-item" href="{{route('orders', ['id' => $val->id])}}"><i class="fas fa-sync"></i>{{__('Orders')}}</a> --}}
                                        <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#delete{{$val->id}}" href="#"><i class="fas fa-trash-alt  me-2"></i>{{__('Delete')}}</a>
                                    </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-12">
                                    <h5 class="h4 mb-2 font-weight-bolder">{{__('Product Name: ')}}{{$val->name}}</h5>
                                    <h5 class="mb-1">{{__('Shop: ')}} {{$val->shop->name}}</h5>
                                    <h5 class="mb-1">{{__('Category: ')}} {{$val->category->name}}</h5>
                                    <h5 class="mb-1">{{__('Amount: ')}} {{$val->currency->symbol}}{{$val->amount}}</h5>
                                    <h5 class="mb-1">{{__('Sold: ')}} {{$val->sold}}</h5>
                                    <h5 class="mb-3">{{__('Currenct Stock:')}} {{$val->quantity}}</h5>
                                    @if($val->status==1)
                                        <span class="badge badge-pill badge-primary"><i class="fas fa-check"></i> {{__('Active')}}</span>
                                    @else
                                        <span class="badge badge-pill badge-danger"><i class="fas fa-ban"></i> {{__('Disabled')}}</span>
                                    @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade modal-blur" id="delete{{$val->id}}" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    <div class="modal-status bg-success"></div>

                                    <div class="modal-body text-center py-4">
                                        <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                                        <h3>{{__('Confirm Delete')}}</h3>
                                        <p class="text-center mt-3">{{ __("Do you want to delete this product?") }}</p>
                                      </div>

                                      <div class="modal-footer">
                                        <a href="javascript:;" class="btn btn-secondary" data-bs-dismiss="modal">{{ __("Cancel") }}</a>
                                        <a href="{{route('user.merchant.product.delete', $val->id)}}" class="btn shadow-none btn-primary" >@lang('Proceed')</a>
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


