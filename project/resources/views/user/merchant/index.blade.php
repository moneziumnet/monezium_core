@extends('layouts.user')

@push('css')
<link href="{{asset('assets/front/css/default.min.css')}}" rel="stylesheet"/>
@endpush

@section('contents')
<div class="container-xl">
        <div class="container-xl">
            <div class="page-header d-print-none">
                <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                    {{__('Merchant Shop Account')}}
                    </h2>
                </div>
                </div>
            </div>
        </div>
        <div class="page-body">
            <div class="container-xl">
            <div class="row justify-content " style="max-height: 368px;overflow-y: scroll;">
                @if (!isset($wallet))
                    @foreach ($wallets as $item)
                    @if ($item->currency->type == 1)
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
                    @endif
                    @endforeach
                @else
                    <p class="text-center">@lang('NO Wallet Found')</p>
                @endif
            </div>
            </div>
        </div>
        @if (isEnabledUserModule('Crypto'))
        <div class="container-xl">
            <div class="page-header d-print-none">
                <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                    {{__('Merchant Crypto Wallet')}}
                    </h2>
                </div>
                </div>
            </div>
        </div>
        <div class="page-body">
            <div class="container-xl">
            <div class="row justify-content " style="max-height: 368px;overflow-y: scroll;">
                @if (!isset($wallet))
                    @foreach ($wallets as $item)
                    @if ($item->currency->type == 2)
                    <div class="col-sm-6 col-md-4 mb-3" >
                        <div>
                            <div class="card h-100 card--info-item">
                                <div class="text-end icon">
                                <i class="fas ">
                                    {{$item->currency->symbol}}
                                </i>
                                </div>
                                <div class="card-body">
                                <div class="h3 m-0 text-uppercase"> {{__($item->shop->name)}}</div>
                                <div class="h4 m-0"> {{ $item->wallet_no }}</div>
                                <div class="text-muted">{{Crypto_Merchant_Balance($item->merchant_id, $item->currency_id, $item->shop_id) }}  {{$item->currency->code}}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                @else
                    <p class="text-center">@lang('NO Wallet Found')</p>
                @endif
            </div>
            </div>
        </div>
        @endif
</div>

<div class="row container-xl" style="margin-left: auto; margin-right:auto;">
    <div class="col-md-6">
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
                                <button class="btn btn-sm copytext input-group-text" id="copyBoard" onclick="cpApiKey()"> <i class="fa fa-copy"></i> </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <p class="me-auto">{{ __('Integrating Website Payment') }}</p>
                                <button class="btn" onclick="cpSampleCode()"><i class="fa fa-copy"></i></button>
                            </div>
                            <pre><code id="api-sample-code" class="language-html">&lt;div id="mt-payment-system"
    data-sitekey="{{@$cred->access_key}}"
    data-siteurl="{{url('/')}}"
    data-currency="USD"
    data-amount="500"
    fn-success="onsuccess"
    fn-error="onerror"&gt;
    &lt;button&gt;Buy&lt;/button&gt;
&lt;/div&gt;
&lt;script src="{{url('/assets/api/payment.js')}}"&gt;&lt;/script&gt;
&lt;script&gt;
  var onsuccess = function (message) {
    // This param is a success message, e.g:; "Wallet Payment Completed."
    alert(message);
  }
  var onerror = function (message) {
    // This param is a error message, e.g:; "Insufficient balance."
    alert(message);
  }
&lt;/script&gt;</code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <div class="col-md-6">
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
                                <form action="{{route('user.merchant.download.qr')}}" method="POST" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                    <div >
                                        <img src="{{generateQR(url('/qr/access?site_key=').$cred->access_key)}}" class="" alt="">
                                    </div>
                                    <h6 class="mt-4">{{url('/qr/access?site_key=').$cred->access_key}}</h6>
                                    <input type="hidden" name="email" value="{{url('/qr/access?site_key=').$cred->access_key}}">
                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-primary btn-lg">@lang('Download')</button>
                                    </div>
                                </form>
                            </div>
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
<script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/11.6.0/highlight.min.js"></script>
<script>
    'use strict';
    hljs.highlightAll();
    function cpApiKey() {
      var copyText = document.getElementById("cronjobURL");
      copyText.select();
      copyText.setSelectionRange(0, 99999);
      document.execCommand("copy");
      toastr.options =
      {
        "closeButton" : true,
        "progressBar" : true
      }
      toastr.success("Copied.");
    }
    function cpSampleCode() {
        var copyText = document.getElementById("api-sample-code").textContent;
        console.log(copyText);
        const textArea = document.createElement('textarea');
        textArea.textContent = copyText;
        textArea.style.position = "absolute";
        textArea.style.left = "-100%";
        document.body.append(textArea);
        textArea.select();
        document.execCommand("copy");
        toastr.options =
        {
            "closeButton" : true,
            "progressBar" : true
        }
        toastr.success("Copied.");
        textArea.remove();
    }
  </script>
@endpush
