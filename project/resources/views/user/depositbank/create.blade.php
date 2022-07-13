@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Deposit Now (Bank)')}}
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
                        <div class="form-group">
                            <label class="form-label required">{{__('Institution')}}</label>
                            <select name="subinstitude" id="subinstitude" class="form-select" required>
                                <option value="">{{ __('Select Institution') }}</option>

                                @foreach ($subinstitude as $ins)
                                        <option value="{{$ins->id}}">{{ $ins->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mt-3">
                            <label class="form-label required">{{__('Bank')}}</label>
                            <select name="method" id="withmethod" class="form-select" required>
                                <option value="">{{ __('Select Bank') }}</option>
                            </select>
                        </div>


                        <input type="hidden" name="currency_sign" value="$">
                        <input type="hidden" id="currencyCode" name="currency_code" value="USD">
                        <input type="hidden" name="currency_id" value="1">


                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Deposit Amount (USD)')}}</label>
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

    $("#subinstitude").on('click',function(){
        let subinstitude = $("#subinstitude").val();
        $.post("{{ route('user.depositbank.list') }}",{id:subinstitude,_token:'{{csrf_token()}}'},function (res) {
            let _optionHtml = '<option value="">Select Bank</option>';
            $.each(res, function(i, item) {
                _optionHtml += '<option value="' + item.name + '(Bank)' + '">' + item.name + '</option>';
            });
            $('select#withmethod').html(_optionHtml);
        })
    });

  </script>

@endpush
