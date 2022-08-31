@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Request Now')}}
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
                    @includeIf('includes.flash')
                    <form id="request-form" action="{{ route('user.money.request.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Account Email')}}</label>
                            <div class="input-group">
                                <input name="account_email" id="account_email" class="form-control camera_value" autocomplete="off" placeholder="{{__('user@gmail.com')}}" type="text" value="{{ old('account_email') }}" min="1" required>
                                <button type="button"  data-bs-toggle="tooltip" data-bs-original-title="@lang('Scan QR code')" class="input-group-text scan"><i class="fas fa-qrcode"></i></button>
                            </div>
                        </div>

                        <div class="form-group mb-3 mt-3">
                          <label class="form-label required">{{__('Select Currency')}}</label>
                          <select name="wallet_id" id="wallet_id" class="form-control" required>
                            <option value="">Select</option>
                            @foreach(DB::table('currencies')->get() as $currency)
                            <option value="{{$currency->id}}">{{$currency->code}}</option>
                            @endforeach
                          </select>

                      </div>

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Account Name')}}</label>
                            <input name="account_name" id="account_name" class="form-control" autocomplete="off" placeholder="{{__('Jhon Doe')}}" type="text" value="{{ old('account_name') }}" min="1" required >
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label required">{{__('Request Amount')}}</label>
                            <input name="amount" id="amount" class="form-control" autocomplete="off" placeholder="{{__('0.0')}}" type="number" step="any" value="{{ old('amount') }}" min="1" required>
                        </div>

                        <div class="form-group mb-3 ">
                            <label class="form-label required">{{__('Description')}}</label>
                            <textarea name="details" id="details" class="form-control nic-edit" cols="30" rows="5" placeholder="{{__('Receive account details')}}" required></textarea>
                        </div>

                        <div class="form-footer">
                            <button type="submit" id="submit" class="btn btn-primary submit-btn w-100" >{{__('Submit')}}</button>
                        </div>
                        <div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                <div class="modal-status bg-primary"></div>
                                <div class="modal-body py-4">
                                    <div class="text-center">

                                        <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                                        <h3>@lang('Details')</h3>
                                    </div>
                                    <ul class="list-group mt-2">
                                        <li class="list-group-item d-flex justify-content-between">@lang('Receiver Name')<span id="receiver_name"></span></li>
                                        <li class="list-group-item d-flex justify-content-between">@lang('Receiver Email')<span id="receiver_email"></span></li>
                                        <li class="list-group-item d-flex justify-content-between">@lang('Currency')<span id="currency"></span></li>
                                        <li class="list-group-item d-flex justify-content-between">@lang('Amount')<span id="re_amount"></span></li>
                                        <li class="list-group-item d-flex justify-content-between">@lang('Description')<span id="re_description"></span></li>
                                    </ul>

                                    <div class="form-group mt-3" id="otp_body">
                                        <label class="form-label required">{{__('OTP Code')}}</label>
                                        <input name="otp_code" id="otp_code" class="form-control" placeholder="{{__('OTP Code')}}" type="text" step="any" value="{{ old('opt_code') }}" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                <div class="w-100">
                                    <div class="row">
                                    <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">
                                        @lang('Cancel')
                                        </a></div>
                                    <div class="col">
                                        <button type="submit" class="btn btn-primary w-100 confirm">
                                        @lang('Confirm')
                                        </button>
                                    </div>
                                    </div>
                                </div>
                                </div>
                            </div>
                            </div>
                        </div>


                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@push('js')
<script>
  'use strict';
  $("#account_email").on('change',function(){
      $.post("{{ route('user.username.email') }}",{email: $("#account_email").val(),_token:'{{csrf_token()}}'}, function(data){
        $("#account_name").val(data['name']);
      });
    })

    $('#submit').on('click', function() {
        if (($('#account_email').val().length != 0) && ($('#wallet_id').val().length != 0) && ($('#amount').val().length != 0) && ($('#account_name').val().length != 0)  && ($('#details').val().length != 0)) {
            var verify = "{{$user->payment_fa_yn}}";
            event.preventDefault();
            $('#receiver_email').text($('#account_email').val());
            $('#receiver_name').text($('#account_name').val());
            $('#currency').text($('#wallet_id option:selected').text().split('--')[0]);
            $('#re_amount').text($('#amount').val());
            $('#re_description').text($('#details').val());
            if (verify == 'Y') {
                var url = "{{url('user/sendotp')}}";
                $.get(url,function (res) {
                    console.log(res)
                    if(res=='success') {
                        $('#modal-success').modal('show');
                    }
                    else {
                        alert('The OTP code can not be sent to you.')
                    }
                });
            } else {
                $('#otp_body').remove();
                $('#modal-success').modal('show');
            }
            $('#modal-success').modal('show')
        }
      })
</script>
@endpush
