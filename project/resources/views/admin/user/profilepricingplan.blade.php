@extends('layouts.admin')
@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ $data->name }}</h5>
    <ol class="breadcrumb py-0 m-0">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.user.index') }}">{{ __('User List') }}</a></li>
    </ol>
  </div>
</div>


<div class="row mt-3">
  <div class="col-lg-12">
    <div class="card mt-1 tab-card">
      @include('admin.user.profiletab')

      <div class="tab-content" id="myTabContent">
        @php
        $currency = defaultCurr();
        @endphp
        @include('includes.admin.form-success')
        <div class="tab-pane fade show p-3 active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
          <div class="card-body">
              <div class="card-header">
                <h4>{{$plan->name}}</h4>
              </div>

              <div class="row mb-3">
                <div class="col-xl-3 col-md-6 mb-4">
                  <div class="card h-100">
                    <div class="card-body">
                      <div class="row align-items-center">
                        <div class="col mr-2">
                          <div class="text-xs font-weight-bold text-uppercase mb-1"> </div>
                          <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">Price {{ showprice($plan->price,$currency) }}  </div>
                          <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">Duration {{$plan->duration}} {{$plan->durationtype}}  </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
          </div>

          
          <div class="card-body">
              <div class="card-header">
                <h4>{{__('Upgrade Plan')}}</h4>
              </div>
            <div class="row mb-3">
             
              <div class="col-xl-12 col-md-6 mb-4">
                <form class="geniusform" action="{{ route('admin-user-upgrade-plan',$data->id) }}" method="POST" enctype="multipart/form-data">
                  @include('includes.admin.form-both')
                  {{ csrf_field() }}
                    <div class="form-group">
                      <label for="inp-name">{{ __('Subscription Type') }}</label>
                        <select class="form-control" name="subscription_type" id="subscription_type">
                          <option value="">{{ __('Select Subscription Type') }}</option>
                          @foreach($plans as $plan)
                          <option value="{{ $plan->id }}">{{ $plan->name }} {{ showprice($plan->price,$currency) }}</option>
                          @endforeach
                        </select>
                    </div>
                  <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Upgrade') }}</button>
                </form>
              </div>
            </div>
          </div>
         
        </div>
      </div>
    </div>
  </div>
</div>
</div>
<!--Row-->
@endsection