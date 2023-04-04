@extends('layouts.user')

@push('css')

@endpush

@section('contents')
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
          @if (count($plans) == 0)
              <div class="card">
                  <h3 class="text-center">{{__('NO FDR PLAN FOUND')}}</h3>
              </div>
            @else

            @foreach ($plans as $key=>$data)
                <div class="col-sm-6 col-lg-4 col-xl-4">
                    <div class="plan__item position-relative">
                        <div class="ribbon ribbon-top ribbon-bookmark bg-green">
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
                                    <div class="name">
                                        @lang('Minimum Amount')
                                    </div>

                                    <div class="info">
                                       {{ $data->min_amount}}
                                    </div>
                                </li>

                                <li>
                                    <div class="name">
                                        @lang('Maximum Amount')
                                    </div>

                                    <div class="info">
                                        {{ $data->max_amount}}
                                    </div>
                                </li>

                                <li>
                                    <div class="name">
                                        @lang('Interval Type')
                                    </div>

                                    <div class="info">
                                        {{ $data->interval_type }}
                                    </div>
                                </li>

                                <li>
                                    <div class="name">
                                        @lang('Locked In Period')
                                    </div>

                                    <div class="info">
                                        {{ $data->matured_days }} {{__('Days')}}
                                    </div>
                                </li>

                                @if ($data->interest_interval)
                                <li>
                                    <div class="name">
                                        @lang('Get Profit every')
                                    </div>

                                    <div class="info">
                                        {{ $data->interest_interval }} {{__('Days')}}
                                    </div>
                                </li>
                                @endif
                            </ul>
                            <a href="javascript:;" class="btn btn-green w-100 apply-fdr" data-id="{{ $data->id}}" data-bs-toggle="modal" data-bs-target="#modal-apply">
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


  <div class="modal modal-blur fade" id="modal-apply" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{('Apply for FDR')}}</h5>
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
          <div class="page-pretitle">
            {{__('Overview')}}
          </div>
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
                        <h3 class="text-center py-5">{{__('No Dps Data Found')}}</h3>
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
                                              <a href="#" id="finish" data-id="{{$data->id}}" class="btn finish">
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
<div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
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
@endsection

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
    $('a.finish').on('click',  function () {
        $this=$(this);
        var data_id = $this.attr('data-id');
        $('#modal-success').modal('show');
        $('#plan_Id').val(data_id);
    })

</script>
@endpush

