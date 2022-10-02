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
              </h2>
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
        </div>
      </div>
    </div>
  </div>
</div>
</div>
</div>
<!--Row-->
@endsection


