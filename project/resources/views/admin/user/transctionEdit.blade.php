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
    <div class="card tab-card">
      @include('admin.user.profiletab')

      <div class="tab-content" id="myTabContent">
        @include('includes.admin.form-success')
        @include('includes.admin.form-error')
        <div class="tab-pane fade show active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
        <div class="card-body">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
              <h6 class="m-0 font-weight-bold text-primary">{{ __('Update Transaction Form') }}</h6>
            </div>
            <form class="geniusform" action="{{route('admin-user.transaction-edit', $transaction->id)}}" method="POST" enctype="multipart/form-data">

              @include('includes.admin.form-both')

              {{ csrf_field() }}

              <div class="form-group">
                <label for="title">{{ __('Transaction Date') }}</label>
                <input type="text" class="form-control" id="transaction_date" name="transaction_date" placeholder="{{ __('Transaction Date') }}" value="{{ $transaction->created_at}}" readonly required>
              </div>

              <div class="form-group">
                <label for="trnx">{{ __('Transaction ID') }}</label>
                <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="trnx" name="trnx" placeholder="{{ __('Transaction Date') }}" value="{{ $transaction->trnx}}" readonly required>
              </div>

              <div class="form-group">
                <label for="description">{{ __('Description') }}</label>
                <input type="text" class="form-control" id="description" name="description" placeholder="{{ __('Enter Description') }}" value="{{ $transaction->details}}" required>
              </div>

              <div class="form-group">
                <label for="remark">{{ __('Remark') }}</label>
                <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="remark" name="remark" placeholder="{{ __('Enter Remark') }}" value="{{ $transaction->remark}}" required>
              </div>

              <div class="form-group">
                <label for="amount">{{ __('Amount') }} ({{$currency->code}})</label>
                <input type="number" class="form-control amount_check" id="amount" name="amount" placeholder="{{ __('0.00') }}" min="1" step="0.01" value="{{$transaction->amount}}" required>
              </div>

              <div class="form-group">
                <label for="charge">{{ __('Charge') }} ({{$currency->code}})</label>
                <input type="number" class="form-control amount_check" id="charge" name="charge" placeholder="{{ __('0.00') }}" step="0.01" value="{{ $transaction->charge}}">
              </div>

              <div class="form-group">
                <label for="sender">{{ __('Sender') }}</label>
                <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="sender" name="sender" placeholder="{{ __('Enter sender name') }}" value="{{__(json_decode($transaction->data)->sender ?? "")}}">
              </div>

              <div class="form-group">
                <label for="receiver">{{ __('Receiver') }}</label>
                <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="receiver" name="receiver" placeholder="{{ __('Enter receiver name') }}" value="{{__(json_decode($transaction->data)->receiver ?? "")}}">
              </div>

              <button type="submit" id="submit-btn" class="btn btn-primary w-100 mt-2">{{ __('Update') }}</button>

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
      $('.amount_check').keypress(function(event) {
          return isNumber(event, this)
      });

        function isNumber(evt, element) {
            var charCode = (evt.which) ? evt.which : event.keyCode

            if (
                (charCode != 45 || $(element).val().indexOf('-') != -1) && // “-” CHECK MINUS, AND ONLY ONE.
                (charCode != 46 || $(element).val().indexOf('.') != -1) && // “.” CHECK DOT, AND ONLY ONE.
                (charCode < 48 || charCode > 57))
                return false;

            return true;
        }

</script>

@endsection
