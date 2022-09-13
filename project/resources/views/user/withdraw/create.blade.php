@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
    @include('user.ex_payment_tab')
      <div class="row align-items-center mt-3">
        <div class="col">
          <h2 class="page-title">
            {{__('Withdraw Now')}}
          </h2>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card p-5">
                    <div class="gocover" style="background: url({{ asset('assets/images/'.$gs->loader) }}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>

                        @if ($gs->withdraw_status == 0)
                            <p class="text-center text-danger">{{__('WithDraw is temporary Off')}}</p>
                        @else
                            @includeIf('includes.flash')
                            <form action="{{route('user.withdraw.store')}}" method="POST" id="withdraw_form" enctype="multipart/form-data">
                                @csrf

                                <div class="form-group">
                                    <label class="form-label required">{{__('Institution')}}</label>
                                    <select name="subinstitude" id="subinstitude" class="form-select" required>
                                        <option value="">{{ __('Select Institution') }}</option>
                                        @foreach ($subinstitude as $ins)
                                            <option value="{{$ins->id}}">{{$ins->name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group mt-3">
                                    <label class="form-label required">{{__('Withdraw Method')}}</label>
                                    <select name="methods" id="withmethod" class="form-select" required>
                                        <option value="">{{ __('Select Withdraw Method') }}</option>
                                    </select>
                                </div>

                                <div class="form-group mt-3">
                                    <label class="form-label required">{{__('Withdraw Currency')}}</label>
                                    <select name="currency_id" id="withcurrency" class="form-select" required>
                                        <option value="">{{ __('Select Withdraw Currency') }}</option>
                                    </select>
                                </div>

                                <div class="form-group mb-3 mt-3">
                                    <label class="form-label required">{{__('Withdraw Amount')}}</label>
                                    <input name="amount" id="amount" class="form-control" autocomplete="off" placeholder="{{__('0.0')}}" type="number" step="any" value="{{ old('amount') }}" min="1" required>
                                </div>

                                <div class="form-group mb-3 ">
                                    <label class="form-label required">{{__('Description')}}</label>
                                    <textarea name="details" id="details" class="form-control nic-edit" cols="30" rows="5" placeholder="{{__('Receive account details')}}" required></textarea>
                                </div>
                                <input name="otp" id="otp" type="hidden" value="">

                                <div class="form-footer">
                                    <button type="submit" id="form_submit" class="btn btn-primary w-100">{{__('Submit')}}</button>
                                </div>


                            </form>
                        @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-blur fade" id="modal-verify" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="modal-status bg-primary"></div>
        <div class="modal-body py-4">
            <div class="text-center">

                <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                <h3>@lang('Withdraw Details')</h3>
            </div>
            <ul class="list-group mt-2">
                <li class="list-group-item d-flex justify-content-between">@lang('Institution Name')<span id="institution_name"></span></li>
                <li class="list-group-item d-flex justify-content-between">@lang('Payment Method')<span id="py_method"></span></li>
                <li class="list-group-item d-flex justify-content-between">@lang('Currency')<span id="py_currency"></span></li>
                <li class="list-group-item d-flex justify-content-between">@lang('Amount')<span id="py_amount"></span></li>
                <li class="list-group-item d-flex justify-content-between">@lang('Description')<span id="py_description"></span></li>
            </ul>
            <div class="modal-body">
              <div class="form-group" id="otp_body">
                <label class="form-label required">{{__('OTP Code')}}</label>
                <input name="otp_code" id="otp_code" class="form-control" placeholder="{{__('OTP Code')}}" type="text" step="any" value="{{ old('opt_code') }}" required>
              </div>

            </div>

            <div class="modal-footer">
                <button  id="submit-btn" class="btn btn-primary">{{ __('Verify') }}</button>
            </div>
      </div>
      </div>
    </div>
  </div>

@endsection

@push('js')

<script type="text/javascript">
    $("#subinstitude").on('click',function(){
        let subinstitude = $("#subinstitude").val();
        $.post("{{ route('user.withdraw.gateway') }}",{id:subinstitude,_token:'{{csrf_token()}}'},function (res) {
            let _optionHtml = '<option value="">Select Withdraw Method</option>';
            $.each(res, function(i, item) {
                _optionHtml += '<option value="' + item.id + '">' + item.name + '</option>';
            });
            $('select#withmethod').html(_optionHtml);
        })
    });

    $("#withmethod").on('change',function(){
        let paymentid = $("#withmethod").val();
        let subinstitude = $("#subinstitude").val();
        $.post("{{ route('user.withdraw.gatewaycurrency') }}",{id:paymentid,_token:'{{csrf_token()}}'},function (res) {
            let _optionHtml = '<option value="">Select Payment Currency</option>';
            $.each(res, function(i,item) {
                _optionHtml += '<option value="' + item.currency.id + '">' + item.currency.code + '  --  (' + parseFloat(item.balance).toFixed(2) + ')' + '</option>';
            });
            $('select#withcurrency').html(_optionHtml);
        })
    });
    $(document).ready(function(){
        $('#form_submit').on('click', function(event){
            var verify = "{{$user->paymentCheck('Withdraw')}}";
            event.preventDefault();
            $('#institution_name').text($('#subinstitude option:selected').text());
            $('#py_method').text($('#withmethod option:selected').text());
            $('#py_currency').text($('#withcurrency option:selected').text());
            $('#py_amount').text($('#amount').val());
            $('#py_description').text($('#details').val());
            if (verify) {
                var url = "{{url('user/sendotp')}}";
                $.get(url,function (res) {
                    if(res=='success') {
                        $('#modal-verify').modal('show');
                    }
                    else {
                        alert('The OTP code can not be sent to you.')
                    }
                });
            } else {
                $('#otp_body').remove();
                $('#modal-verify').modal('show');
            }
        })
        $('#submit-btn').on('click', function(){
            var verify = "{{$user->paymentCheck('Withdraw')}}";
            if(verify) {
                if($('#otp_code').val()) {

                    $('#otp').val($('#otp_code').val());
                    $("#withdraw_form").submit();
                }
                else {
                    alert('Please input OTP code.');
                }
            }
            else {
                $("#withdraw_form").submit();
            }

        })
    });

</script>
@endpush
