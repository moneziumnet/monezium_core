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
        @include('includes.admin.form-success')
        <div class="tab-pane fade show p-3 active" id="accounts" role="tabpanel" aria-labelledby="accounts-tab">
            @php
                $userType = explode(',', $data->user_type);
                $supervisor = DB::table('customer_types')->where('type_name', 'Supervisors')->first()->id;
                if(in_array($supervisor, $userType)) {
                    $accounttype = ['All', 'Current', 'Card', 'Deposit', 'Loan', 'Escrow', 'Supervisor'];
                }
                else {
                    $accounttype = ['All', 'Current', 'Card', 'Deposit', 'Loan', 'Escrow'];
                }
                $curlist = DB::table('currencies')->get();
            @endphp

          <div class="card-body">
            <div class="row mb-3">
                <div class="mr-3">
                    <h4 class="mt-1">@lang('Select Type:')</h4>
                </div>
                <div class="col-3 mr-3">
                    <select class="col-lg select mb-3 input-field" id="wallet_type">
                        <option value="0"> {{'All'}} </option>
                        <option value="1"> {{'Curreny'}} </option>
                        <option value="2"> {{'Card'}} </option>
                        <option value="3"> {{'Deposit'}} </option>
                        <option value="4"> {{'Loan'}} </option>
                        <option value="5"> {{'Escrow'}} </option>
                        @if (sizeof($accounttype)>6)
                        <option value="6"> {{'Supervisor'}} </option>
                        @endif
                    </select>
                </div>

            </div>

            <div class="row mb-3" id="walletlist">
            @for ($i = 1; $i < sizeof($accounttype); $i++)
              @foreach (DB::table('currencies')->get() as $dcurr)
              @php
                  $wallet = DB::table('wallets')->where('user_id', $data->id)->where('wallet_type',$i)->where('currency_id',$dcurr->id)->first();
              @endphp
              @if ($wallet != null)
              <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100" >
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col mr-2">
                        <div class="row mb-1 mr-1">
                            <div class='col font-weight-bold text-gray-900'>{{$accounttype[$i]}}</div>
                            <div class='col font-weight-bold text-gray-900'>{{$wallet->wallet_no}}</div>
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
                      <div class="col mr-2">
                        <div class="row mb-1 mr-1">
                            <div class='col font-weight-bold text-gray-900'>{{$accounttype[$i]}}</div>
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
            @endfor
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
<!--Row-->
@endsection
@section('scripts')
<script type="text/javascript">
$('#addpayment').on('click', function() {
    window.location.reload();
});
    let accounttype = ['All', 'Current', 'Card', 'Deposit', 'Loan', 'Escrow', 'Supervisor'];
    let _orignhtml = $('div#walletlist').html();
    $('#wallet_type').on('change', function() {
        let wallet_type = $("#wallet_type").val();
        let data ="{{$data->id }}";
        let curlist = '{{$curlist}}';
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
                                    <div class="col mr-2"> \
                                        <div class="row mb-1 mr-1"> \
                                        <div class="col font-weight-bold text-gray-900">' + accounttype[item.wallet_type] + '</div>\
                                                <div class="col font-weight-bold text-gray-900">' + item.wallet_no +'</div> \
                                        </div> \
                            <div class="text-xs font-weight-bold text-uppercase mb-1">' + item.currency.curr_name + '</div> \
                            <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">' + parseFloat(item.balance).toFixed(2) + " " + item.currency.code + " " + item.currency.symbol + '</div> \
                            </div> \
                            </div> \
                            </div> \
                            </div> \
                            </div>'
                        });
            }
            else {
                var createurl = `${mainurl}/admin/user/${data}/accounts/wallet/create/${wallet_type}/${value.id}`;


                _divhtml += '              <a href="javascript:;" data-toggle="modal" data-target="#statusModal" data-href='+ createurl + ' class = "col-xl-3 col-md-6 mb-4" style="text-decoration: none;"> \
                        <div class="card h-100" style="background-color: #a2b2c5;"> \
                            <div class="card-body"> \
                                <div class="row align-items-center"> \
                                    <div class="col mr-2"> \
                                        <div class="row mb-1 mr-1"> \
                                        <div class="col font-weight-bold text-gray-900">' + accounttype[wallet_type] + '</div>\
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
