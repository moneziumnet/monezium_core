@extends('layouts.admin')
@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Crypto Withdraws') }}</h5>
    <ol class="breadcrumb py-0 m-0">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.withdraws.crypto.index') }}">{{ __('Crypto Withdraws') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.withdraws.crypto.edit', $withdraw->id) }}">{{ __('Edit Transaction') }}</a></li>
    </ol>
  </div>
</div>



<div class="row mt-3">
  <div class="col-lg-12">
    <div class="card tab-card">
      <div class="tab-content" id="myTabContent">
        @include('includes.admin.form-success')
        <div class="tab-pane fade show active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
        <div class="card-body">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
              <h6 class="m-0 font-weight-bold text-primary">{{ __('Add Hash Value') }}</h6>
            </div>
            <form class="geniusform" action="{{route('admin.withdraws.crypto.update', $withdraw->id)}}" method="POST" enctype="multipart/form-data">

              @include('includes.admin.form-both')

              {{ csrf_field() }}

              <div class="form-group">
                <label for="customer_name">{{ __('Customer Name') }}</label>
                <input type="text" pattern="[^()/><\][-;!|]+" class="form-control" id="customer_name" name="customer_name" value="{{ $withdraw->user->company_name ?? $withdraw->user->name}}" readonly required>
              </div>

              <div class="form-group">
                <label for="sender_address">{{ __('Customer Crypto Address') }}</label>
                <input type="text" pattern="[^()/><\][;!|]+" class="form-control" id="sender_address" name="sender_address" value="{{ $withdraw->sender_address}}" readonly required>
              </div>

              <div class="form-group">
                <label for="amount">{{ __('Amount') }}</label>
                <input type="number" step="any" class="form-control" id="amount" name="amount"  value="{{ $withdraw->amount.$withdraw->currency->code}}" readonly required>
              </div>

              <div class="form-group">
                <label for="hash">{{ __('Hash') }}</label>
                <input type="text" pattern="[^()/><\][;!|]+" class="form-control" id="hash" name="hash" placeholder="{{ __('Enter Hash Value') }}" value="{{$withdraw->hash}}" required>
              </div>

              <button type="submit" id="submit-btn" class="btn btn-primary w-100 mt-2">{{ __('Save') }}</button>

              </form>
        </div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>


<!--Row-->
@endsection
@section('scripts')

<script type="text/javascript">
	"use strict";
</script>

@endsection
