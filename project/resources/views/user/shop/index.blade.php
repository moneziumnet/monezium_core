@extends('layouts.user')

@push('css')

@endpush

@section('title', __('Shop'))

@section('contents')
<div class="container-fluid">
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
  <div class="container-fluid mt-3 mb-3">
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
                                        <a class="dropdown-item download-qrcode" data-data="{{generateQR(route('user.merchant.product.link', $val->ref_id))}}" href="#"><i class="fas fa-qrcode  me-2"></i>{{__('QR code')}}</a>
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
                                        <span class="badge badge-pill bg-primary"><i class="fas fa-check"></i> {{__('Active')}}</span>
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
<div class="modal fade modal-blur" id="qrcode" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-status bg-success"></div>
            <div class="modal-body text-center py-4">
                <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                <h3>{{__('QR code')}}</h3>
                <form action="{{route('user.merchant.download.qr')}}" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                        <div >
                            <img src="" class="" id="qr_code" alt="">
                        </div>
                        <input type="hidden" id="email" name="email" value="">
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary btn-lg">@lang('Download')</button>
                        </div>
                </form>
              </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <div class="page-pretitle">
            {{__('Overview')}}
          </div>
          <h2 class="page-title">
            {{__('Campaign List')}}
          </h2>
        </div>
      </div>
    </div>
</div>


<div class="page-body">
    <div class="container-fluid mt-3 mb-3">
        <div class="row row-cards">
            <div class="row justify-content " style="max-height: 1600px;overflow-y: scroll;">
                    @if (count($campaigns) == 0)
                        <h3 class="text-center py-5">{{__('No Campaign Data Found')}}</h3>
                    @else
                    @foreach($campaigns as $key=>$val)
                      <div class="col-lg-3 mb-3">
                          <div class="card">
                              <img class="back-preview-image"
                                  src="{{asset('assets/images')}}/{{$val->logo}}"
                              alt="Campaign Logo">
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
                                          <a class="dropdown-item" href="{{route('user.campaign.donate', ['id' => $val->id])}}"><i class="fas fa-donate me-2"></i>{{__('Donate')}}</a>
                                          <a class="dropdown-item download-qrcode" data-data="{{generateQR(route('user.merchant.campaign.link', $val->ref_id))}}" href="#"><i class="fas fa-qrcode  me-2"></i>{{__('QR code')}}</a>
                                      </div>
                                      </div>
                                  </div>
                                  <div class="row mb-3">
                                      <div class="col-12">
                                      <h5 class="h4 mb-2 font-weight-bolder">{{__('Campaign Title: ')}}{{$val->title}}</h5>
                                      <h5 class="mb-1">{{__('Category: ')}} {{$val->category->name}}</h5>
                                      <h5 class="mb-1">{{__('Organizer: ')}} {{$val->user->company_name ?? $val->user->name}}</h5>
                                      <h5 class="mb-1">{{__('Goal: ')}} {{$val->currency->symbol}}{{$val->goal}}</h5>
                                      @php
                                          $total = DB::table('campaign_donations')->where('campaign_id', $val->id)->where('status', 1)->sum('amount');
                                      @endphp
                                      <h5 class="mb-1">{{__('FundsRaised: ')}} {{$val->currency->symbol}}{{$total}}</h5>
                                      <h5 class="mb-1">{{__('Deadline: ')}} {{$val->deadline}}</h5>
                                      <h5 class="mb-3">{{__('Created Date:')}} {{$val->created_at}}</h5>
                                      <h6 class="mb-3">{{__('Description:')}} {{$val->description}}</h6>
                                      @if($val->status==1)
                                          <span class="badge badge-pill bg-primary"><i class="fas fa-check"></i> {{__('Active')}}</span>
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
@push('js')
<script>
        'use strict';
        $('.download-qrcode').on('click', function() {
            $('#qrcode').modal('show');
            $('#email').val($(this).data('data'));
            $('#qr_code').attr('src' , $(this).data('data'));
        })
    </script>
@endpush

