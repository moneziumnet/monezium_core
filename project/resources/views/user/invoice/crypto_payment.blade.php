@extends('layouts.user')

@section('title')
   @lang('Invoice Cypto Payment')
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
            {{__('Invoice Crypto Payment')}}
          </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <a class="btn btn-primary d-sm-inline-block" href="{{route('user.invoice.payment', encrypt($invoice->number))}}">
                <i class="fas fa-backward me-1"></i> {{__('Back')}}
            </a>
        </div>
      </div>
    </div>
</div>
<div class="container-xl mt-3 mb-3">
    <div class="row row-cards">
        <div class="col-12">
            <div class="card p-5">
                @includeIf('includes.flash')
                <form action="{{route('user.invoice.payment.submit')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="text-center">
                        <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                        <div class="col">
                            <h2>{{$invoice->type}} {{$invoice->number}}</h2>
                            <div class="page-pretitle">
                                {{$invoice->description}}
                            </div>
                        </div>
                        <h3></h3>

                        <img id="qrcode" src="{{'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl='.$wallet->wallet_no.'&choe=UTF-8'}}" class="" alt="">
                    </div>
                    <div class="text-center mt-2">
                        <span id="qrdetails" class="ms-2 check">{{__($wallet->wallet_no)}}</span>
                    </div>

                    <div class="form-group mb-3 mt-3">
                        <label class="form-label required">{{$wallet->currency->code}} {{__('Address')}}</label>
                        <input name="address" id="address" class="form-control" autocomplete="off"  type="text" pattern="[^()/><\][\\;!|]+" value="{{ $wallet->wallet_no }}" readonly required>
                    </div>

                    <div class="form-group mb-3 mt-3">
                        <label class="form-label required">{{__('Amount')}}</label>
                        <input name="amount" id="amount" class="form-control" autocomplete="off"  type="number" step="any" value="{{ $total_amount/$cal_amount }}" readonly required>
                    </div>

                    <input type="hidden" name="id" value="{{$invoice->id}}">
                    <input type="hidden" name="currency_id" value="{{$wallet->currency->id}}">
                    <input type="hidden" name="payment" value="crypto">
                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary w-100">{{__('Done')}}</button>
                    </div>
                </form>
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
