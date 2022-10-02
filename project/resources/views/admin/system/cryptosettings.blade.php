@extends('layouts.admin')
@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between py-3">
    <ol class="breadcrumb py-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.system.crypto.api', $keyword) }}">{{ ucfirst($keyword) }}</a></li>
    </ol>
  </div>
</div>


<div class="row mt-3">
  <div class="col-lg-12">
    <div class="card mt-1 tab-card">
      @include('admin.system.systemcryptotab')

      <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show p-3 active" id="settings" role="tabpanel" aria-labelledby="settings-tab">
          <div class="col">
            <h3 class="page-title">
              {{__('Api Setting')}}
              </h3>
          </div>

          <div class="card-body">
            <form class="geniusform" id="request-form" action="{{ route('admin.system.crypto.api.save') }}" method="POST" enctype="multipart/form-data">
              @include('includes.admin.form-both')
              {{ csrf_field() }}
              <div class="form-group">
                <label for="inp-name">{{ __('Api Key') }}</label>
                <input name="api_key" class="form-control" autocomplete="off" placeholder="{{__('New Api Key')}}" value="{{$api->api_key ?? ''}}" type="text">
              </div>
              <div class="form-group">
                <label for="inp-name">{{ __('Api Secret') }}</label>
                <input name="api_secret" class="form-control" autocomplete="off" placeholder="{{__('New Api Secret')}}" value="{{$api->api_secret ?? ''}}" type="text">
              </div>
              <div class="form-group">
                <label for="inp-name">{{ __('Withdraw Eth key') }}</label>
                <input name="withdraw_eth" class="form-control" autocomplete="off" placeholder="{{__('Add Withdraw Ethereum Key')}}" value="{{$api->withdraw_eth ?? ''}}" type="text">
              </div>
              <div class="form-group">
                <label for="inp-name">{{ __('Withdraw BTC key') }}</label>
                <input name="withdraw_btc" class="form-control" autocomplete="off" placeholder="{{__('Add Withdraw BTC Key')}}" value="{{$api->withdraw_btc ?? ''}}" type="text">
              </div>
              <input type="hidden" name="keyword" value="{{$keyword}}">
              <div class="form-group">
                <button type="submit" class="btn btn-primary w-100">{{__('Submit')}}</button>
              </div>
            </form>
          </div>

          <div class="col">
            <h3 class="page-title">
              {{__($keyword.(' Accounts'))}}
              </h2>
          </div>
          @php
                $accounttype = array('ZUSD'=>'USD', 'ZEUR'=>'EUR', 'ZGBP'=>'GBP', 'XETH'=>'ETH', 'XXBT'=>'BTC');
          @endphp
          <div class="row mb-3">
            @foreach ($accounttype as $key => $type )

            <div class="col-xl-3 col-md-6  mt-3  mb-4">
                <div class="card h-100" >
                <div class="card-body">
                    <div class="row align-items-center">
                    <div class="col">
                        <div class="d-flex mb-1 mr-1 align-items-start">
                            <div class='font-weight-bold text-gray-900 w-75 mr-auto'>{{$type}}<br/></div>
                            <div class='font-weight-bold text-gray-900 w-25 text-right'>
                                <div class="dropdown">
                                    <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown" style="padding: 6px 11px 1px 7px; border-radius: 50%;">
                                        <span class="caret"></span>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        {{-- <a class="dropdown-item" href="javascript:;" onclick="getDetails({{$wallet->id}})">{{ __('Fee') }}</a>
                                        <a class="dropdown-item" href="javascript:;" onclick="Deposit({{$wallet->id}})">{{ __('Deposit') }}</a>
                                        <a class="dropdown-item" href="{{route('admin-wallet-transactions', ['user_id' => $data->id, 'wallet_id'=>$wallet->id])}}">{{ __('Transaction View') }}</a> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">{{$balance->$key ?? '0.000000'}} </div>
                    </div>
                    </div>
                </div>
                </div>
            </div>
            @endforeach
          </div>

        </div>
      </div>
    </div>
  </div>
</div>



<!--Row-->
@endsection


