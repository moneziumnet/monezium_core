@extends('layouts.user')

@push('css')

@endpush

@section('title', __('Home'))

@section('contents')
<div class="container-fluid">

  <div class="page-header d-print-none">
  <div class="row align-items-center">
    <div class="col mb-2">
      <div class="d-flex">
        <span class="balance-body">{{ showprice($userBalance,$currency) }}</span>
        <span class="change-language">
          <select name="currency" class="currency selectors nice language-bar">
            @foreach(DB::table('currencies')->where('type', 1)->get() as $value)
            <option value="{{route('front.currency',$value->id)}}" {{ Session::has('currency') ? ( Session::get('currency') == $value->id ? 'selected' : '' ) : (DB::table('currencies')->where('is_default','=',1)->first()->id == $value->id ? 'selected' : '') }}>
              {{$value->code}}
            </option>
            @endforeach
          </select>
        </span>
      </div>
      <div class="d-flex">
        <span class="balance-footer">{{ __('Total balance from all accounts') }} <span class="text-primary ms-2" style="font-weight:700;"></span></span>
      </div>
    </div>
    <div class="col-auto ms-auto d-print-none">
      <div class="btn-list">
        <a href="#" data-bs-toggle="modal" data-bs-target="#modal-wallet-create" class="tx-color">
          <i class="fas fa-plus me-1"></i> {{__('Open a wallet')}}
        </a>
      </div>
    </div>
  </div>
  </div>
</div>
<div class="page-body">
  <div class="container-fluid d-flex">
    @if (auth()->user()->kyc_status == 1)
      @if ($kyc_request_id != 0)
        @if ($kyc_request_status != 3)

          @if ($kyc_request_status == 2)
            <div class="row mb-3">
              <div class="col-md-12">
                <div class="card">
                  <div class="card-body">
                    <div class="form-group w-100 d-flex flex-wrap align-items-center justify-content-evenly justify-content-sm-between">
                        <h3 class="my-1 text-center text-sm-start">{{ __('You are rejected . Please submit for additional kyc verification again.') }}</h3>
                        <div class="my-1">
                            <a href="{{ route('user.aml.kyc') }}" class="btn btn-warning">@lang('Submit')</a>
                        </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          @elseif ($kyc_request_status == 0)
            <div class="row mb-3">
              <div class="col-md-12">
                <div class="card">
                  <div class="card-body">
                    <div class="form-group w-100 d-flex flex-wrap align-items-center justify-content-evenly justify-content-sm-between">
                        <h3 class="my-1 text-center text-sm-start">{{ __('You have a information to submit for additional kyc verification.') }}</h3>
                        <div class="my-1">
                            <a href="{{ route('user.aml.kyc') }}" class="btn btn-warning">@lang('Submit')</a>
                        </div>
                      </div>
                </div>
              </div>
            </div>
          </div>
          @endif
        @endif
      @endif

    @else
    <div class="row mb-3">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            <div class="form-group w-100 d-flex flex-wrap align-items-center justify-content-evenly justify-content-sm-between">
              @if (auth()->user()->kyc_status != 3)
                @if (auth()->user()->kyc_token)
                    @if (auth()->user()->kyc_status == 2)
                        <h3 class="my-1 text-center text-sm-start">{{ __('You are rejected . Please submit for kyc verification again.') }}</h3>
                        <div class="my-1">
                            <a href="{{ route('user.kyc.form') }}" class="btn btn-warning">@lang('Submit')</a>
                        </div>
                    @else
                        <h3 class="my-1 text-center text-sm-start">{{ __('You have already submitted kyc for auto verification. Please check verificiation status') }}</h3>
                        <div class="my-1">
                            <a href="{{ route('user.kyc.form') }}" class="btn btn-warning">@lang('Check')</a>
                        </div>
                    @endif
                @else
                    <h3 class="my-1 text-center text-sm-start">{{ __('You have a information to submit for kyc verification.') }}</h3>
                    <div class="my-1">
                        <a href="{{ route('user.kyc.form') }}" class="btn btn-warning">@lang('Submit')</a>
                    </div>
                @endif
              @elseif(auth()->user()->kyc_status == 3)
              <h3 class="my-1 text-center text-sm-start">{{ __('You have submitted kyc for verification.') }}</h3>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
    @endif
    <div class="col-md-6 justify-content-start pe-3">
      <div class="row">
        @if (isEnabledUserModule('Incoming'))
          <div class="col-md-6 mb-3">
            <div class="card h-100 card--info-item">
              <div class="card-body">
                <div class="h1 m-0">{{$currency->symbol}}{{ round($depositAmount, 2) }}</div>
                <div class="text-muted">@lang('Total Deposit')</div>
              </div>
            </div>
          </div>
        @endif
        @if (isEnabledUserModule('External Payments'))
        <div class="col-md-6 mb-3">
          <div class="card h-100 card--info-item">
            <div class="card-body p-3 p-md-4">
              <div class="h2 m-0">{{$currency->symbol}}{{ round($withdrawAmount, 2) }}</div>
              <div class="text-muted">@lang('Total Withdraw')</div>
            </div>
          </div>
        </div>
        @endif
      </div>
      <div class="card mb-3">
        <div class="card-body">
          <div class="d-flex justify-content-center">
            <h3 class="card-title">{{ __('Incoming and Withdraw') }}</h3>
          </div>
          <div id="chart_finance_monthly" class="chart-lg"></div>
        </div>
      </div>
      @if($website_theme == 1)
      <div class="container-fluid">
        <div class="page-header d-print-none">
          <div class="row align-items-center">
            <div class="col">
              <h1>
                {{__('Wallet list')}}
              </h1>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                  <a href="#" data-bs-toggle="modal" data-bs-target="#modal-wallet-create" class="tx-color">
                      <i class="fas fa-plus me-1"></i> {{__('Create Wallet')}}
                  </a>
                </div>
              </div>
          </div>
        </div>
      </div>
      @php
      $userType = explode(',', auth()->user()->user_type);
      @$supervisor = DB::table('customer_types')->where('type_name', 'Supervisors')->first()->id;
      @$merchant = DB::table('customer_types')->where('type_name', 'Merchants')->first()->id;
      $wallet_type = array('0'=>'All', '1'=>'Current', '2'=>'Card', '3'=>'Deposit', '4'=>'Loan', '5'=>'Escrow');
      if(in_array($supervisor, $userType)) {
          $wallet_type['6'] = 'Supervisor';
      }
      elseif (DB::table('managers')->where('manager_id', auth()->id())->first()) {
          $wallet_type['10'] = 'Manager';
      }
      if(in_array($merchant, $userType)) {
          $wallet_type['7'] = 'Merchant';
      }
      @endphp
      <div class="row justify-content mt-3" style="max-height: 368px;">
        @if (count($wallets) != 0)
            @foreach ($wallets as $item)
                @if (isset($wallet_type[$item->wallet_type]))
                    <div class="col-sm-6 col-md-6 mb-3">
                        <div class="card h-100 card--info-item">
                        <div class="text-end icon rounded-circle">
                            <i class="fas ">
                                {{$item->currency->symbol}}
                            </i>
                        </div>
                        <div class="card-body">
                            <div class="h3 m-0 text-uppercase"> {{$wallet_type[$item->wallet_type]}}</div>
                            <div class="h4 m-0 text-uppercase"> {{ $item->wallet_no }}</div>
                            <div class="text-muted">{{ amount($item->balance,$item->currency->type,2) }}  {{$item->currency->code}}</div>
                        </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @else
            <p class="text-center">@lang('NO Wallet FOUND')</p>
        @endif
      </div>
      <hr>
      @endif
      <div class="modal modal-blur fade" id="modal-wallet-create" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-title" style="border-radius: 10px 10px 0 0">
              <div class="ms-3">
                <p>{{('Wallet Create')}}</p>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
              <form action="{{route('user.wallet.create')}}" method="POST" enctype="multipart/form-data">
                  @csrf
                  <div class="modal-body">
                      <div class="form-group">
                          <label class="form-label required">{{__('Currency')}}</label>
                          <select name="currency_id" class="form-select" required>
                              <option value="">{{ __('Select Currency') }}</option>
                              @foreach ($currencies as $key => $value)
                                      <option value="{{$value->id}}">{{$value->code}}</option>
                              @endforeach
                          </select>
                      </div>
                      <input type="hidden" name="user_id" value="{{$user->id}}">
                  </div>

                  <div class="modal-footer">
                      <button  id="submit-btn" class="btn btn-primary">{{ __('Create') }}</button>
                  </div>
              </form>
          </div>
        </div>
      </div>

      @if (isEnabledUserModule('Crypto'))
      <div class="container-fluid">
          <div class="page-header d-print-none">
            <div class="row align-items-center">
              <div class="col">
                <h1>
                  {{__('Crypto Wallet list')}}
                </h1>
              </div>
              <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                  <a href="#" data-bs-toggle="modal" data-bs-target="#modal-crypto-wallet-create" class="tx-color">
                    <i class="fas fa-plus me-1"></i> {{__('Create Crypto Wallet')}}
                  </a>
                </div>
              </div>
            </div>
          </div>
      </div>
      <div class="modal modal-blur fade" id="modal-crypto-wallet-create" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-title" style="border-radius: 10px 10px 0 0">
            <div class="ms-3">
              <p>{{('Crypto Wallet Create')}}</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
            <form action="{{route('user.wallet.crypto.create')}}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label required">{{__('Crypto Currency')}}</label>
                        <select name="crypto_currency_id" class="form-select" required>
                            <option value="">{{ __('Select Currency') }}</option>
                            @foreach ($crypto_currencies as $key => $value)
                              <option value="{{$value->id}}">{{$value->code}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <input type="hidden" name="user_id" value="{{$user->id}}">
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
                </div>
            </form>
        </div>
        </div>
      </div>
      <div class="row justify-content mb-3" style="max-height: 368px;overflow-y: scroll;">
          @if (count($cryptowallets) != 0)
              @foreach ($cryptowallets as $item)
                      <div class="col-sm-6 col-md-6 mb-3">
                          <div class="card h-100 card--info-item">
                          <div class="text-end icon rounded-circle">
                              <i class="fas ">
                                  {{$item->currency->symbol}}
                              </i>
                          </div>
                          <div class="card-body">
                              <div class="h3 m-0 text-uppercase">{{ amount(Crypto_Balance($item->user_id, $item->currency_id), 2)}}  {{$item->currency->code}}</div>
                              <div class="h4 m-0"> {{str_dis($item->wallet_no)}}</div>
                              <div class="text-muted"> ({{ amount(Crypto_Balance_Fiat($item->user_id, $item->currency_id), 1)}}  {{$currency->code}})</div>

                          </div>
                          </div>
                      </div>
              @endforeach
          @else
              <p class="text-center">@lang('NO Crypto Wallet FOUND')</p>
          @endif

      </div>
      @endif
      <hr>

      <div class="container-fluid">
          <div class="page-header d-print-none">
            <div class="row align-items-center">
              <div class="col">
                <h1>
                  {{__('Bank Account list')}}
                </h1>
              </div>
              @if ($user->kyc_status == 1)
              <div class="col-auto ms-auto d-print-none">
                  <!--<div class="btn-list">

                    <a  data-bs-toggle="modal" data-bs-target="#modal-bank-create" class="btn btn-primary d-sm-inline-block">
                        <i class="fas fa-plus me-1"></i> {{__('Create Bank Account')}}
                    </a>
                  </div>-->
                </div>
              @endif
            </div>
          </div>
      </div>

      <div class="modal modal-blur fade" id="modal-bank-create" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-title" style="border-radius: 10px 10px 0 0">
              <div class="ms-3">
                <p>{{('Bank Account Create')}}</p>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
              <form action="" class="bankaccount" method="POST" enctype="multipart/form-data">
                  @csrf
                  <div class="modal-body">
                      <div class="form-group">
                          <label class="form-label required">{{ __('SubInstitions Bank') }}</label>
                              <select class="form-control" name="subbank" id="subbank" required>
                              <option value="">{{ __('Select SubInstitions Bank') }}</option>
                              @foreach ($subbank as $bank )
                                @if($bank->hasGateway())
                                  <option value="{{$bank->id}}">{{ __($bank->name) }}</option>
                                @endif
                              @endforeach
                              </select>
                          </div>
                      <div class="form-group mt-1">
                          <label class="form-label required">{{__('Currency')}}</label>
                          <select name="currency" class="form-select" required>
                              <option value="">{{ __('Select Currency') }}</option>
                              @foreach ($currencies as $key => $value)
                                      <option value="{{$value->id}}">{{$value->code}}</option>
                              @endforeach
                          </select>
                      </div>
                      <input type="hidden" name="user" value="{{$user->id}}">
                  </div>

                  <div class="modal-footer">
                      <button  id="submit-btn" class="btn btn-primary">{{ __('Create') }}</button>
                  </div>
              </form>
          </div>
          </div>
      </div>

      <div class="row justify-content mt-3" style="max-height: 368px;overflow-y: scroll;">
          @if (count($bankaccountlist) != 0)
              @foreach ($bankaccountlist as $item)
                  <div class="col-sm-6 col-md-12 mb-3">
                      <div class="card h-100 card--info-item">
                      <div class="text-end icon rounded-circle">
                          <i class="fas ">
                          {{$item->currency->symbol}}
                          </i>
                      </div>
                      <div class="card-body">
                          <div class="h3 m-0 text-uppercase"> {{$item->iban}}</div>
                          <div class="h4 m-0 text-uppercase"> SWIFT: {{ $item->swift }}</div>
                          <div class="row">
                              <div class="col text-muted">{{ __($item->subbank->name) }} </div>
                              <div class="col h4 m-0 text-uppercase text-center">{{ __($item->currency->code) }} </div>
                          </div>
                      </div>
                      </div>
                  </div>
              @endforeach
          @else
              <p class="text-center">@lang('NO Bank Account FOUND')</p>
          @endif

      </div>
    </div>

    <div class="col-md-6">
      <div class="row justify-content-start">
        {{-- <div class="col-sm-6 col-md-4 mb-3">
          <div class="card h-100 card--info-item">
            <div class="text-end icon">
              <i class="fas fa-exchange-alt"></i>
            </div>
            <div class="card-body">
              <div class="h2 m-0">{{ count($user->transactions) }}</div>
              <div class="text-muted">@lang('Transactions')</div>
            </div>
          </div>
        </div> --}}
        @if (isEnabledUserModule('Loan'))
        <div class="col-md-4 mb-3">
          <div class="card card--info-item">
            <div class="card-body">
              <div class="h1 m-0">{{ count($user->loans) }}</div>
              <div class="text-muted">@lang('Loan')</div>
            </div>
          </div>
        </div>
        @endif
  
        @if (isEnabledUserModule('Investments'))
        <div class="col-md-4 mb-3">
          <div class="card card--info-item">
            <div class="card-body">
              <div class="h1 m-0">{{ count($user->dps) }}</div>
              <div class="text-muted">@lang('DPS')</div>
            </div>
          </div>
        </div>
  
        <div class="col-md-4 mb-3">
          <div class="card card--info-item">
            <div class="card-body">
              <div class="h1 m-0">{{ count($user->fdr) }}</div>
              <div class="text-muted">@lang('FDR')</div>
            </div>
          </div>
        </div>
        @endif
      </div>

      <div class="row row-deck cards mb-3">
        <div class="col-lg-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">@lang('Latest Transaction')</h3>
              </div>
    
              @if (count($transactions) == 0)
              <p class="text-center p-2">@lang('NO DATA FOUND')</p>
              @else
              <div class="table-responsive">
                <table class="table card-table table-vcenter table-mobile-md text-nowrap datatable">
                  {{-- <thead>
                    <tr>
                      <th>@lang('Remark')</th>
                      <th>@lang('Amount')</th>
                      <th class="text-end"  style="padding-right: 28px;">@lang('Details')</th>
                    </tr>
                  </thead> --}}
                  <tbody>
                    @foreach ($transactions as $key=>$data)
                    <tr>
                      <td data-label="@lang('Remark')">
                        <div class="card-content">
                          <a href="#" class="tx-color details" data-data="{{$data}}">{{ucwords(str_replace('_',' ',$data->remark))}}</a>
                        </div>
                        <div class="card-datetime">
                          <span>{{dateFormat($data->created_at,'d-M-Y h:m:s')}}</span>
                        </div>
                        
                      </td>
                      <td data-label="@lang('Amount')">
                        <div style="text-align:right;">
                          <a href="#" class="card-content details" data-data="{{$data}}">{{$data->type}} {{amount($data->amount+$data->charge,$data->currency->type,2)}} {{$data->currency->code}}</a>
                        </div>
                    </td>
                    {{-- <td data-label="@lang('Details')" class="text-end">
                      <a href="#" class="tx-color details" data-data="{{$data}}">@lang('Details')</a>
                    </td> --}}
                   </tr>
                    @endforeach
    
                  </tbody>
                </table>
              </div>
              @endif
    
            </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-body">
              <div class="form-group">
                <p>{{ __('Your Referral Link') }}</p>
                <div class="input-group input--group">
                  <input type="text" name="key" value="{{ url('/').'?reff='.$user->affilate_code}}" class="form-control" id="cronjobURL" readonly>
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

<div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-title">
        <div class="ms-3">
          <p>@lang('Transaction Details')</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <hr class="mt-3 mb-5">
      </div>
      <div class="modal-body text-center">
        <i class="fas fa-info-circle fa-3x text-primary mb-2"></i>
        <p class="trx_info_amount"></p>
        <p class="trx_info_date"></p>
        <span class="badge bg-success-lt">Paid</span>

        <ul class="list-group mt-2">

        </ul>
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
    toastr.options =
    {
      "closeButton" : true,
      "progressBar" : true
    }
    toastr.success("Copied.");
  }
</script>
<script>
      'use strict';

      $('.details').on('click',function () {
        var url = "{{url('user/transaction/details/')}}"+'/'+$(this).data('data').id;
        $.get(url,function (res) {
          if(res == 'empty'){
            $('.list-group').html("<p>@lang('No details found!')</p>");
          }else{
            var parser = new DOMParser();
            var node = parser.parseFromString(res, "text/html");
            $('.trx_info_amount').text(node.getElementById("trnx_amount").textContent + " to " + node.getElementById("Receiver").textContent);
            $('.trx_info_date').text("Completed " + node.getElementById("trnx_date").textContent);
            $('.list-group').html(res)
          }
          $('#modal-success').modal('show')
        })
      })

      $('#subbank').on('change', function() {
            $.post("{{ route('user.bankaccount.gateway') }}",{id:$('#subbank').val(),_token:'{{csrf_token()}}'},function (res) {
            console.log(res);

                if(res.keyword == 'openpayd')
                    {
                        $('.bankaccount').prop('action',"{{ route('user.bankaccount.openpayd.store') }}");
                    }
                if(res.keyword == 'railsbank')
                {
                    $('.bankaccount').prop('action',"{{ route('user.bankaccount.railsbank.store') }}");
                }
                if(res.keyword == 'clearjunction')
                {
                    $('.bankaccount').prop('action',"{{ route('user.clearjunction.api.ibancreate') }}");
                }
                if(res.keyword == 'swan')
                {
                    $('.bankaccount').prop('action',"{{ route('user.bankaccount.swan.store') }}");
                }
             });
        })
    </script>
<script>
    var array_months = '{{$array_months}}';
    array_months = array_months.split(',');
    var array_deposits = '{{$array_deposits}}';
    array_deposits = array_deposits.split(',');
    var array_withdraws = '{{$array_withdraws}}';
    array_withdraws = array_withdraws.split(',');
    document.addEventListener("DOMContentLoaded", function () {
        window.tabler_chart = window.tabler_chart || {};
        window.ApexCharts && (window.tabler_chart["chart_finance_monthly"] = new ApexCharts(document.getElementById('chart_finance_monthly'), {
            chart: {
                type: "line",
                fontFamily: 'inherit',
                height: 300,
                parentHeightOffset: 0,
                toolbar: {
                    show: false,
                },
                animations: {
                    enabled: false
                },
            },
            fill: {
                opacity: 1,
            },
            stroke: {
                width: 2,
                lineCap: "round",
                curve: "smooth",
            },
            series: [{
                name: "Incoming",
                data: array_deposits
            },{
                name: "Withdraw",
                data: array_withdraws
            }],
            tooltip: {
                theme: 'dark'
            },
            grid: {
                padding: {
                    top: -20,
                    right: 0,
                    left: -4,
                    bottom: -4
                },
                strokeDashArray: 4,
                xaxis: {
                    lines: {
                        show: true
                    }
                },
            },
            xaxis: {
                labels: {
                    datetimeFormatter: {
                        year: 'yyyy',
                        month: 'MMM \'yy',
                        day: 'dd MMM',
                        hour: 'HH:mm'
                    }
                    },
                tooltip: {
                    enabled: false
                },
                type: 'categroy',
            },
            yaxis: {
                labels: {
                    padding: 4
                },
            },
            labels: array_months,
            colors: [ "#1877f2", "#ea4c89"],
            legend: {
                show: true,
                position: 'bottom',
                offsetY: 12,
                markers: {
                    width: 10,
                    height: 10,
                    radius: 100,
                },
                itemMargin: {
                    horizontal: 8,
                    vertical: 8
                },
            },
        })).render();
    });
  </script>
@endpush
