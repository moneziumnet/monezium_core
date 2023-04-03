@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
        @include('user.settingtab')
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Pricing Plans')}}
          </h2>
        </div>
      </div>
    </div>
  </div>
  <div class="page-body">
    <div class="container-xl">
      <div class="row">
          @if (count($packages) == 0)
              <div class="card">
                  <h3 class="text-center">{{__('NO PLAN FOUND')}}</h3>
              </div>
            @else

            @foreach ($packages as $key=>$data)

                <div class="col-sm-6 col-lg-4 col-xl-4">
                    <div class="plan__item position-relative">
                        <div class="ribbon ribbon-top ribbon-bookmark bg-primary">
                        </div>
                        <div class="plan__item-header">
                            <div class="left">
                                <h4 class="title">{{ $data->title}}</h4>
                            </div>
                            <div class="right">
                                <h5 class="title">
                                    {{$currency->symbol}}{{ $data->amount }}
                                </h5>
                                <span>{{ $data->days }} @lang('Days')</span>
                            </div>
                        </div>
                        <div class="plan__item-body">
                            <ul>
                            <li>
                                        <div class="name">
                                            @lang('Maximum Send Money (Daily)')
                                        </div>
                                        <div class="info">
                                            {{$currency->symbol}}{{ plan_details_by_type('send', $data->id)->daily_limit }}
                                        </div>
                                    </li>

                                    <li>
                                        <div class="name">
                                            @lang('Maximum Send Money (Monthly)')
                                        </div>

                                        <div class="info">
                                            {{$currency->symbol}}{{ plan_details_by_type('send', $data->id)->monthly_limit }}
                                        </div>
                                    </li>

                                    <li>
                                        <div class="name">
                                            @lang('Maximum Request Money (Daily)')
                                        </div>

                                        <div class="info">
                                            {{$currency->symbol}}{{ plan_details_by_type('recieve', $data->id)->daily_limit }}
                                        </div>
                                    </li>

                                    <li>
                                        <div class="name">
                                            @lang('Maximum Request Money (Monthly)')
                                        </div>

                                        <div class="info">
                                            {{$currency->symbol}}{{ plan_details_by_type('recieve', $data->id)->monthly_limit }}
                                        </div>
                                    </li>

                                    <li>
                                        <div class="name">
                                            @lang('Maximum Withdraw Amount (Daily)')
                                        </div>

                                        <div class="info">
                                            {{$currency->symbol}}{{ plan_details_by_type('withdraw', $data->id)->daily_limit  }}
                                        </div>
                                    </li>
                                    <li>
                                        <div class="name">
                                            @lang('Maximum Withdraw Amount (Monthly)')
                                        </div>

                                        <div class="info">
                                            {{$currency->symbol}}{{ plan_details_by_type('withdraw', $data->id)->monthly_limit  }}
                                        </div>
                                    </li>

                                    <li>
                                        <div class="name">
                                            @lang('Maximum Deposit Money (Daily)')
                                        </div>

                                        <div class="info">
                                            {{$currency->symbol}}{{ plan_details_by_type('deposit', $data->id)->daily_limit }}
                                        </div>
                                    </li>

                                    <li>
                                        <div class="name">
                                            @lang('Maximum Deposit Money (Monthly)')
                                        </div>

                                        <div class="info">
                                            {{$currency->symbol}}{{ plan_details_by_type('deposit', $data->id)->monthly_limit }}
                                        </div>
                                    </li>

                                    <li>
                                        <div class="name">
                                            @lang('Maximum Escrow Money (Daily)')
                                        </div>

                                        <div class="info">
                                            {{$currency->symbol}}{{ plan_details_by_type('escrow', $data->id)->daily_limit }}
                                        </div>
                                    </li>

                                    <li>
                                        <div class="name">
                                            @lang('Maximum Escrow Money (Monthly)')
                                        </div>

                                        <div class="info">
                                            {{$currency->symbol}}{{ plan_details_by_type('escrow', $data->id)->monthly_limit }}
                                        </div>
                                    </li>

                                    <li>
                                        <div class="name">
                                            @lang('Installment Interval')
                                        </div>
                                        <div class="info">
                                            {{ $data->days }} {{ __('Days')}}
                                        </div>
                                    </li>


                                @if ($data->attribute)
                                    @foreach (json_decode($data->attribute,true) as $key=>$attribute)
                                        <li>
                                            <div class="w-100">
                                                {{ $attribute }}
                                            </div>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                            @if (auth()->user()->bank_plan_id == $data->id)
                                <a href="javascript:;" class="btn btn-primary w-100">
                                    {{__('Current Plan')}}
                                </a>

                                <div class="text-end mt-2">
                                    ({{ auth()->user()->plan_end_date ? auth()->user()->plan_end_date->toDateString() : '' }}) <a href="{{route('user.package.subscription',$data->id)}}" class="text--base">@lang('Renew Plan')</a>
                                </div>
                            @else
                                <a href="{{route('user.package.subscription',$data->id)}}" class="btn btn-primary w-100">
                                    {{__('Get Started')}}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
          @endif
      </div>
    </div>
  </div>


@endsection

@push('js')

@endpush

