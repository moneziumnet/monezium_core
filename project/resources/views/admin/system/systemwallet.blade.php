@extends('layouts.admin')
@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between py-3">
    <ol class="breadcrumb py-0 m-0">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.system.accounts') }}">{{ __('System Account List') }}</a></li>
    </ol>
  </div>
</div>


<div class="row mt-3">
  <div class="col-lg-12">
    <div class="card mt-1 tab-card">
        @include('admin.system.systemcryptotab')
      <div class="tab-content" id="myTabContent">
        <div class="mt-3 mx-4">
          @include('includes.admin.form-error')
          @include('includes.admin.form-success')
        </div>
        <div class="tab-pane fade show p-3 active" id="accounts" role="tabpanel" aria-labelledby="accounts-tab">
          <div class="card-body">
            <div class="card mb-4">
                <div class="row m-3" id="walletlist">
                @foreach (DB::table('currencies')->get() as $dcurr)
                @php
                    $wallet = get_wallet(0,$dcurr->id,9);
                @endphp
                    @if ($wallet != null)
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100" >
                            <div class="card-body">
                                <div class="row align-items-center">
                                <div class="col mr-2">
                                    <div class="row mb-1 mr-1">
                                        <div class='col font-weight-bold text-gray-900'>{{__('System')}}</div>
                                        <div class='col font-weight-bold text-gray-900'>{{$wallet->wallet_no}}</div>
                                    </div>
                                    <div class="text-xs font-weight-bold text-uppercase mb-1"> {{$dcurr->curr_name}}</div>
                                    <div class="row mb-1 mr-1">
                                    @if ($dcurr->type == 2)
                                        <div class="col h6 mb-0 mt-2 font-weight-bold text-gray-800">{{ amount(Crypto_Balance(0,$dcurr->id), 2)}}  {{$dcurr->code}} ({{amount(Crypto_Balance_Fiat(0,$dcurr->id), 1)}}  {{$currency->code}})</div>
                                    @else
                                        <div class="col h6 mb-0 mt-2 font-weight-bold text-gray-800">{{amount($wallet->balance,$dcurr->type,2)}} {{$dcurr->code}} ({{$dcurr->symbol}}) </div>
                                    @endif
                                        <div class='font-weight-bold text-gray-900 w-25 text-right'>
                                            <div class="dropdown">
                                                <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown" style="padding: 6px 11px 1px 7px; border-radius: 50%;">
                                                    <span class="caret"></span>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    @if ($dcurr->type == 2)
                                                        <a class="dropdown-item" href="javascript:;" onclick="Crypto_withdraw({{$dcurr->id}})" >{{ __('Withdraw Crypto') }}</a>
                                                    @else
                                                        <a class="dropdown-item" href="javascript:;" onclick="Withdraw({{$dcurr->id}})" >{{ __('Withdraw') }}</a>
                                                    @endif
                                                    <a class="dropdown-item" href="{{route('admin.system.account.transactions', $dcurr->id)}}">{{ __('Transaction View') }}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>
                            </div>
                        </div>
                        @else
                        <a href="javascript:;" data-toggle="modal" data-target="#statusModal" data-href="{{ route('admin.system.account.create',$dcurr->id) }}" class = "col-xl-3 col-md-6 mb-4" style="text-decoration: none;">
                            <div class="card h-100" style="background-color: #a2b2c5;">
                            <div class="card-body">
                                <div class="row align-items-center">
                                <div class="col mr-2">
                                    <div class="row mb-1 mr-1">
                                        <div class='col font-weight-bold text-gray-900'>{{__('System')}}</div>
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


<div class="modal modal-blur fade" id="modal-success-2" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-status bg-primary"></div>
        <div class="modal-body py-4">
        <div class="text-center"><i  class="fas fa-info-circle fa-3x text-primary mb-2"></i></div>
        <h3 class="text-center">@lang('Withdraw Details')</h3>
        <form action="{{route('admin.system.account.crypto.withdraw.store')}}" method="post" class="m-3">
            @csrf

            <ul class="list-group mt-2"></ul>
            </form>
        </div>
    </div>
    </div>
  </div>

  <div class="modal modal-blur fade" id="modal-success-1" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-status bg-primary"></div>
        <div class="modal-body py-4">
        <div class="text-center"><i  class="fas fa-info-circle fa-3x text-primary mb-2"></i></div>
        <h3 class="text-center">@lang('Withdraw Details')</h3>
        <form action="{{route('admin.system.account.withdraw.store')}}" method="post" class="m-3" id="iban-submit">
            @csrf
            <input type="hidden" name="currency_id" id="fiat_currency_id">
            <div class="form-group mt-3">
                <label class="form-label required">{{ __('Bank Account') }}</label>
                <select name="payment_gateway" id="subbank" class="form-control" required>
                    <option value="">{{ __('Select Bank Account') }}</option>
                    @foreach ($banks as $item)
                        <option value="{{ $item->name }}">
                            {{ $item->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mt-3">
                <label class="form-label required">{{__('Beneficiary User Name')}}</label>
                <input name="beneficiary_user_name" id="beneficiary_user_name" class="form-control" autocomplete="off" placeholder="{{__('John Doe')}}" type="text" pattern="[^À-ž()/><\][\\\-;&$@!|]+" required>
            </div>
            <div class="form-group mt-2">
                <label class="form-label required">{{__('Beneficiary Account/IBAN')}}</label>
                <input name="account_iban" id="account_iban" class="form-control iban-input" autocomplete="off" placeholder="{{__('Enter Account/IBAN')}}" type="text" value="{{ old('account_iban') }}" required>
                <small class="text-danger iban-validation"></small>
            </div>

            <div class="form-group mt-2">
                <label class="form-label required">{{__('Beneficiary Bank Name')}}</label>
                <input name="bank_name" id="bank_name" class="form-control" autocomplete="off" placeholder="{{__('Enter Bank Name')}}" type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" value="{{ old('bank_name') }}" required readonly>
            </div>

            <div class="form-group mt-2">
                <label class="form-label required">{{__('Beneficiary Bank Address')}}</label>
                <input name="bank_address" id="bank_address" class="form-control" autocomplete="off" placeholder="{{__('Enter Bank Address')}}" type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" value="{{ old('bank_address') }}" required readonly>
            </div>

            <div class="form-group mt-2">
                <label class="form-label required">{{__('Beneficiary SWIFT/BIC')}}</label>
                <input name="swift_bic" id="swift" class="form-control" autocomplete="off" placeholder="{{__('MEINATWW')}}" type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" value="{{ old('swift_bic') }}" required readonly>
            </div>

            <div class="form-group mb-3 mt-3">
                <label class="form-label required">{{__('Amount')}}</label>
                <input name="amount" id="amount" class="form-control" autocomplete="off" placeholder="{{__('0.0')}}" type="number" step="any" value="{{ old('amount') }}" required>
            </div>

            <div class="form-group mt-3">
                <label class="form-label required">{{ __('Description') }}</label>
                <textarea name="description" id="description" class="form-control" autocomplete="off"
                    placeholder="{{ __('Please input description') }}" type="text" required></textarea>
            </div>

            <div class="form-group">
                <button type="submit" id="submit-btn" class="btn btn-primary w-100 mt-2"  data-bs-dismiss="modal">{{ __('Withdraw') }}</button>
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

$('#addpayment').on('click', function() {
    window.location.reload();
});


function Crypto_withdraw(id) {
        var url = "{{url('admin/system-settings/crypto/withdraw/')}}" + '/' + id
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

function Withdraw(id) {
    $('#fiat_currency_id').val(id);
    $('#modal-success-1').modal('show');
}

</script>
@endsection
