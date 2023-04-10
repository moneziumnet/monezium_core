@extends('layouts.user')

@push('css')

@endpush

@section('title', __('Investments'))

@section('contents')
<div class="container-fluid">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
        <div class="col">
            </div>
            <h2 class="page-title">
            {{__('Deposit Account')}}
            </h2>
        </div>
        </div>
    </div>

<div class="page-body">
    <div class="container-fluid">
    <div class="row justify-content " style="max-height: 368px;">
    @if (count($wallets) == 0)
    <div class="card">
        <h3 class="text-center">{{__('NO Wallet FOUND')}}</h3>
    </div>
    @else
    @foreach ($wallets as $item)
    <div class="col-sm-6 col-md-4 mb-3">
        <div class="card h-100 card--info-item">
            <div class="text-end icon rounded-circle">
            <i class="fas ">
                {{$item->currency->symbol}}
            </i>
            </div>
            <div class="card-body">
            <div class="h3 m-0 text-uppercase"> {{__('Deposit')}}</div>
            <div class="h4 m-0 text-uppercase"> {{ $item->wallet_no }}</div>
            <div class="text-muted">{{ amount($item->balance,$item->currency->type,2) }}  {{$item->currency->code}}</div>
            </div>
        </div>
    </div>
    @endforeach
    @endif
    </div>
    </div>
</div>

<div class="container-fluid">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Dps Plan')}}
          </h2>
        </div>
      </div>
    </div>
  </div>
  <div class="page-body">
    <div class="container-fluid">
      <div class="row mb--25-none">
          @if (count($dps_plans) == 0)
              <div class="card">
                  <h3 class="text-center">{{__('NO DPS PLAN FOUND')}}</h3>
              </div>
            @else

            @foreach ($dps_plans as $key=>$data)

                <div class="col-sm-6 col-lg-3 col-xl-3">
                    <div class="plan__item position-relative">
                        <div class="ribbon ribbon-top ribbon-bookmark bg-primary">
                        </div>
                        <div class="plan__item-header">
                            <div class="left">
                                <h4 class="title">{{ $data->title}}</h4>
                            </div>
                            <div class="right">
                                <h5 class="title">
                                    {{ $data->interest_rate }} %
                                </h5>
                                <span>@lang('Interest Rate')</span>
                            </div>
                        </div>
                        <div class="plan__item-body">
                            <ul>
                                <li>
                                    <div class="name font-weight-normal">
                                        @lang('Per Installment')
                                    </div>

                                    <div class="info">
                                        {{ $data->per_installment }}
                                    </div>
                                </li>

                                <li>
                                    <div class="name font-weight-normal">
                                        @lang('Total Deposit')
                                    </div>

                                    <div class="info">
                                        {{ $data->final_amount}}
                                    </div>
                                </li>

                                <li>
                                    <div class="name font-weight-normal">
                                        @lang('After Matured')
                                    </div>

                                    <div class="info">
                                        {{ round($data->final_amount + $data->user_profit,2) }}
                                    </div>
                                </li>

                                <li>
                                    <div class="name font-weight-normal">
                                        @lang('Installment Interval')
                                    </div>

                                    <div class="info">
                                        {{ $data->installment_interval }} {{ __('Days')}}
                                    </div>
                                </li>

                                <li>
                                    <div class="name font-weight-normal">
                                        @lang('Total Installment')
                                    </div>

                                    <div class="info">
                                        {{ $data->total_installment }}
                                    </div>
                                </li>
                            </ul>
                            <a href="javascript:;" class="btn btn-primary w-100 apply-dps" data-id="{{ $data->id}}" data-bs-toggle="modal" data-bs-target="#modal-apply-dps">
                                {{__('Apply')}}
                              </a>
                                {{-- <a href="{{ route('user.dps.planDetails',$data->id) }}" class="btn btn-primary w-100">{{__('Apply')}}</a> --}}
                        </div>
                    </div>
                </div>
            @endforeach
          @endif
      </div>
    </div>
  </div>

  <div class="modal modal-blur fade" id="modal-apply-dps" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-title" style="border-radius: 10px 10px 0 0">
          <div class="ms-3">
            <p>{{('Apply for DPS')}}</p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('user.dps.planDetails') }}" method="post">
            @csrf
            <div class="modal-body">

              <div class="form-group mt-3">
                <label class="form-label required">{{__('Currency')}}</label>
                <select name="currency_id" id="withcurrency" class="form-select" required>
                    <option value="">{{ __('Select Currency') }}</option>
                    @foreach ($currencylist as $currency )
                    <option value="{{$currency->id}}">{{ $currency->code }}</option>
                    @endforeach
                </select>
              </div>

              <input type="hidden" name="planIddps" id="planIddps" value="">
            </div>

            <div class="modal-footer">
                <button type="submit" id="submit-btn" class="btn btn-primary">{{ __('Submit') }}</button>
            </div>
        </form>
      </div>
    </div>
  </div>

  <div class="container-fluid">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('DPS Manage')}}
          </h2>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-fluid">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
                    @if (count($dps) == 0)
                        <h3 class="text-center py-5">{{__('No Dps Data Found')}}</h3>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter table-mobile-lg card-table">
                                <thead>
                                <tr>
                                    <th>{{ __('Plan No') }}</th>
                                    <th>{{ __('Deposit Amount') }}</th>
                                    <th>{{ __('Matured Amount') }}</th>
                                    <th>{{ __('Total Installement') }}</th>
                                    <th>{{ __('Next Installment') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('View Log') }}</th>
                                    <th>{{ __('Action') }}</th>
                                    <th class="w-1"></th>
                                </tr>
                                </thead>
                                <tbody>
                                  @foreach($dps as $key=>$data)
                                      <tr>
                                          <td data-label="{{ __('Plan No') }}">
                                              <div>
                                                  {{ $data->transaction_no }}
                                                  <br>
                                                  <span class="text-info">{{ $data->plan->title }}</span>
                                              </div>
                                          </td>

                                          <td data-label="{{ __('Deposit Amount') }}">
                                            <div>
                                              {{ amount($data->deposit_amount, $data->currency->type, 2) }} {{$data->currency->code}}
                                              <br>
                                              <span class="text-info">{{ amount($data->per_installment, $data->currency->type, 2) }} {{$data->currency->symbol}} {{__('each')}}</span>
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Matured Amount') }}">
                                            <div>
                                              {{ amount($data->matured_amount, $data->currency->type, 2) }} {{$data->currency->code}}
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Total Installement') }}">
                                              <div>
                                                {{ $data->total_installment}}
                                                <br>
                                                <span class="text-info">{{ $data->given_installment }} @lang('Given')</span>
                                              </div>
                                          </td>

                                          <td data-label="{{ __('Next Installment') }}">
                                            <div>
                                              {{ $data->next_installment ?  $data->next_installment->toDateString() : '--'}}
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Status') }}">
                                            <div>
                                              @if ($data->status == 1)
                                                <span class="badge bg-info">@lang('Running')</span>
                                              @else
                                                <span class="badge bg-success">@lang('Matured')</span>
                                              @endif
                                            </div>
                                          </td>

                                          <td data-label="{{ __('View Logs') }}">
                                            <div class="btn-list flex-nowrap">
                                              <a href="{{ route('user.dps.logs',$data->id) }}" class="btn">
                                                @lang('Logs')
                                              </a>
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Action') }}">
                                            <div>
                                              @if ($data->status == 1)
                                              <a href="#" id="dpsfinish" data-id="{{$data->id}}" class="btn dpsfinish">
                                                @lang('Finish')
                                                </a>
                                              @endif
                                            </div>
                                          </td>

                                      </tr>
                                  @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $dps->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-blur fade" id="modal-success-1" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="modal-status bg-primary"></div>
        <div class="modal-body text-center py-4">
            <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
            <h3>@lang('Do you want to finish this plan now?')</h3>
        </div>
        <form action="{{ route('user.dps.finish') }}" method="post">
            @csrf
            <div class="modal-body">
              <div class="form-group">
                  <input type="hidden" name="plan_Id" id="plan_Id" value="">
              </div>
            </div>
            <div class="modal-footer">
                <button type="submit" id="submit-btn" class="btn btn-primary">{{ __('Ok') }}</button>
            </div>
        </form>
    </div>
    </div>
</div>


  <div class="container-fluid">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('FDR Plan')}}
          </h2>
        </div>
      </div>
    </div>
  </div>
  <div class="page-body">
    <div class="container-fluid">
      <div class="row mb--25-none">
          @if (count($fdr_plans) == 0)
              <div class="card">
                  <h3 class="text-center">{{__('NO FDR PLAN FOUND')}}</h3>
              </div>
            @else

            @foreach ($fdr_plans as $key=>$data)
                <div class="col-sm-6 col-lg-3 col-xl-3">
                    <div class="plan__item position-relative">
                        <div class="ribbon ribbon-top ribbon-bookmark bg-primary">
                        </div>
                        <div class="plan__item-header">
                            <div class="left">
                                <h4 class="title">{{ $data->title}}</h4>
                            </div>
                            <div class="right">
                                <h5 class="title">{{ $data->interest_rate }} %</h5>
                                <span>@lang('Interest Rate')</span>
                            </div>
                        </div>
                        <div class="plan__item-body">
                            <ul>
                                <li>
                                    <div class="name font-weight-normal">
                                        @lang('Minimum Amount')
                                    </div>

                                    <div class="info">
                                       {{ $data->min_amount}}
                                    </div>
                                </li>

                                <li>
                                    <div class="name font-weight-normal">
                                        @lang('Maximum Amount')
                                    </div>

                                    <div class="info">
                                        {{ $data->max_amount}}
                                    </div>
                                </li>

                                <li>
                                    <div class="name font-weight-normal">
                                        @lang('Interval Type')
                                    </div>

                                    <div class="info">
                                        {{ $data->interval_type }}
                                    </div>
                                </li>

                                <li>
                                    <div class="name font-weight-normal">
                                        @lang('Locked In Period')
                                    </div>

                                    <div class="info">
                                        {{ $data->matured_days }} {{__('Days')}}
                                    </div>
                                </li>

                                @if ($data->interest_interval)
                                <li>
                                    <div class="name font-weight-normal">
                                        @lang('Get Profit every')
                                    </div>

                                    <div class="info">
                                        {{ $data->interest_interval }} {{__('Days')}}
                                    </div>
                                </li>
                                @endif
                            </ul>
                            <a href="javascript:;" class="btn btn-primary w-100 apply-fdr" data-id="{{ $data->id}}" data-bs-toggle="modal" data-bs-target="#modal-apply-fdr">
                                    {{__('Apply')}}
                                  </a>
                        </div>
                    </div>
                </div>
            @endforeach
          @endif
      </div>
    </div>
  </div>


  <div class="modal modal-blur fade" id="modal-apply-fdr" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-title" style="border-radius: 10px 10px 0 0">
          <div class="ms-3">
            <p>{{('Apply for FDR')}}</p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('user.fdr.amount') }}" method="post">
            @csrf
            <div class="modal-body">
              <div class="form-group">
                <label class="form-label required">{{__('Amount')}}</label>
                <input name="amount" id="amount" class="form-control" autocomplete="off" placeholder="{{__('0.0')}}" type="number" step="any" value="{{ old('amount') }}" min="1" required>
              </div>

              <div class="form-group mt-3">
                <label class="form-label required">{{__('Currency')}}</label>
                <select name="currency_id" id="withcurrency" class="form-select" required>
                    <option value="">{{ __('Select Currency') }}</option>
                    @foreach ($currencylist as $currency )
                    <option value="{{$currency->id}}">{{ $currency->code }}</option>
                    @endforeach
                </select>
              </div>

              <input type="hidden" name="planId" id="planId" value="">
            </div>

            <div class="modal-footer">
                <button type="submit" id="submit-btn" class="btn btn-primary">{{ __('Submit') }}</button>
            </div>
        </form>
      </div>
    </div>
  </div>






<div class="container-fluid">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('FDR Manage')}}
          </h2>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-fluid">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
                    @if (count($fdr) == 0)
                        <h3 class="text-center py-5">{{__('No FDR Data Found')}}</h3>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter table-mobile-md card-table">
                                <thead>
                                <tr>
                                    <th>{{ __('Plan No') }}</th>
                                    <th>{{ __('FDR Amount') }}</th>
                                    <th>{{ __('Profit Type') }}</th>
                                    <th>{{ __('Profit') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                  @foreach($fdr as $key=>$data)
                                      <tr>
                                          <td data-label="{{ __('Plan No') }}">
                                            <div>

                                              {{ $data->transaction_no }}
                                              <br>
                                            <span class="text-info">{{ $data->plan->title }}</span>
                                            </div>
                                          </td>

                                          <td data-label="{{ __('FDR Amount') }}">
                                            <div>

                                              {{ amount($data->amount, $data->currency->type, 2)}} {{ $data->currency->code}}
                                              <br>
                                              <span class="text-info">@lang('Profit Rate') {{$data->interest_rate}} (%) </span>
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Profit Type') }}">
                                            <div>
                                              {{ strtoupper($data->profit_type) }}
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Profit') }}">
                                            <div class="text-center text-md-start">

                                              {{ amount($data->profit_amount, $data->currency->type, 2) }} {{ $data->currency->code}}
                                              <br>
                                              @if ($data->profit_type == 'partial')
                                                  <span class="text-info"> @lang('Next Frofit Days') ({{ $data->next_profit_time ? $data->next_profit_time->toDateString() : "--" }})</span>
                                              @else
                                                  <span class="text-info"> @lang('Profit will get after locked period') </span>
                                              @endif
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Status') }}">
                                            <div>
                                              @if ($data->status == 1)
                                                <span class="badge bg-success">@lang('Running')</span>
                                              @else
                                                <span class="badge bg-danger">@lang('Closed')</span>
                                              @endif
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Action') }}">
                                            <div>
                                              @if ($data->status == 1)
                                              <a href="#" id="fdrfinish" data-id="{{$data->id}}" class="btn fdrfinish">
                                                @lang('Finish')
                                                </a>
                                              @endif
                                            </div>
                                          </td>
                                      </tr>
                                  @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $fdr->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal modal-blur fade" id="modal-success-2" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="modal-status bg-primary"></div>
        <div class="modal-body text-center py-4">
            <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
            <h3>@lang('Do you want to finish this plan now?')</h3>
        </div>
        <form action="{{ route('user.fdr.finish') }}" method="post">
            @csrf
            <div class="modal-body">
              <div class="form-group">
                  <input type="hidden" name="plan_Id" id="fdrplan_Id" value="">
              </div>
            </div>
            <div class="modal-footer">
                <button type="submit" id="submit-btn" class="btn btn-primary">{{ __('Ok') }}</button>
            </div>
        </form>
    </div>
    </div>
</div>



@endsection

@push('js')
<script>
  'use strict';
    $('.apply-dps').on('click',function(){
        let id = $(this).data('id');
        $('#planIddps').val(id);
    });
</script>
@endpush

@push('js')
<script>
    $('a.dpsfinish').on('click',  function () {
        $this=$(this);
        var data_id = $this.attr('data-id');
        console.log(data_id);
        $('#modal-success-1').modal('show');
        $('#plan_Id').val(data_id);
    })

</script>
@endpush

@push('js')
<script>
  'use strict';
    $('.apply-fdr').on('click',function(){
        let id = $(this).data('id');
        $('#planId').val(id);
    });

</script>
@endpush

@push('js')
<script>
    $('a.fdrfinish').on('click',  function () {
        $this=$(this);
        var data_id = $this.attr('data-id');
        $('#modal-success-2').modal('show');
        $('#fdrplan_Id').val(data_id);
    })

</script>
@endpush

