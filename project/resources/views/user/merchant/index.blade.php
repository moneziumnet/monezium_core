@extends('layouts.user')

@push('css')

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
                            <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#modal-success{{$item->id}}"  >
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
                        </a>
                        </div>
                    <div class="modal modal-blur fade" id="modal-success{{$item->id}}" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            <div class="modal-status bg-primary"></div>
                            <div class="modal-body text-center py-1">
                            <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                            <h3>@lang('Edit Wallet Address')</h3>
                            </div>
                            <form id="depositbank_gateway" action="{{ route('user.merchant.cryptowallet.update') }}" method="post"  enctype="multipart/form-data">
                                @csrf
                                <div class="modal-body">
                                    <div class="form-group" id="otp_body">
                                        <label class="form-label required">{{__('Address')}}</label>
                                        <input name="address" id="address" class="form-control" placeholder="{{__('0x....')}}" type="text" step="any" value="{{ $item->wallet_no }}" required>
                                    </div>
                                  <div class="form-group mt-3">
                                    <input type="hidden" id="wallet_id" name="wallet_id" value="{{$item->id}}">
                                   </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" id="submit-btn" class="btn btn-primary">{{ __('Ok') }}</button>
                                </div>
                            </form>
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
                                <button class="btn btn-sm copytext input-group-text" id="copyBoard" onclick="myFunction()"> <i class="fa fa-copy"></i> </button>
                                </div>
                            </div>
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
                                        <img src="{{generateQR($user->email)}}" class="" alt="">
                                    </div>
                                    <h6 class="mt-4">{{$user->email}}</h6>
                                    <input type="hidden" name="email" value="{{$user->email}}">
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
