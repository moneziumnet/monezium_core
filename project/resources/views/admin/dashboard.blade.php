@extends('layouts.admin')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4 py-3">
  <h1 class="h3 mb-0 text-gray-800">{{ __('Dashboard') }}</h1>
  <ol class="breadcrumb m-0 py-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
  </ol>
</div>
@if(Session::has('cache'))

<div class="alert alert-success validation">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
  <h3 class="text-center">{{ Session::get("cache") }}</h3>
</div>

@endif
<div class="card mt-3 mb-3">
    <div class="card-body">
      <div class="d-flex justify-content-center">
        <h3 class="card-title">{{ __('Incoming and Withdraw') }}</h3>
      </div>
      <div id="chart_finance_monthly" class="chart-lg"></div>
    </div>
</div>
@if(Auth::guard('admin')->user()->IsSuper())
<div class="row mb-3">
  <div class="col-xl-4 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">{{ __('Active Institutions') }}</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800">{{count($ainstitutions)}}</div>
          </div>
          <div class="col-auto">
            <i class="fas fa-user fa-2x text-success"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-4 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">{{ __('Languages') }}</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800">{{count($languages)}}</div>
          </div>
          <div class="col-auto">
            <i class="fas fa-user fa-2x text-success"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-4 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">{{ __('Active Domains') }}</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800">{{count($adomains)}}</div>
          </div>
          <div class="col-auto">
            <i class="fas fa-user fa-2x text-success"></i>
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
        <h6 class="m-0 font-weight-bold text-primary">@lang('Recent Registration Institutions')</h6>
      </div>
      @if (count($ainstitutions)>0)

      <div class="table-responsive">
        <table class="table align-items-center table-flush">
          <thead class="thead-light">
            <tr>
              <th>@lang('Serial No')</th>
              <th>@lang('Name')</th>
              <th>@lang('Email')</th>
              <th>@lang('Status')</th>
              <!-- <th>@lang('Action')</th> -->
            </tr>
          </thead>
          <tbody>
            @foreach ($ainstitutions as $key=>$data)
            <tr>
              <td data-label="@lang('Serial No')">{{ $loop->iteration}}</td>
              <td data-label="@lang('Name')">{{ $data->name }}</td>
              <td data-label="@lang('Email')">{{ $data->email }}</td>
              <td data-label="@lang('Status')"><span class="badge badge-{{ $data->status == 1 ? 'success' : 'danger'}}">{{ $data->status == 1 ? 'activated' : 'deactivated'}}</span></td>
              <!-- <td data-label="@lang('Action')"><a href="{{ route('admin-user-profile',$data->id) }}" class="btn btn-sm btn-primary">@lang('Detail')</a></td> -->
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="card-footer"></div>
      @else
      <p class="text-center">@lang('NO USER FOUND')</p>
      @endif
    </div>
  </div>
</div>
@else
<div class="row mb-3">
  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">{{ __('Active Customers') }}</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800">{{count($acustomers)}}</div>
          </div>
          <div class="col-auto">
            <i class="fas fa-user fa-2x text-success"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">{{ __('Blocked Customers') }}</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ count($bcustomers) }}</div>
          </div>
          <div class="col-auto">
            <i class="fas fa-user fa-2x text-danger"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">{{ __('Total Blogs') }}</div>
            <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">{{ count($blogs) }} </div>
          </div>
          <div class="col-auto">
            <i class="fas fa-fw fa-newspaper fa-2x text-success"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">{{ __('Total Transactions') }}</div>
            <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">{{ count($transactions) }} </div>
          </div>
          <div class="col-auto">
            <i class="fas fa-dollar-sign fa-2x text-success"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">{{ __('Total Loan') }}</div>
            <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">{{ count(\App\Models\UserLoan::get()) }} </div>
          </div>
          <div class="col-auto">
            <i class="fas fa-cash-register fa-2x text-success"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">{{ __('Total DPS') }}</div>
            <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">{{ count(\App\Models\UserDps::get()) }} </div>
          </div>
          <div class="col-auto">
            <i class="fas fa-warehouse fa-2x text-success"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">{{ __('Total FDR') }}</div>
            <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">{{ count(\App\Models\UserFdr::get()) }} </div>
          </div>
          <div class="col-auto">
            <i class="fas fa-user-shield fa-2x text-success"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">{{ __('Total Tickets') }}</div>
            <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">{{ count(\App\Models\AdminUserConversation::get()) }} </div>
          </div>
          <div class="col-auto">
            <i class="fas fa-ticket-alt fa-2x text-success"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">{{ __('Telegram Users') }}</div>
            <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">{{ count(\App\Models\UserTelegram::where('chat_id', '!=', null)->get()) }} </div>
          </div>
          <div class="col-auto">
            <i class="fab fa-telegram fa-2x text-success"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">{{ __('Whatsapp Users') }}</div>
            <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">{{ count(\App\Models\UserWhatsapp::where('phonenumber', '!=', null)->get()) }} </div>
          </div>
          <div class="col-auto">
            <i class="fab fa-whatsapp fa-2x text-success"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">{{ __('Shops') }}</div>
            <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">{{ count(\App\Models\MerchantShop::where('status', 1)->get()) }} </div>
          </div>
          <div class="col-auto">
            <i class="fas fa-shopping-cart fa-2x text-success"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">{{ __('Product') }}</div>
            <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">{{ count(\App\Models\Product::where('status', 1)->get()) }} </div>
          </div>
          <div class="col-auto">
            <i class="fas fa-shopping-bag fa-2x text-success"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">{{ __('Campaign') }}</div>
            <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">{{ count(\App\Models\Campaign::where('status', 1)->get()) }} </div>
          </div>
          <div class="col-auto">
            <i class="fas fa-donate fa-2x text-success"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<div class="row mb-3">
  <div class="col-xl-4 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">{{ __('Total Deposit Amount') }}</div>
            <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">{{ round($depositAmount,2) }} {{ $currency->code}} </div>
          </div>
          <div class="col-auto">
            <i class="fas fa-dollar-sign fa-2x text-success"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-4 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">{{ __('Total Withdraw Amount') }}</div>
            <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">{{ round($withdrawAmount,2) }} {{ $currency->code}}</div>
          </div>
          <div class="col-auto">
            <i class="fas fa-dollar-sign fa-2x text-success"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-4 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">{{ __('Total Charge Amount') }}</div>
            <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">{{ round($ChargeAmount,2) }} {{ $currency->code}}</div>
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
  <div class="col-xl-12 col-lg-12 mb-4">
    <div class="card">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">@lang('Recent Joined Users')</h6>
      </div>
      @if (count($users)>0)

      <div class="table-responsive">
        <table class="table align-items-center table-flush">
          <thead class="thead-light">
            <tr>
              <th>@lang('Serial No')</th>
              <th>@lang('Name')</th>
              <th>@lang('Email')</th>
              <th>@lang('Status')</th>
              <th>@lang('Action')</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($users as $key=>$data)
            <tr>
              <td data-label="@lang('Serial No')">{{ $loop->iteration}}</td>
              <td data-label="@lang('Name')">{{ $data->name }}</td>
              <td data-label="@lang('Email')">{{ $data->email }}</td>
              <td data-label="@lang('Status')"><span class="badge badge-{{ $data->is_banned == 0 ? 'success' : 'danger'}}">{{ $data->is_banned == 0 ? 'activated' : 'deactivated'}}</span></td>
              <td data-label="@lang('Action')"><a href="{{ route('admin-user-profile',$data->id) }}" class="btn btn-sm btn-primary">@lang('Detail')</a></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="card-footer"></div>
      @else
      <p class="text-center">@lang('NO USER FOUND')</p>
      @endif
    </div>
  </div>
</div>
@endif
@endsection


@section('scripts')
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

@endsection
