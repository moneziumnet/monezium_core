@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
        @include('user.deposittab')
      <div class="row align-items-center mt-3">
        <div class="col">
          <h2 class="page-title">
            {{__('Incoming (Bank)')}}
          </h2>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card p-3 p-sm-4 p-lg-5">
                    @includeIf('includes.flash')
                    <form id="deposit-form" action="{{route('user.depositbank.store')}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group mt-3">
                            <label class="form-label required">{{__('Bank')}}</label>
                            <select name="method" id="withmethod" class="form-select" required>
                                <option value="">{{ __('Select Bank') }}</option>
                                @foreach ($banks as $bank)
                                        <option value="{{$bank->name}}">{{$bank->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <input type="hidden" name="currency_sign" value="$">
                        <input type="hidden" id="currencyCode" name="currency_code" value="USD">
                        <input type="hidden" name="currency_id" value="1">


                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Incoming Amount (USD)')}}</label>
                            <input name="amount" id="amount" class="form-control" autocomplete="off" placeholder="{{__('0.0')}}" type="number" step="any" value="{{ old('amount') }}" min="1" required>
                        </div>

                        <div class="form-group mb-3 ">
                            <label class="form-label">{{__('Description')}}</label>
                            <textarea name="details" class="form-control nic-edit" cols="30" rows="5" placeholder="{{__('Receive account details')}}"></textarea>
                        </div>

                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary w-100">{{__('Submit')}}</button>
                        </div>


                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@push('js')

  <script type="text/javascript">
  'use strict';
  </script>

@endpush
