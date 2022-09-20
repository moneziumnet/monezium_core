@extends('layouts.user')

@push('css')

@endpush



@section('contents')

<div class="container-xl">
  <div class="page-header d-print-none">
    @include('user.merchant.tab')
    <div class="row align-items-center">
      <div class="col">
        <div class="page-pretitle">
          {{__('Overview')}}
        </div>
        <h2 class="page-title">
          {{__('Checkout list')}}
        </h2>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">

          <a id="create_form" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
            {{__('Create Checkout')}}
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
    <div class="row row-cards">
      <div class="row justify-content " style="max-height: 800px;overflow-y: scroll;">
          @if (count($checkouts) != 0)
              @foreach ($checkouts as $item)
              <div class="col-lg-4 mb-2">
                  <div class="card">
                    <!-- Card body -->
                    <div class="card-body">
                      <div class="row justify-content-between align-items-center">
                        <div class="col">
                            <h5 class="h4 mb-1 font-weight-bolder">{{$item->name}}</h5>
                        </div>
                        <div class="col-auto nav-item dropdown">
                            <p class="text-sm text-dark mb-2"><a class="btn btn-dark btn-sm copy" data-clipboard-text="{{route('user.merchant.checkout.link', $item->ref_id)}}" title="Copy">{{__('COPY LINK')}} <i class="fas fa-link text-xs"></i></a></p>
                          <a class="mr-0 nav-link" data-bs-toggle="dropdown">
                            <i class="fas fa-chevron-circle-down "></i>
                          </a>
                          <div class="dropdown-menu">
                            <a href="{{route('user.merchant.checkout.edit', ['id'=>$item->id])}}" class="dropdown-item">{{__(' Edit')}}</a>
                            <a href="{{route('user.merchant.checkout.status', ['id'=>$item->id])}}" class="dropdown-item">{{$item->status == 1 ? __('Disable') :__('Enable')}}</a>
                            <a href="{{route('user.merchant.checkout.delete', ['id'=>$item->id])}}" class="dropdown-item">{{__('Delete')}}</a>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col">
                          <p>{{__('Description')}}: {{$item->description}}</p>
                          <p>{{__('Reference')}}: {{$item->ref_id}}</p>
                          <p>{{__('Amount')}}: @if($item->amount==null) Not fixed @else {{$item->currency->symbol.$item->amount}}({{$item->currency->code}}) @endif</p>
                          <p>{{__('Redirect URL')}}: {{$item->redirect_link}}</p>
                          <p class="text-sm mb-2">{{__('Date')}}: {{date("h:i:A j, M Y", strtotime($item->created_at))}}</p>
                          @if($item->status==1)
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
          @else
              <p class="text-center">@lang('NO Checkout FOUND')</p>
          @endif

      </div>
    </div>
  </div>

  <div class="modal modal-blur fade" id="modal-form" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{('Create CheckOut')}}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <form action="{{route('user.merchant.checkout.store')}}" method="POST" enctype="multipart/form-data">
                  @csrf
                  <div class="form-group mb-3 mt-3">
                      <label class="form-label required">{{__('Title')}}</label>
                      <input name="name" id="name" class="form-control" placeholder="{{__('Title')}}" type="text"  required>
                  </div>

                  <div class="form-group mb-3">
                    <label class="form-label">{{__('Description')}}</label>
                    <input name="description" id="description" class="form-control" placeholder="{{__('Description')}}" type="text" >
                  </div>

                  <div class="form-group mb-3">
                    <label class="form-label required">{{__('Select Shop')}}</label>
                    <select name="shop_id" id="shop_id" class="form-control" required>
                      <option value="">Select</option>
                      @foreach($shops as $shop)
                      <option value="{{$shop->id}}">{{$shop->name}}</option>
                      @endforeach
                    </select>
                  </div>

                    <div class="form-group mb-3 mt-3">
                        <label class="form-label required">{{__('Select Currency')}}</label>
                        <select name="currency_id" id="currency_id" class="form-control" required>
                            @foreach($currencylist as $currency)
                            <option value="{{$currency->id}}">{{$currency->code}}</option>
                            @endforeach
                        </select>

                    </div>

                  <div class="form-group mb-3">
                    <label class="form-label">{{__('Amount')}}</label>
                    <input name="amount" id="amount" class="form-control" placeholder="{{__('Amount')}}" type="text" >
                  </div>

                  <div class="form-group mb-3">
                    <label class="form-label ">{{__('Redirect URL')}}</label>
                    <input name="redirect_link" id="redirect_link" class="form-control" placeholder="{{__('https://...')}}" type="text" >
                  </div>
                  <input type="hidden" name="user_id" value="{{auth()->id()}}">

                  <div class="modal-footer">
                      <button  id="submit-btn" class="btn btn-primary">{{ __('Create') }}</button>
                  </div>
              </form>
          </div>

        </div>
      </div>
  </div>
</div>
@endsection

@push('js')
<script src="{{asset('assets/user/js/clipboard.min.js')}}"></script>

    <script>
        'use strict';
        $('#create_form').on('click', function(){
            $('#modal-form').modal('show');
        })
        var clipboard = new ClipboardJS('.copy');
        clipboard.on('success', function(e) {
           console.log('success','Contract URL Copied')
        });
    </script>

@endpush
