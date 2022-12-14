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
                $accounttype = array('XETH'=>'ETH', 'XXBT'=>'BTC');
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
                                        <a class="dropdown-item" href="javascript:;" onclick="deposit('{{$key}}', '{{$keyword}}')">{{ __('Deposit') }}</a>
                                        @if($type == 'ETH' || $type == 'BTC')
                                            <a class="dropdown-item" href="javascript:;" onclick="exchange('{{$key}}', '{{__('XETH')}}')">{{ __('Exchange To') }} {{$type=='ETH' ? 'BTC' : 'ETH'}}</a>
                                        @else
                                            <a class="dropdown-item" href="javascript:;" onclick="exchange('{{$key}}', '{{__('XETH')}}')">{{ __('Exchange To ETH') }}</a>
                                            <a class="dropdown-item" href="javascript:;" onclick="exchange('{{$key}}', '{{__('XXBT')}}')">{{ __('Exchange To BTC') }}</a>
                                        @endif
                                        @if($type == 'ETH' || $type == 'BTC')
                                            <a class="dropdown-item" href="javascript:;" onclick="withdraw('{{$key}}')">{{ __('Withdraw To System') }}</a>
                                        @endif
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

<div class="modal modal-blur fade" id="modal-deposit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-status bg-primary"></div>
        <div class="modal-body py-4">
        <div class="text-center"><i  class="fas fa-info-circle fa-3x text-primary mb-2"></i></div>
        <h3 class="text-center">@lang('Deposit')</h3>
            <input name="pair" id="pair" type="hidden">
            <input name="keyword" value="{{$keyword}}" type="hidden">
            <div class="form-group mt-3">
                <label class="form-label required">{{__('Deposit Method')}}</label>
                <select name="method" id="depositmethod" class="form-control" required>
                    <option value="">{{ __('Select Deposit Method') }}</option>
                </select>
            </div>
            <div id="address_list" class="mb-3">
            </div>
            <div class="form-group">
                <button id="deposit_confirm" class="btn btn-primary w-100">{{__('Confirm')}}</button>
              </div>

        </div>
    </div>
    </div>
</div>

<div class="modal modal-blur fade" id="modal-exchange" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-status bg-primary"></div>
        <div class="modal-body py-4">
        <div class="text-center"><i  class="fas fa-info-circle fa-3x text-primary mb-2"></i></div>
        <h3 class="text-center">@lang('Exchange')</h3>
        <form action="{{route('admin.system.crypto.order')}}" method="post" class="m-3" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="inp-name">{{ __('Exchange Amount') }}</label>
                <input name="amount" class="form-control" autocomplete="off" placeholder="{{__('Eth Amount')}}" value="" type="number" min="0.01" step="any" required>
            </div>
            <input name="pair_type" id="pair_type" type="hidden" value="">
            <input name="order_type" id="order_type" type="hidden" value="">
            <input name="keyword" value="{{$keyword}}" type="hidden">


            <div class="form-group">
                <button type="submit" class="btn btn-primary w-100">{{__('Exchange')}}</button>
              </div>

            </form>
        </div>
    </div>
    </div>
</div>

<div class="modal modal-blur fade" id="modal-withdraw" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-status bg-primary"></div>
        <div class="modal-body py-4">
        <div class="text-center"><i  class="fas fa-info-circle fa-3x text-primary mb-2"></i></div>
        <h3 class="text-center">@lang('Withdraw To System')</h3>
        <form action="{{route('admin.system.crypto.withdraw')}}" method="post" class="m-3" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="inp-name">{{ __('Key') }}</label>
                <input name="withdraw_key" id="withdraw_key" class="form-control" autocomplete="off" placeholder="{{__('New Withdraw Key')}}" value="" type="text" readonly>
            </div>

            <div class="form-group">
                <label for="inp-name">{{ __('Avaliable Amount') }}</label>
                <input name="available_amount" id="available_amount" class="form-control" autocomplete="off" placeholder="{{__('Amount')}}" value="{{$balance->XXBT ?? ''}}" type="number"  readonly>
            </div>

            <div class="form-group">
                <label for="inp-name">{{ __('Withdraw Amount') }}</label>
                <input name="amount" class="form-control" autocomplete="off" placeholder="{{__('Amount')}}" value="" type="number" step="any">
            </div>

            <input name="keyword" value="{{$keyword}}" type="hidden">
            <input name="asset" id="asset" type="hidden">

            <div class="form-group">
                <button type="submit" class="btn btn-primary w-100">{{__('Submit')}}</button>
              </div>

            </form>
        </div>
    </div>
    </div>
</div>



<!--Row-->
@endsection
@section('scripts')
<script type="text/javascript">
    "use strict";
    function deposit(asset, keyword) {
        $('#address_list').html('');
        var url = '{{route('admin.system.crypto.depositMethods')}}'
        var token = '{{ csrf_token() }}';
        var data  = {asset:asset,keyword:keyword,_token:token}
        let _optionHtml = '<option value="">Select method.</option>' ;
        $.post(url,data, function(res) {
            if(res.length == 0) {
                _optionHtml = '<option value="">There is no method.</option>';
            }
            else {
                $.each(res, function(i, item) {
                    _optionHtml += '<option value="'+ item.method +'" data-asset="'+ asset +'">'+ item.method +'</option>';
                })
            }
            $('select#depositmethod').html(_optionHtml);
            $('#modal-deposit').modal('show');
          })
    }

    function exchange(to, from='XETH') {
        if (to=='XETH') {
            $('#pair_type').val(from+'XXBT')
            $('#order_type').val('sell')
        }
        else {
            $('#pair_type').val(from+to)
            $('#order_type').val('buy')
        }
        $('#modal-exchange').modal('show')
    }

    function withdraw(asset) {
        if (asset == 'XETH') {
            $('#withdraw_key').val('{{$api? $api->withdraw_eth: ""}}')
            $('#available_amount').val('{{$balance?$balance->XETH :""}}')
        }
        else {
            $('#withdraw_key').val('{{$api?$api->withdraw_btc:""}}')
            $('#available_amount').val('{{$balance?$balance->XXBT : ""}}')
        }
        $('#asset').val(asset)

        $('#asset').val(asset)
        $('#modal-withdraw').modal('show')
    }

    $('#depositmethod').on('change', function() {
        var url = '{{route('admin.system.crypto.depositaddress')}}'
        var token = '{{ csrf_token() }}';
        var keyword = '{{$keyword}}';
        var method = $('#depositmethod').val();
        var asset = $('#depositmethod option:selected');


        var data  = {asset:asset.data('asset'),keyword:keyword,method:method,_token:token}
        var _divHtml = '';
        $.post(url,data, function(res) {
            if(res.length == 0) {
                _divHtml = '<h5 class="text-center">There is no available address.</h5>';
            }
            else {
                $.each(res, function(i, item) {
                    _divHtml += '<div class="form-group"> \
                        <label for="inp-name">Address</label> \
                        <input class="form-control" autocomplete="off" value="'+ item.address +'" type="text"  readonly> \
                    </div>'
                })
            }
            $('#address_list').html(_divHtml);
          })
    })
    $('#deposit_confirm').on('click', function() {
        $('#modal-deposit').modal('hide')

    })
</script>
@endsection



