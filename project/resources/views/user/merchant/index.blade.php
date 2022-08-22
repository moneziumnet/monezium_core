@extends('layouts.user')

@push('css')

@endpush

@section('contents')

<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
        <div class="col">
            </div>
            <h2 class="page-title">
            {{__('Merchant Shop Account')}}
            </h2>
        </div>
        </div>
    </div>

<div class="page-body">
    <div class="container-xl">
    <div class="row justify-content " style="max-height: 368px;overflow-y: scroll;">
        @if (!isset($wallet))
            @foreach ($wallets as $item)
            <div class="col-sm-6 col-md-4 mb-3">
                <div class="card h-100 card--info-item">
                    <div class="text-end icon">
                    <i class="fas ">
                        {{$item->currency->symbol}}
                    </i>
                    </div>
                    <div class="card-body">
                    <div class="h3 m-0 text-uppercase"> {{__($item->shop->name)}}</div>
                    <div class="h4 m-0 text-uppercase"> {{ $item->wallet_no }}</div>
                    <div class="text-muted">{{ amount($item->balance,$item->currency->type,2) }}  {{$item->currency->code}}</div>
                    </div>
                </div>
            </div>
            @endforeach
        @else
            <p class="text-center">@lang('NO Wallet Found')</p>
        @endif
    </div>
    </div>
</div>

<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('QR CODE')}}
          </h2>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        <div class="row row-cards">
            <div class="col-12">
              <div class="qr--code">
                <div class="card">
                    <div class="card-body text-center">
                        <div >
                            <img src="{{generateQR($user->email)}}" class="" alt="">
                        </div>
                        <h6 class="mt-4">{{$user->email}}</h6>
                        <div class="mt-3">
                            <a href="{{route('user.merchant.download.qr',$user->email)}}" class="btn btn-primary btn-lg btn-download">@lang('Download')</a>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>

<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('API Access Key')}}
          </h2>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <p>{{ __('API Access Key') }}</p>
                            <div class="input-group input--group">
                              <input type="text" name="key" value="{{@$cred->access_key}}" class="form-control" id="cronjobURL" readonly>
                              <button class="btn btn-sm copytext input-group-text" id="copyBoard" onclick="myFunction()"> <i class="fa fa-copy"></i> </button>
                            </div>
                          </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
    'use strict';

    function myFunction() {
      var copyText = document.getElementById("cronjobURL");
      copyText.select();
      copyText.setSelectionRange(0, 99999);
      document.execCommand("copy");
      alert('copied');
    }
  </script>
@endpush