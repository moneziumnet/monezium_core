@extends('layouts.admin')

@section('content')
<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between">
  <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Edit Plan') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.bank.plan.index')}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
  <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="javascript:;">{{ __('Pricing Plan') }}</a></li>
  </ol>
  </div>
</div>

<div class="row justify-content-center mt-3">
<div class="col-md-10">
  <div class="card mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
      <h6 class="m-0 font-weight-bold text-primary">{{ __('Edit Plan Form') }}</h6>
    </div>

    <div class="card-body">
      <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
      <form class="geniusform" action="{{route('admin.bank.plan.update',$data->id)}}" method="POST" enctype="multipart/form-data">

          @include('includes.admin.form-both')

          {{ csrf_field() }}

          <div class="row">
            <div class="col-md-3">
              <div class="form-group">
                <label for="title">{{ __('Title') }}</label>
                <input type="text" class="form-control" id="title" name="title" placeholder="{{ __('Enter Title') }}" value="{{ $data->title }}" required>
              </div>
            </div>

            <div class="col-md-3">
              <div class="form-group">
                <label for="amount">{{ __('Amount') }}</label>
                <input type="number" class="form-control" id="amount" name="amount" placeholder="{{ __('Enter Amount') }}" min="0" value="{{ $data->amount }}" required>
              </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                  <label for="days">{{ __('Days') }}</label>
                  <input type="number" class="form-control" id="days" name="days" placeholder="{{ __('Enter Days') }}" min="1" value="{{ $data->days }}" required>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label >{{ __('') }}</label>
                    <button type="submit" id="submit-btn" class="btn btn-primary w-100 mt-2">{{ __('Submit') }}</button>
                </div>
            </div>

          </div>
      </form>
            @foreach ($plan_details as $detail)
                <form class="geniusform" action="{{route('admin.bank.plan.detail.update',$detail->id)}}" method="POST" enctype="multipart/form-data">
                    @include('includes.admin.form-both')

                    {{ csrf_field() }}


                    <div class="row">
                    <div class="col-md-1">
                        <div class="form-group">
                        <label for="detail_type">{{ __('Name') }}</label>
                        <input type="text" class="form-control" id="detail_type" name="detail_type" placeholder="{{ __('Enter Name') }}" value="{{ $detail->type }}" readonly>
                        </div>
                    </div>

                    <div class="col-md-1">
                        <div class="form-group">
                        <label for="detail_min">{{ __('Min') }}</label>
                        <input type="number" class="form-control" id="detail_min" name="detail_min" placeholder="{{ __('Enter Min Value') }}" min="0" value="{{ $detail->min }}" required>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="detail_max">{{ __('Max') }}</label>
                            <input type="number" class="form-control" id="detail_max" name="detail_max" placeholder="{{ __('Enter Max Value') }}" min="1" value="{{ $detail->max }}" required>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="detail_daily">{{ __('Maximum Send Money') }} ({{ __('Daily')}})</label>
                            <input type="number" class="form-control" id="detail_daily" name="detail_daily" placeholder="{{ __('Enter Max Value') }}" min="1" value="{{ $detail->daily_limit }}" required>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="detail_monthly">{{ __('Maximum Send Money') }} ({{ __('Monthly')}})</label>
                            <input type="number" class="form-control" id="detail_monthly" name="detail_monthly" placeholder="{{ __('Enter Max Value') }}" min="1" value="{{ $detail->monthly_limit }}" required>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label >{{ __('') }}</label>
                            <button type="submit" id="submit-btn" class="btn btn-primary w-100 mt-2">{{ __('Update') }}</button>
                        </div>
                    </div>

                    </div>
                </form>
            @endforeach
        </div>
  </div>
</div>

</div>

@endsection

