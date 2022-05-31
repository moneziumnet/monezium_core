@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">

  <div class="page-header d-print-none">

  </div>
</div>
<div class="page-body">
  <div class="container-xl">

    @if (auth()->user()->kyc_status != 1)
    <div class="row mb-3">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            <div class="form-group w-100 d-flex flex-wrap align-items-center justify-content-evenly justify-content-sm-between">
              <h3 class="my-1 text-center text-sm-start">{{ __('You have a information to submit for kyc verification.') }}</h3>
              <div class="my-1">
                <a href="{{ route('user.kyc.form') }}" class="btn btn-warning">@lang('Submit')</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endif

    <div class="row row-deck row-cards mb-2">

      <div class="col-sm-6 col-md-6">
        <div class="card mb-2">
          <div class="card-body p-3 p-md-4">
            <div class="balence--item">
              <div class="icon">
                <i class="fas fa-wallet"></i>
              </div>
              <div class="content">
                <div class="subheader">{{__('Account Number')}}</div>
                <div class="h1 mb-0 mt-2">{{ $user->account_number }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-md-6">
        <div class="card mb-2">
          <div class="card-body p-3 p-md-4">
            <div class="balence--item">
              <div class="icon">
                <i class="fas fa-money-bill-wave"></i>
              </div>
              <div class="content">
                <div class="subheader">{{__('Available Balance')}}</div>
                <div class="h1 mb-0 mt-2">{{ showprice($userBalance->total_amount,$currency) }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <div class="row justify-content-center">
      <div class="col-sm-6 col-md-4 mb-3">
        <div class="card h-100 card--info-item">
          <div class="text-end icon">
            <i class="fas fa-money-check"></i>
          </div>
          <div class="card-body">
            <div class="h1 m-0">{{ count($user->deposits) }}</div>
            <div class="text-muted">@lang('Deposits')</div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-md-4 mb-3">
        <div class="card h-100 card--info-item">
          <div class="text-end icon">
            <i class="fas fa-dollar-sign"></i>
          </div>
          <div class="card-body p-3 p-md-4">
            <div class="h1 m-0">{{ count($user->withdraws) }}</div>
            <div class="text-muted">@lang('Withdraws')</div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-md-4 mb-3">
        <div class="card h-100 card--info-item">
          <div class="text-end icon">
            <i class="fas fa-exchange-alt"></i>
          </div>
          <div class="card-body">
            <div class="h1 m-0">{{ count($user->transactions) }}</div>
            <div class="text-muted">@lang('Transactions')</div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-md-4 mb-3">
        <div class="card h-100 card--info-item">
          <div class="text-end icon">
            <i class="fas fa-hand-holding-usd"></i>
          </div>
          <div class="card-body">
            <div class="h1 m-0">{{ count($user->loans) }}</div>
            <div class="text-muted">@lang('Loan')</div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-md-4 mb-3">
        <div class="card h-100 card--info-item">
          <div class="text-end icon">
            <i class="fas fa-wallet"></i>
          </div>
          <div class="card-body">
            <div class="h1 m-0">{{ count($user->dps) }}</div>
            <div class="text-muted">@lang('DPS')</div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-md-4 mb-3">
        <div class="card h-100 card--info-item">
          <div class="text-end icon">
            <i class="far fa-credit-card"></i>
          </div>
          <div class="card-body">
            <div class="h1 m-0">{{ count($user->fdr) }}</div>
            <div class="text-muted">@lang('FDR')</div>
          </div>
        </div>
      </div>
    </div>

    <div class="row mb-3">
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

    <div class="row row-deck row-cards">
    <div class="col-lg-9">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">@lang('Recent Transaction')</h3>
          </div>

          @if (count($transactions) == 0)
          <p class="text-center p-2">@lang('NO DATA FOUND')</p>
          @else
          <div class="table-responsive">
            <table class="table card-table table-vcenter table-mobile-md text-nowrap datatable">
              <thead>
                <tr>
                  <th class="w-1">@lang('No').
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm text-dark icon-thick" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                      <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                      <polyline points="6 15 12 9 18 15" />
                    </svg>
                  </th>
                  <th>@lang('Date')</th>
                  <th>@lang('Transaction ID')</th>
                  <th>@lang('Remark')</th>
                  <th>@lang('Amount')</th>
                  <th>@lang('Details')</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @foreach ($transactions as $key=>$data)
                <tr>
                  <td data-label="@lang('No')">
                    <div>
                      <span class="text-muted">{{ $loop->iteration }}</span>
                    </div>
                  </td>
                  <td data-label="@lang('Date')">{{dateFormat($data->created_at,'d-M-Y')}}</td>
                  <td data-label="@lang('Transaction ID')">
                    {{__($data->trnx)}}
                  </td>
                  <td data-label="@lang('Remark')">
                    <span class="badge badge-dark">{{ucwords(str_replace('_',' ',$data->remark))}}</span>
                  </td>
                  <td data-label="@lang('Amount')">
									<span class="{{$data->type == '+' ? 'text-success':'text-danger'}}">{{$data->type}} {{amount($data->amount,$data->currency->type,2)}} {{$data->currency->code}}</span> 
								</td>
                <td data-label="@lang('Details')" class="text-end">
                        <button class="btn btn-primary btn-sm details" data-data="{{$data}}">@lang('Details')</button>
                    </td>
               </tr>
                @endforeach

              </tbody>
            </table>
          </div>
          @endif

        </div>
    </div>
    <div class="col-lg-3">
      <div class="card wallet__card">
        <div class="card-header">
          <h4>@lang('Your Wallets')</h4>
        </div>
        <div class="card-body card-body-scrollable card-body-scrollable-shadow">
          <div class="divide-y">
            @foreach ($wallets as $item)
            <div>
              <div class="row align-items-center">
                <div class="col-auto">
                  <span class="bg-blue text-white avatar">
                    {{$item->currency->symbol}}
                  </span>
                </div>
                <div class="col">
                  <div class="{{$item->currency->default == 1 ? 'font-weight-bold text-success' : 'font-weight-medium'}}">
                    {{amount($item->balance,$item->currency->type,2)}} {{$item->currency->code}}
                  </div>
                  <div class="text-muted">
                    {{$item->currency->curr_name}}
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
</div>

<div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered" role="document">
  <div class="modal-content">
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      <div class="modal-status bg-primary"></div>
      <div class="modal-body text-center py-4">
      <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
      <h3>@lang('Transaction Details')</h3>
      <p class="trx_details"></p>
      <ul class="list-group mt-2">
         
      </ul>
      </div>
      <div class="modal-footer">
      <div class="w-100">
          <div class="row">
          <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">
              @lang('Close')
              </a></div>
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
<script>
      'use strict';
   
      $('.details').on('click',function () { 
        var url = "{{url('user/transaction/details/')}}"+'/'+$(this).data('data').id
        $('.trx_details').text($(this).data('data').details)
        $.get(url,function (res) { 
          if(res == 'empty'){
            $('.list-group').html('<p>@lang('No details found!')</p>')
          }else{
            $('.list-group').html(res)
          }
          $('#modal-success').modal('show')
        })
      })
    </script>
@endpush