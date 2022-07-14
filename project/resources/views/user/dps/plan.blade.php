@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
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
    <div class="container-xl">
      <div class="row mb--25-none">
          @if (count($plans) == 0)
              <div class="card">
                  <h3 class="text-center">{{__('NO DPS PLAN FOUND')}}</h3>
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
                                <h5 class="title">
                                    {{ $data->interest_rate }} %
                                </h5>
                                <span>@lang('Interest Rate')</span>
                            </div>
                        </div>
                        <div class="plan__item-body">
                            <ul>
                                <li>
                                    <div class="name">
                                        @lang('Per Installment')
                                    </div>

                                    <div class="info">
                                        {{ $data->per_installment }}
                                    </div>
                                </li>

                                <li>
                                    <div class="name">
                                        @lang('Total Deposit')
                                    </div>

                                    <div class="info">
                                        {{ $data->final_amount}}
                                    </div>
                                </li>

                                <li>
                                    <div class="name">
                                        @lang('After Matured')
                                    </div>

                                    <div class="info">
                                        {{ round($data->final_amount + $data->user_profit,2) }}
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
                            <a href="javascript:;" class="btn btn-green w-100 apply-dps" data-id="{{ $data->id}}" data-bs-toggle="modal" data-bs-target="#modal-apply">
                                {{__('Apply')}}
                              </a>
                                {{-- <a href="{{ route('user.dps.planDetails',$data->id) }}" class="btn btn-green w-100">{{__('Apply')}}</a> --}}
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
          <h5 class="modal-title">{{('Apply for DPS')}}</h5>
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

              <input type="hidden" name="planId" id="planId" value="">
            </div>

            <div class="modal-footer">
                <button type="submit" id="submit-btn" class="btn btn-primary">{{ __('Submit') }}</button>
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
        $('#planId').val(id);
    });
</script>
@endpush

