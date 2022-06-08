@extends('layouts.merchant')

{{-- @section('title')
   @lang('Merchant Dashboard')
@endsection

@section('breadcrumb')
 <section class="section">
    <div class="section-header">
        <h1>@lang('Merchant Dashboard')</h1>
    </div>
</section>
@endsection --}}

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4 py-3">
  <h1 class="h3 mb-0 text-gray-800">{{ __('Wallet') }}</h1>
  <ol class="breadcrumb m-0 py-0">
    <li class="breadcrumb-item"><a href="{{route('merchant.dashboard')}}">{{ __('Dashboard') }}</a></li>
  </ol>
</div>
@if(Session::has('cache'))

<div class="alert alert-success validation">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
            aria-hidden="true">×</span></button>
    <h3 class="text-center">{{ Session::get("cache") }}</h3>
</div>

@endif
{{-- @dd($wallets) --}}
<div class="row mb-3">
@if(!empty($wallets))
  @foreach($wallets as $wallet)
  <div class="col-xl-4 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">{{ __('Wallet Balance') }}</div>
            <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800"> {{ $currency->code}} </div>
          </div>
          <div class="col-auto">
            <i class="fas fa-dollar-sign fa-2x text-success"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endforeach
@endif

  

  <div class="col-xl-4 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">{{ __('Total Withdraw Amount') }}</div>
            <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">{{ $currency->code}}</div>
          </div>
          <div class="col-auto">
            <i class="fas fa-dollar-sign fa-2x text-success"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  
</div>

<div class="row mb-3">
  
  <div class="col-sm-6 col-xl-3 mb-3">
    <div class="card card-sm">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-auto">
            <span class="bg-danger text-white avatar">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrows-right-left"  width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"  stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <line x1="21" y1="7" x2="3" y2="7"></line>
                <path d="M18 10l3 -3l-3 -3"></path>
                <path d="M6 20l-3 -3l3 -3"></path>
                <line x1="3" y1="17" x2="21" y2="17"></line>
             </svg>
            </span>
          </div>
          <div class="col">
            <div class="font-weight-medium">
              {{-- {{numFormat($totalTransferMoney,2)}} {{$gs->curr_code}} <sup class="text-danger">*</sup> --}}
            </div>
            <div class="text-muted">
              @lang('Total Money Transfered')
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3 mb-3">
    <div class="card card-sm">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-auto">
            <span class="bg-success text-white avatar">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-currency-dogecoin" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <path d="M6 12h6"></path>
                <path d="M9 6v12"></path>
                <path d="M6 18h6a6 6 0 1 0 0 -12h-6"></path>
             </svg>
            </span>
          </div>
          <div class="col">
            <div class="font-weight-medium">
              {{-- {{numFormat($totalDeposit,2)}} {{$gs->curr_code}} <sup class="text-danger">*</sup> --}}
            </div>
            <div class="text-muted">
              @lang('Total Deposit')
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3 mb-3">
    <div class="card card-sm">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-auto">
            <span class="bg-warning text-white avatar">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-copy" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <rect x="8" y="8" width="12" height="12" rx="2"></rect>
                <path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2"></path>
             </svg>
            </span>
          </div>
          <div class="col">
            <div class="font-weight-medium">
              {{-- {{numFormat($totalWithdraw,2)}} {{$gs->curr_code}} <sup class="text-danger">*</sup> --}}
            </div>
            <div class="text-muted">
              @lang('Total Withdraw')
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3 mb-3">
    <div class="card card-sm">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-auto">
            <span class="bg-info text-white avatar">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrows-right-left" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <line x1="21" y1="7" x2="3" y2="7"></line>
                <path d="M18 10l3 -3l-3 -3"></path>
                <path d="M6 20l-3 -3l3 -3"></path>
                <line x1="3" y1="17" x2="21" y2="17"></line>
             </svg>
            </span>
          </div>
          <div class="col">
            <div class="font-weight-medium">
              {{-- {{numFormat($totalExchange,2)}} {{$gs->curr_code}} <sup class="text-danger">*</sup> --}}
            </div>
            <div class="text-muted">
              @lang('Total Exchange')
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<div class="row mb-3">
  <div class="col-xl-12 col-lg-12 mb-4">
    <div class="card">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">@lang('Recent Transactions')</h6>
      </div>
      @if (count($recentTransactions)>0)
          
        <div class="table-responsive">
          <table class="table align-items-center table-flush">
            <thead class="thead-light">
              <tr>
                <th>@lang('Date')</th>
                <th>@lang('Transaction ID')</th>
                <th>@lang('Description')</th>
                <th>@lang('Remark')</th>
                <th>@lang('Amount')</th>
                <th>@lang('Details')</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentTransactions as $trx)
               
              <tr>
                <td data-label="@lang('Date')">{{dateFormat($trx->created_at,'d-M-Y')}}</td>
                <td data-label="@lang('Transaction ID')">{{$trx->trnx}}</td>
                <td data-label="@lang('Description')">
                  {{__($trx->details)}}
                </td>
                <td data-label="@lang('Remark')">
                  <span class="badge badge-dark">{{ucwords(str_replace('_',' ',$trx->remark))}}</span>
                </td>
                <td data-label="@lang('Amount')">
                    <span class="{{$trx->type == '+' ? 'text-success':'text-danger'}}">{{$trx->type}} {{amount($trx->amount,$trx->currency->type,2)}} {{$trx->currency->code}}</span> 
                </td>
                <td data-label="@lang('Details')" class="text-end">
                    <button class="btn btn-primary btn-sm details" data-data="{{$trx}}">@lang('Details')</button>
                </td>
              </tr>
              @empty
            @endforelse

            </tbody>
          </table>
        </div>
        <div class="card-footer"></div>
        @else 
          <p class="text-center">@lang('NO DATA FOUND')</p>
      @endif
    </div>
  </div>
</div>

{{-- <div class="row mb-3">
  <div class="col-xl-12 col-lg-12 mb-4">
    <div class="card">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">@lang('Wallets')</h6>
      </div>
      @if (count($wallets)>0)
          
        <div class="table-responsive">
          <table class="table align-items-center table-flush">
            <thead class="thead-light">
              <tr>
                <th>@lang('Date')</th>
                <th>@lang('Transaction ID')</th>
                <th>@lang('Description')</th>
                <th>@lang('Remark')</th>
                <th>@lang('Amount')</th>
                <th>@lang('Details')</th>
              </tr>
            </thead>
            <tbody>
              @forelse($wallets as $item)
               
              <tr>
                <td data-label="@lang('Date')">{{dateFormat($trx->created_at,'d-M-Y')}}</td>
                <td data-label="@lang('Transaction ID')">{{$trx->trnx}}</td>
                <td data-label="@lang('Description')">
                  {{__($trx->details)}}
                </td>
                <td data-label="@lang('Remark')">
                  <span class="badge badge-dark">{{ucwords(str_replace('_',' ',$trx->remark))}}</span>
                </td>
                <td data-label="@lang('Amount')">
                    <span class="{{$trx->type == '+' ? 'text-success':'text-danger'}}">{{$trx->type}} {{amount($trx->amount,$trx->currency->type,2)}} {{$trx->currency->code}}</span> 
                </td>
                <td data-label="@lang('Details')" class="text-end">
                    <button class="btn btn-primary btn-sm details" data-data="{{$trx}}">@lang('Details')</button>
                </td>
              </tr>
              @empty
              <tr>
                <td class="text-center" colspan="6">@lang('No data found!')</td>
            </tr>
            @endforelse

            </tbody>
          </table>
        </div>
        <div class="card-footer"></div>
        @else 
          <p class="text-center">@lang('NO DATA FOUND')</p>
      @endif
    </div>
  </div>
</div> --}}

<div class="row mb-3">
  <div class="col-xl-12 col-lg-12 mb-4">
    <div class="card">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">@lang('Recent Withdraws')</h6>
      </div>
      @if (count($recentTransactions)>0)
          
        <div class="table-responsive">
          <table class="table align-items-center table-flush">
            <thead class="thead-light">
              <tr>
                <th>@lang('Transaction')</th>
                <th>@lang('Amount')</th>
                <th>@lang('Charge')</th>
                <th>@lang('Total Amount')</th>
                <th>@lang('Method Name')</th>
                <th>@lang('Status')</th>
                <th>@lang('Date')</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($recentWithdraw as $item)
               
              <tr>
                <td data-label="@lang('Transaction')">{{$item->trx}}</td>
                <td data-label="@lang('Amount')">{{amount($item->amount,$item->currency->type,2)}} {{$item->currency->code}}</td>
                <td data-label="@lang('Charge')">{{amount($item->charge,$item->currency->type,2)}} {{$item->currency->code}}</td>
                <td data-label="@lang('Total Amount')">{{amount($item->total_amount,$item->currency->type,2)}} {{$item->currency->code}}</td>
                <td data-label="@lang('Method Name')">{{$item->method->name}}</td>
                <td data-label="@lang('Status')">
                    @if($item->status == 1)
                        <span class="badge bg-success">@lang('Accepted')</span>
                    @elseif($item->status == 2)
                        <span class="badge badge-danger">@lang('Rejected')</span>
                        <button class="btn btn-sm bg-dark text-white reason" data-bs-toggle="modal" data-bs-target="#modal-team" data-reason="{{$item->reject_reason}}"><i class="fas fa-info"></i></button>
                    @else
                        <span class="badge badge-warning">@lang('Pending')</span>
                    @endif
                </td>
                <td data-label="@lang('Date')">{{dateFormat($item->created_at)}}</td>
            </tr>
            @empty
            <tr>
                <td class="text-center" colspan="7">@lang('No data found!')</td>
            </tr>
            @endforelse

            </tbody>
          </table>
        </div>
        <div class="card-footer"></div>
        @else 
          <p class="text-center">@lang('NO DATA FOUND')</p>
      @endif
    </div>
  </div>
</div>

{{-- <div class="row">
    <div class="col-md-12 mb-3">
        <h6>@lang('Wallets')</h6>
    </div>
    @foreach ($wallets as $item)
    <div class="col-xl-3 col-md-6 currency--card">
        <div class="card card-statistic-1">
            <div class="card-icon bg-primary text-white">
                {{$item->currency->code}}
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>@lang($item->currency->curr_name)</h4>
                </div>
                <div class="card-body">
                    {{amount($item->balance,$item->currency->type,2)}} {{$item->currency->code}}
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div> --}}

<div class="row row-deck row-cards">
    
{{-- 
<div class="row row-deck row-cards">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4>@lang('Recent Withdraws')</h4>
            </div>
            <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped">
                <thead>
                <tr>
                    <th>@lang('Transaction')</th>
                    <th>@lang('Amount')</th>
                    <th>@lang('Charge')</th>
                    <th>@lang('Total Amount')</th>
                    <th>@lang('Method Name')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Date')</th>
                </tr>
                </thead>
                <tbody>
                 @forelse ($recentWithdraw as $item)
                    <tr>
                        <td data-label="@lang('Transaction')">{{$item->trx}}</td>
                        <td data-label="@lang('Amount')">{{amount($item->amount,$item->currency->type,2)}} {{$item->currency->code}}</td>
                        <td data-label="@lang('Charge')">{{amount($item->charge,$item->currency->type,2)}} {{$item->currency->code}}</td>
                        <td data-label="@lang('Total Amount')">{{amount($item->total_amount,$item->currency->type,2)}} {{$item->currency->code}}</td>
                        <td data-label="@lang('Method Name')">{{$item->method->name}}</td>
                        <td data-label="@lang('Status')">
                            @if($item->status == 1)
                                <span class="badge bg-success">@lang('Accepted')</span>
                            @elseif($item->status == 2)
                                <span class="badge badge-danger">@lang('Rejected')</span>
                                <button class="btn btn-sm bg-dark text-white reason" data-bs-toggle="modal" data-bs-target="#modal-team" data-reason="{{$item->reject_reason}}"><i class="fas fa-info"></i></button>
                            @else
                                <span class="badge badge-warning">@lang('Pending')</span>
                            @endif
                        </td>
                        <td data-label="@lang('Date')">{{dateFormat($item->created_at)}}</td>
                    </tr>
                    @empty
                    <tr>
                        <td class="text-center" colspan="12">@lang('No data found!')</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div> --}}

<div class="modal modal-blur fade" id="modal-team" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">@lang('Reject Reasons')</h5>
          
        </div>
        <div class="modal-body">
          <div>
            <textarea class="form-control reject-reason" rows="5" disabled></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-dark ms-auto" data-dismiss="modal">@lang('Close')</button>
         
        </div>
      </div>
    </div>
</div>

<div class="modal fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-body text-center py-4">
        <h3>@lang('Transaction Details')</h3>
        <p class="trx_details"></p>
        <ul class="list-group mt-2">
           
        </ul>
        </div>
        <div class="modal-footer">
        <div class="w-100">
            <div class="row">
            <div class="col"><a href="#" class="btn w-100 btn-dark" data-dismiss="modal">
                @lang('Close')
                </a></div>
            </div>
        </div>
        </div>
    </div>
    </div>
</div>

@endsection

@push('script')
    <script>
      'use strict';
   
      $('.details').on('click',function () { 
        var url = "{{url('merchant/transaction/details/')}}"+'/'+$(this).data('data').id
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

      $('.reason').on('click',function() { 
            $('#modal-team').find('.reject-reason').val($(this).data('reason'))
            $('#modal-team').modal('show')
        })
    </script>
@endpush