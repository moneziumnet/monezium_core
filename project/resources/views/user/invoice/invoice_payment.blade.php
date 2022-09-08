@extends('layouts.user')

@section('title')
   @lang('Invoice Payment')
@endsection

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center mt-3">
        <div class="col">
          <div class="page-pretitle">
            {{__('Overview')}}
          </div>
          <h2 class="page-title">
            {{__('Invoice Payment')}}
          </h2>
        </div>
      </div>
    </div>
</div>
<div class="container-xl mt-3 mb-3">
    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                <form action="" method="post" enctype="multipart/form-data">
                    @csrf
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between">{{__('Name :')}} <span class="font-weight-bold">{{$invoice->invoice_to}}</span> </li>
                            <li class="list-group-item d-flex justify-content-between">{{__('Email :')}} <span class="font-weight-bold">{{$invoice->email}}</span> </li>
                            <li class="list-group-item d-flex justify-content-between">{{__('Amount :')}} <span class="font-weight-bold">{{amount($invoice->final_amount,$invoice->currency->type,2).' '.$invoice->currency->code}}</span> </li>
                            <li class="list-group-item d-flex justify-content-between">{{__('Tax :')}} <span class="font-weight-bold">{{amount($tax_value,$invoice->currency->type,2).' '.$invoice->currency->code}}</span> </li>
                        </ul>

                        <div class="text-center mt-3">
                            <div class="form-selectgroup">

                                <label class="form-selectgroup-item">
                                <input type="radio" name="payment" value="wallet" class="form-selectgroup-input" checked="">
                                <span class="form-selectgroup-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-wallet" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12"></path>
                                        <path d="M20 12v4h-4a2 2 0 0 1 0 -4h4"></path>
                                    </svg>
                                    {{__('Pay with system wallet')}}
                                    </span>
                                </label>

                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-block">{{__('Next')}} <i class="ms-2 fas fa-long-arrow-alt-right"></i></button>
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
