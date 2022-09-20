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
          {{__('Product Order List')}}
        </h2>
      </div>
    </div>
  </div>
</div>


<div class="page-body">
  <div class="container-xl mt-3 mb-3">
      <div class="row row-cards">
          <div class="row justify-content " style="max-height: 1600px;overflow-y: scroll;">
                  @if (count($orders) == 0)
                      <h3 class="text-center py-5">{{__('No Merchant Product Order Data Found')}}</h3>
                  @else
                  @foreach($orders as $key=>$val)
                    <div class="col-lg-3 mb-3">
                        <div class="card">
                            <!-- Card body -->
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-12">
                                        @php
                                            $currency=DB::table('currencies')->where('id', $val->product->currency_id)->first();
                                        @endphp
                                    <h5 class="h4 mb-2 font-weight-bolder">{{__('Product Name: ')}}{{$val->product->name}}</h5>
                                    <h5 class="mb-1">{{__('Name: ')}} {{$val->name}}</h5>
                                    <h5 class="mb-1">{{__('Email: ')}} {{$val->email}}</h5>
                                    <h5 class="mb-1">{{__('Phone: ')}} {{$val->phone}}</h5>
                                    <h5 class="mb-1">{{__('Address: ')}} {{$val->address}}</h5>
                                    <h5 class="mb-1">{{__('Quantity: ')}} {{$val->quantity}}</h5>
                                    <h5 class="mb-1">{{__('Price: ')}} {{$currency->symbol}}{{$val->product->amount}}</h5>
                                    <h5 class="mb-1">{{__('Amount: ')}}  {{$currency->symbol}}{{$val->amount}}</h5>
                                    <h5 class="mb-1">{{__('Type: ')}} {{$val->type}}</h5>
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

