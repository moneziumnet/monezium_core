@extends('layouts.admin')
@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ $data->name }}</h5>
    <ol class="breadcrumb py-0 m-0">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.user.index') }}">{{ __('User List') }}</a></li>
    </ol>
  </div>
</div>


<div class="row mt-3">
  <div class="col-lg-12">
    <div class="card mt-1 tab-card">
      @include('admin.user.profiletab')

      <div class="tab-content" id="myTabContent">
        @php
        $currency = defaultCurr();
        @endphp
        <div class="mt-3 mx-4">
            @include('includes.admin.form-success')
            @include('includes.admin.form-error')
        </div>
        <div class="tab-pane fade show p-3 active" id="accounts" role="tabpanel" aria-labelledby="accounts-tab">
            @php
                $userType = explode(',', $data->user_type);
                @$supervisor = DB::table('customer_types')->where('type_name', 'Supervisors')->first()->id;
                @$merchant = DB::table('customer_types')->where('type_name', 'Merchants')->first()->id;
                $accounttype = array('0'=>'All', '1'=>'Current', '2'=>'Card', '3'=>'Deposit', '4'=>'Loan', '5'=>'Escrow', '8'=>'Crypto');
                if(in_array($supervisor, $userType)) {
                    $accounttype['6'] = 'Supervisor';
                }
                elseif (DB::table('managers')->where('manager_id', $data->id)->first()) {
                    $accounttype['10'] = 'Manager';
                }

                if(in_array($merchant, $userType)) {
                    $accounttype['7'] = 'Merchant';
                }
                $curlist = DB::table('currencies')->whereType('1')->get();
                $cryptolist = DB::table('currencies')->whereType('2')->get();
            @endphp

          <div class="card-body">
            <div class="row mb-3">
                <div class="mr-3">
                    <h4 class="mt-1">@lang('Select Type:')</h4>
                </div>
                <div class="col-3 mr-3">
                    <select class="col-lg select mb-3 input-field" id="wallet_type">
                        <option value="0"> {{'All'}} </option>
                        <option value="1"> {{'Current'}} </option>
                        <option value="2"> {{'Card'}} </option>
                        <option value="3"> {{'Deposit'}} </option>
                        <option value="4"> {{'Loan'}} </option>
                        <option value="5"> {{'Escrow'}} </option>
                        <option value="8"> {{'Crypto'}} </option>
                        @if (isset($accounttype['6']))
                        <option value="6"> {{'Supervisor'}} </option>
                        @endif
                        @if (isset($accounttype['7']))
                        <option value="7"> {{'Merchant'}} </option>
                        @endif
                        @if (isset($accounttype['10']))
                        <option value="10"> {{'Manager'}} </option>
                        @endif
                    </select>
                </div>

            </div>

            <div class="row mb-3" id="walletlist">
            @foreach ($accounttype as $i=>$value)
              @foreach (DB::table('currencies')->whereType('1')->get() as $dcurr)
              @php
                  $wallet = DB::table('wallets')->where('user_id', $data->id)->where('wallet_type',$i)->where('currency_id',$dcurr->id)->first();
              @endphp
              @if ($i > '0' && $i != '8')
                @if ($wallet != null)
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card h-100" >
                        <div class="card-body">
                            <div class="row align-items-center">
                            <div class="col">
                                <div class="d-flex mb-1 mr-1 align-items-start">
                                    <div class='font-weight-bold text-gray-900 w-75 mr-auto'>{{$value}}<br/>{{$wallet->wallet_no}}</div>
                                    <div class='font-weight-bold text-gray-900 w-25 text-right'>
                                        <div class="dropdown">
                                            <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown" style="padding: 6px 11px 1px 7px; border-radius: 50%;">
                                                <span class="caret"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="javascript:;" onclick="getDetails({{$wallet->id}})">{{ __('Fee') }}</a>
                                                <a class="dropdown-item" href="javascript:;" onclick="Deposit({{$wallet->id}})">{{ __('Deposit') }}</a>
                                                <a class="dropdown-item" href="{{route('admin-wallet-between', ['user_id' => $data->id, 'wallet_id'=>$wallet->id])}}">{{ __('Payment between accounts') }}</a>
                                                <a class="dropdown-item" href="{{route('admin-wallet-internal', ['user_id' => $data->id, 'wallet_id'=>$wallet->id])}}">{{ __('Internal Payment') }}</a>
                                                <a class="dropdown-item" href="{{route('admin-wallet-external', ['user_id' => $data->id, 'wallet_id'=>$wallet->id])}}">{{ __('External Payment') }}</a>
                                                <a class="dropdown-item" href="{{route('admin-wallet-transactions', ['user_id' => $data->id, 'wallet_id'=>$wallet->id])}}">{{ __('Transaction View') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1"> {{$dcurr->curr_name}}</div>
                                <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">{{amount($wallet->balance,$dcurr->type,2)}} {{$dcurr->code}} ({{$dcurr->symbol}}) </div>
                            </div>
                            </div>
                        </div>
                        </div>
                    </div>
                    @else
                    <a href="javascript:;" data-toggle="modal" data-target="#statusModal" data-href="{{ route('admin-user-wallet-create',['id' => $data->id, 'wallet_type' => $i, 'currency_id' =>$dcurr->id ]) }}" class = "col-xl-3 col-md-6 mb-4" style="text-decoration: none;">
                        <div class="card h-100" style="background-color: #a2b2c5;">
                        <div class="card-body">
                            <div class="row align-items-center">
                            <div class="col">
                                <div class="row mb-1 mr-1">
                                    <div class='col font-weight-bold text-gray-900'>{{$value}}<br/><br/></div>
                                    <div class='col font-weight-bold text-gray-900'></div>
                                </div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1"> {{$dcurr->curr_name}}</div>
                                <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">{{'xxx'}} {{$dcurr->code}} ({{$dcurr->symbol}}) </div>
                            </div>
                            </div>
                        </div>
                    </div>
                    </a>
                @endif
              @endif

              @endforeach
            @endforeach
            @foreach (DB::table('currencies')->whereType('2')->get() as $dcurr)
              @php
                  $wallet = DB::table('wallets')->where('user_id', $data->id)->where('wallet_type',8)->where('currency_id',$dcurr->id)->first();
              @endphp
                @if ($wallet != null)
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card h-100" >
                        <div class="card-body">
                            <div class="row align-items-center">
                            <div class="col">
                                <div class="d-flex mb-1 mr-1 align-items-start">
                                    <div class='font-weight-bold text-gray-900 w-75 mr-auto'>{{'Crypto'}} {{$wallet->wallet_no}}</div>
                                    <div class='col font-weight-bold text-gray-900 w-25 text-right'>
                                        <div class="dropdown">
                                            <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown" style="padding: 6px 11px 1px 7px; border-radius: 50%;">
                                                <span class="caret"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="javascript:;" onclick="getDetails({{$wallet->id}})">{{ __('Fee') }}</a>
                                                <a class="dropdown-item" href="javascript:;" onclick="Crypto_Deposit({{$wallet->id}})">{{ __('Deposit') }}</a>
                                                <a class="dropdown-item" href="{{route('admin-wallet-internal', ['user_id' => $data->id, 'wallet_id'=>$wallet->id])}}">{{ __('Internal Payment') }}</a>
                                                <a class="dropdown-item" href="{{route('admin-wallet-transactions', ['user_id' => $data->id, 'wallet_id'=>$wallet->id])}}">{{ __('Transaction View') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1"> {{$dcurr->curr_name}}</div>
                                @if ($dcurr->type == 2)
                                    @if ($dcurr->code == 'BTC')
                                        <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">{{ RPC_BTC_Balance('getbalance',$wallet->keyword) == 'error' ? amount($wallet->balance,$dcurr->type,2) : RPC_BTC_Balance('getbalance', $wallet->keyword)}}  {{$dcurr->code}}</div>
                                    @elseif($dcurr->code == 'ETH')
                                        <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">{{ hexdec(RPC_ETH('eth_getBalance',[$wallet->wallet_no, "latest"]))/pow(10,18) == 'error' ? amount($wallet->balance,$dcurr->type,2) : hexdec(RPC_ETH('eth_getBalance',[$wallet->wallet_no, "latest"]))/pow(10,18) }}  {{$dcurr->code}}</div>
                                    @else
                                        @php
                                            $geth = new App\Classes\EthereumRpcService();
                                            $tokenContract = $dcurr->address;
                                            $balance = $geth->getTokenBalance($tokenContract, $wallet->wallet_no);
                                        @endphp
                                        <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">{{ $balance != 'error' ?? amount($wallet->balance,$dcurr->type,2) }}  {{$dcurr->code}}</div>
                                    @endif
                                @else
                                    <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">{{amount($wallet->balance,$dcurr->type,2)}} {{$dcurr->code}} ({{$dcurr->symbol}}) </div>
                                @endif
                            </div>
                            </div>
                        </div>
                        </div>
                    </div>
                    @else
                    <a href="javascript:;" data-toggle="modal" data-target="#statusModal" data-href="{{ route('admin-user-wallet-create',['id' => $data->id, 'wallet_type' => 8, 'currency_id' =>$dcurr->id ]) }}" class = "col-xl-3 col-md-6 mb-4" style="text-decoration: none;">
                        <div class="card h-100" style="background-color: #a2b2c5;">
                        <div class="card-body">
                            <div class="row align-items-center">
                            <div class="col">
                                <div class="row mb-1 mr-1">
                                    <div class='col font-weight-bold text-gray-900'>{{'Crypto'}}<br/><br/></div>
                                    <div class='col font-weight-bold text-gray-900'></div>
                                </div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1"> {{$dcurr->curr_name}}</div>
                                <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">{{'xxx'}} {{$dcurr->code}} ({{$dcurr->symbol}}) </div>
                            </div>
                            </div>
                        </div>
                    </div>
                    </a>
              @endif

              @endforeach
            </div>
            <div class="card-header">
              <h5>@lang('Transaction Type')</h5>
              <h6>@lang('Deposit, Internal, Withdrawal')</h6>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
<div class="modal fade status-modal" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">{{ __("Add New Wallet") }}</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div class="modal-body">
				<p class="text-center">{{ __("Now You are adding new Wallet.") }}</p>
				<p class="text-center">{{ __("Do you want to proceed?") }}</p>
			</div>

			<div class="modal-footer">
				<a href="javascript:;" class="btn btn-secondary" data-dismiss="modal">{{ __("Cancel") }}</a>
				<a href="javascript:;" class="btn btn-success btn-ok" id="addpayment" >{{ __("Add") }}</a>
			</div>
		</div>
	</div>
</div>

<div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-status bg-primary"></div>
        <div class="modal-body py-4">
        <div class="text-center"><i  class="fas fa-info-circle fa-3x text-primary mb-2"></i></div>
        <h3 class="text-center">@lang('Fee Details')</h3>
        <ul class="list-group mt-2">

        </ul>
        </div>
    </div>
    </div>
  </div>

  <div class="modal modal-blur fade" id="modal-success-2" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-status bg-primary"></div>
        <div class="modal-body py-4">
        <div class="text-center"><i  class="fas fa-info-circle fa-3x text-primary mb-2"></i></div>
        <h3 class="text-center">@lang('Deposit Details')</h3>
        <form action="{{route('admin-user-accounts-deposit')}}" method="post" class="m-3">
            @csrf
            <input type="hidden" class="form-control" id="wallet_id" name="wallet_id"  value="" >

            <ul class="list-group mt-2"></ul>
            </form>
        </div>
    </div>
    </div>
  </div>
<!--Row-->
@endsection
@section('scripts')
<script type="text/javascript">

    function getDetails (id)
    {
            var url = "{{url('admin/user/accounts/fee')}}"+'/'+id
            $.get(url,function (res) {
                if(res == 'empty'){
                $('.list-group').html("<p>@lang('No details found!')</p>")
                }else{
                $('.list-group').html(res)
                }
            });

            $('#modal-success').modal('show')
    }


    function Deposit(id) {
        console.log(id);
        $('#wallet_id').val(id);
        var url = "{{url('admin/user/accounts/deposit/form')}}"
            $.get(url,function (res) {
                if(res == 'empty'){
                $('.list-group').html("<p>@lang('No details found!')</p>")
                }else{
                $('.list-group').html(res)
                }
            });
        $('#modal-success-2').modal('show')
    }

    function Crypto_Deposit(id) {
        console.log(id);
        $('#wallet_id').val(id);
        var url = "{{url('admin/user/accounts/crypto/deposit/form/')}}" + '/' + id
        console.log(url)
            $.get(url,function (res) {
                if(res == 'empty'){
                $('.list-group').html("<p>@lang('No details found!')</p>")
                }else{
                $('.list-group').html(res)
                }
            });
        $('#modal-success-2').modal('show')
    }

    $('#addpayment').on('click', function() {
        window.location.reload();
    });

    let accounttype = {'0':'All', '1':'Current', '2':'Card', '3':'Deposit', '4':'Loan', '5':'Escrow', '6':'Supervisor', '7':'Merchant', '8':'Crypto', '10':'Manager'};
    let _orignhtml = $('div#walletlist').html();
    $('#wallet_type').on('change', function() {
        let wallet_type = $("#wallet_type").val();
        let data ="{{$data->id }}";
        let curlist = wallet_type == '8' ? '{{$cryptolist}}' : '{{$curlist}}';
        const obj = curlist.replace(/&quot;/g, '"');
        let _divhtml = "" ;
        $.each(JSON.parse(obj), function(key, value) {


        var url = `${mainurl}/admin/user/${data}/accounts/wallets/${wallet_type}/${value.id}`;
        $.get(url, function(res) {
            if (res.length >=1){

                $.each(res, function(i, item) {
                    _divhtml += '<div class="col-xl-3 col-md-6 mb-4"> \
                            <div class="card h-100"> \
                                <div class="card-body"> \
                                    <div class="row align-items-center"> \
                                        <div class="col"> \
                                            <div class="d-flex mb-1 mr-1 align-items-start"> \
                                                <div class="font-weight-bold text-gray-900 w-75 mr-auto">' + accounttype[`${item.wallet_type}`] + '<br/>' + item.wallet_no +'</div>\
                                                <div class="font-weight-bold text-gray-900 w-25 text-right">\
                                                    <div class="dropdown">\
                                                        <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown" style="padding: 6px 11px 1px 7px; border-radius: 50%;">\
                                                            <span class="caret"></span>\
                                                        </button>\
                                                        <div class="dropdown-menu dropdown-menu-right">\
                                                            <a class="dropdown-item" href="javascript:;" onclick="getDetails('+ item.id +')">{{ __("Fee") }}</a>\
                                                            <a class="dropdown-item" href="javascript:;" onclick=' +( wallet_type == '8' ? '"Crypto_Deposit(' : '"Deposit(')+ item.id +')">{{ __("Deposit") }}</a>'
                                                            + ( wallet_type == '8' ? '':'<a class="dropdown-item" href="{{url("admin/wallet/$data->id")}}/'+ item.id +'/between">{{ __("Payment between accounts") }}</a>') +
                                                            '<a class="dropdown-item" href="{{url("admin/wallet/$data->id")}}/'+ item.id +'/internal">{{ __("Internal Payment") }}</a>' +
                                                            (wallet_type == '8' ? '':'<a class="dropdown-item" href="{{url("admin/wallet/$data->id")}}/'+ item.id +'/external">{{ __("External Payment") }}</a>') +
                                                            '<a class="dropdown-item" href="{{url("admin/wallet/$data->id")}}/'+ item.id +'/transactions">{{ __("Transaction View") }}</a>\
                                                        </div>\
                                                    </div>\
                                                </div>\
                                            </div> \
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">' + item.currency.curr_name + '</div>\
                                            <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">' + parseFloat(item.balance).toFixed(2) + " " + item.currency.code + " " + item.currency.symbol + '</div> \
                                        </div> \
                                    </div> \
                                </div> \
                            </div>\
                        </div>'
                        });
            }
            else {
                var createurl = `${mainurl}/admin/user/${data}/accounts/wallet/create/${wallet_type}/${value.id}`;


                _divhtml += '<a href="javascript:;" data-toggle="modal" data-target="#statusModal" data-href='+ createurl + ' class = "col-xl-3 col-md-6 mb-4" style="text-decoration: none;"> \
                        <div class="card h-100" style="background-color: #a2b2c5;"> \
                            <div class="card-body"> \
                                <div class="row align-items-center"> \
                                    <div class="col mr-2"> \
                                        <div class="row mb-1 mr-1"> \
                                            <div class="col font-weight-bold text-gray-900">' + accounttype[`${wallet_type}`] + '<br/><br/></div>\
                                            <div class="col font-weight-bold text-gray-900">' + "" +'</div> \
                                        </div> \
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">' + value.curr_name + '</div> \
                                        <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">'  + value.code + " " + value.symbol + '</div> \
                                    </div> \
                                </div> \
                            </div> \
                        </div> \
                    </a>'

            }
            if(wallet_type==0){
                $('div#walletlist').html(_orignhtml);
            }
            else{
                $('div#walletlist').html(_divhtml);
            }
            })
        })
    })
</script>
@endsection
