@extends('layouts.user')

@push('css')

@endpush

@section('contents')

<div class="container-xl">
  <div class="page-header d-print-none">
    <div class="row align-items-center">
      <h2 class="page-title">
        {{__('Loan Account')}}
      </h2>
    </div>
  </div>
  <div class="page-body">
    <div class="container-xl">
    <div class="row justify-content " style="max-height: 368px;">
      @foreach ($wallets as $item)
      <div class="col-sm-6 col-md-4 mb-3">
          <div class="card h-100 card--info-item">
            <div class="text-end icon">
              <i class="fas ">
                  {{$item->currency->symbol}}
              </i>
            </div>
            <div class="card-body">
              <div class="h3 m-0 text-uppercase"> {{__('Loan')}}</div>
              <div class="h4 m-0 text-uppercase"> {{ $item->wallet_no }}</div>
              <div class="text-muted">{{ amount($item->balance,$item->currency->type,2) }}  {{$item->currency->code}}</div>
            </div>
          </div>
      </div>
      @endforeach
    </div>
    </div>
  </div>
  <div class="page-header d-print-none">
    <div class="row align-items-center">
      <div class="col">
        <h2 class="page-title">
          {{__('Loan Plan')}}
        </h2>
      </div>
    </div>
  </div>
  <div class="page-body">
    <div class="container-xl">
      <div class="row mb--25-none">
          @if (count($plans) == 0)
              <div class="card">
                  <h3 class="text-center">{{__('NO LOAN PLAN FOUND')}}</h3>
              </div>
            @else

            @foreach ($plans as $key=>$data)
                <div class="col-sm-6 col-lg-4 col-xl-4">
                    <div class="plan__item position-relative">
                        <div class="ribbon ribbon-top ribbon-bookmark bg-blue">
                        </div>
                        <div class="plan__item-header">
                            <div class="left">
                                <h4 class="title">{{ $data->title}}</h4>
                            </div>
                            <div class="right">
                                <h5 class="title">{{ $data->per_installment }} %</h5>
                                <span>@lang('Per Installment')</span>
                            </div>
                        </div>
                        <div class="plan__item-body">
                            <ul>
                                <li>
                                    <div class="name">
                                        @lang('Minimum Amount')
                                    </div>

                                    <div class="info">
                                      {{-- {{ showprice($data->min_amount,$currency) }} --}}
                                      {{$data->min_amount}}
                                    </div>
                                </li>
                                <li>
                                    <div class="name">
                                        @lang('Maximum Amount')
                                    </div>

                                    <div class="info">
                                        {{-- {{ showprice($data->max_amount,$currency) }} --}}
                                        {{$data->max_amount}}
                                    </div>
                                </li>
                                <li>
                                    <div class="name">
                                        @lang('Installment Interval')
                                    </div>

                                    <div class="info">
                                        {{ $data->installment_interval }} {{ __('Days')}}
                                    </div>
                                </li>
                                <li>
                                    <div class="name">
                                        @lang('Total Installment')
                                    </div>

                                    <div class="info">
                                        {{ $data->total_installment }}
                                    </div>
                                </li>
                            </ul>
                            <a href="javascript:;" class="btn btn-primary w-100 apply-loan" data-id="{{ $data->id}}" data-bs-toggle="modal" data-bs-target="#modal-apply">
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
  <div class="page-header d-print-none">
    <div class="row align-items-center">
      <h2 class="page-title">
        {{__('Loan Manage')}}
      </h2>
    </div>
  </div>
  <div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
                    @if (count($loans) == 0)
                        <h3 class="text-center py-5">{{__('No Loan Data Found')}}</h3>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter table-mobile-lg card-table">
                                <thead>
                                <tr>
                                    <th>{{ __('Plan No') }}</th>
                                    <th>{{ __('Loan Amount') }}</th>
                                    <th>{{ __('Per Installment') }}</th>
                                    <th>{{ __('Total Installement') }}</th>
                                    <th>{{ __('Next Installment') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('View log') }}</th>
                                    <th>{{ __('Action') }}</th>
                                    <th class="w-1"></th>
                                </tr>
                                </thead>
                                <tbody>
                                  @foreach($loans as $key=>$data)
                                      <tr>
                                          <td data-label="{{ __('Plan No') }}">
                                              <div>
                                                {{ $data->transaction_no }}
                                                <br>
                                              <span class="text-info">{{ $data->plan->title }}</span>
                                              </div>
                                          </td>
                                          <td data-label="{{ __('Loan Amount') }}">
                                            <div>
                                              {{ amount($data->loan_amount, $data->currency->type, 2) }} {{$data->currency->code}}
                                            </div>
                                          </td>
                                          <td data-label="{{ __('Per Installment') }}">
                                            <div>
                                              {{ amount($data->per_installment_amount,$data->currency->type, 2) }} {{$data->currency->code}}
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
                                              @if ($data->status == 0)
                                                <span class="badge bg-warning">@lang('Pending')</span>
                                              @elseif($data->status == 1)
                                                  <span class="badge bg-success">@lang('Running')</span>
                                              @elseif($data->status == 3)
                                                  <span class="badge bg-info">@lang('Paid')</span>
                                              @else
                                                  <span class="badge bg-danger">@lang('Rejected')</span>
                                              @endif
                                            </div>
                                          </td>
                                          <td data-label="{{__('View Log')}}">
                                            <div class="btn-list flex-nowrap">
                                              <a href="{{ route('user.loans.logs',$data->id) }}" class="btn">
                                                @lang('Logs')
                                              </a>
                                            </div>
                                          </td>
                                          <td data-label="{{__('Action')}}">
                                            <div class="btn-list flex-nowrap">
                                               @if($data->status == 1)
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
                        {{ $loans->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
  </div>
</div>


<div class="modal modal-blur fade" id="modal-apply" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{('Apply for Loan')}}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form action="{{ route('user.loan.amount') }}" method="post">
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
<div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="modal-status bg-primary"></div>
        <div class="modal-body text-center py-4">
            <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
            <h3>@lang('Do you want to finish this plan now?')</h3>
        </div>
        <form action="{{ route('user.loan.finish') }}" method="post">
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

    $('.apply-loan').on('click',function(){
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

