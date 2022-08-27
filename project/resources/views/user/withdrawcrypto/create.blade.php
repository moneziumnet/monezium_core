@extends('layouts.user')

@push('css')
<style>
    .document {
        display:none;
        }

        </style>
@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
        @include('user.ex_payment_tab')
      <div class="row align-items-center mt-3">
        <div class="col">
          <h2 class="page-title">
            {{__('Withdraw (Crypto)')}}
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
                    <form action="{{ route('user.cryptowithdraw.store') }}" method="post"  enctype="multipart/form-data">
                        @csrf
                        <div class="form-group mt-3">
                            <label class="form-label required">{{__('Select Crypto')}}</label>
                            <select name="currency_id" id="currency_id" class="form-select" required>
                                <option value="">{{ __('Select Crypto Currency') }}</option>
                                @foreach ($cryptocurrencies as $key => $currency)
                                        <option value="{{$currency->id}}">{{$currency->code}}</option>
                                @endforeach
                            </select>
                            <span class="ms-2 check"></span>
                        </div>

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Amount')}}</label>
                            <input name="amount" id="amount" class="form-control" autocomplete="off" placeholder="{{__('0.0')}}" type="number" step="any" value="{{ old('amount') }}" required>
                        </div>


                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Your Crypto Address')}}</label>
                            <input name="sender_address" id="sender_address" class="form-control" autocomplete="off" placeholder="{{__('0x....')}}" type="text" value="{{ old('sender_address') }}" required>
                        </div>


                        <input type="hidden" name="user_id" value="{{auth()->id()}}">

                        <div class="form-footer">
                            <button id="submit" class="btn btn-primary w-100">{{__('Submit')}}</button>
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
      $('#currency_id').on('click', function() {
          var url = '{{route('user.cryptodeposit.currency')}}';
          var token = '{{ csrf_token() }}';
          var data  = {id:$(this).val(),_token:token}
          $.post(url,data, function(res) {
              $('.check').text('@lang('Received Address is ')' + res).addClass('text-success');
          })
      })
  </script>

@endpush
