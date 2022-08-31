@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Internal Payment')}}
          </h2>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card p-4">
                    <ul class="nav nav-tabs nav-fill" data-bs-toggle="tabs">
                      <li class="nav-item">
                        <a href="#other-account" class="nav-link active" data-bs-toggle="tab">{{__('Other Account')}}</a>
                      </li>
                      <li class="nav-item">
                        <a href="#saved-account" class="nav-link" data-bs-toggle="tab">{{__('Saved Account')}}</a>
                      </li>
                    </ul>
                    @php
                        $userType = explode(',', auth()->user()->user_type);
                        $supervisor = DB::table('customer_types')->where('type_name', 'Supervisors')->first()->id;
                        $merchant = DB::table('customer_types')->where('type_name', 'Merchants')->first()->id;
                        $wallet_type_list = array('0'=>'All', '1'=>'Current', '2'=>'Card', '3'=>'Deposit', '4'=>'Loan', '5'=>'Escrow', '8'=>'Crypto');
                        if(in_array($supervisor, $userType)) {
                            $wallet_type_list['6'] = 'Supervisor';
                        }
                        elseif (DB::table('managers')->where('manager_id', auth()->id())->first()) {
                            $wallet_type_list['10'] = 'Manager';
                        }
                        if(in_array($merchant, $userType)) {
                            $wallet_type_list['7'] = 'Merchant';
                        }

                    @endphp
                    <div class="card-body">
                      <div class="tab-content">
                        <div class="tab-pane active show" id="other-account">
                            @includeIf('includes.flash')
                            {{-- <form action="{{route('send.money.store-two-auth')}}" method="POST" enctype="multipart/form-data"> --}}
                            <form action="{{route('send.money.store')}}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <div class="form-group mb-3 mt-3">
                                    <label class="form-label required">{{__('Account Email')}}</label>
                                    <div class="input-group">
                                        <input name="email" id="email" class="form-control camera_value" autocomplete="off" placeholder="{{__('user@email.com')}}" type="email" value="{{ $savedUser ? $savedUser->email : '' }}" min="1" required>
                                        <button type="button"  data-bs-toggle="tooltip" data-bs-original-title="@lang('Scan QR code')" class="input-group-text scan"><i class="fas fa-qrcode"></i></button>
                                    </div>
                                </div>

                                <div class="form-group mb-3 mt-3">
                                    <label class="form-label required">{{__('Select Wallet')}}</label>
                                    <select name="wallet_id" id="wallet_id" class="form-control" required>
                                      <option value="">Select</option>
                                      @if(!empty($wallets))
                                        @foreach($wallets as $wallet)
                                        @if (isset($wallet_type_list[$wallet->wallet_type]))
                                            <option value="{{$wallet->id}}">{{$wallet->currency->code}} --  ({{amount($wallet->balance,$wallet->currency->type,2)}}) --{{$wallet_type_list[$wallet->wallet_type]}} </option>
                                        @endif

                                        @endforeach
                                      @endif
                                    </select>

                                </div>

                                <div class="form-group mb-3 mt-3">
                                    <label class="form-label required">{{__('Account Name')}}</label>
                                    <input name="account_name" id="account_name" class="form-control" autocomplete="off" placeholder="{{__('Jhon Doe')}}" type="text" value="{{ $savedUser ? $savedUser->name : '' }}" min="1" required readonly>
                                </div>

                                <div class="form-group mt-3">
                                    <label class="form-label required">{{__('Amount')}}</label>
                                    <input name="amount" id="amount" class="form-control" autocomplete="off" placeholder="{{__('0.0')}}" type="number" step="any" value="{{ old('amount') }}" min="1" required>
                                </div>

                                <div class="form-group mt-3">
                                    <label class="form-label required">{{__('Description')}}</label>
                                    <textarea name="description" id="description" class="form-control" placeholder="{{__('Enter description')}}" rows="5" required></textarea>
                                </div>


                                <div class="form-footer">
                                    <button type="submit" id="submit"  class="btn btn-primary w-100">{{__('Submit')}}</button>
                                </div>


                                <div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        <div class="modal-status bg-primary"></div>
                                        <div class="modal-body py-4">
                                            <div class="text-center">

                                                <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                                                <h3>@lang('Deposit Details')</h3>
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
                        <div class="tab-pane" id="saved-account">
                          <div class="row g-3">
                            @if (count($saveAccounts) == 0)
                                <p class="text-center">{{__('NO SAVED ACCOUNT FOUND')}}</p>
                              @else
                              @foreach ($saveAccounts as $key=>$data)
                              @php
                                  $reciver = App\Models\User::whereId($data->receiver_id)->first();
                              @endphp
                                @if ($reciver)
                                  <div class="col-6">
                                    <a href="{{ route('send.money.savedUser',$reciver->email) }}">
                                      <div class="row g-3 align-items-center">
                                        <span class="col-auto">
                                          <span class="avatar" style="background-image: url({{ asset('assets/images/'.$reciver->photo) }})">
                                            <span class="badge bg-red"></span></span>
                                        </span>
                                        <div class="col text-truncate">
                                          <span>{{$reciver->name}}</span>
                                          <br>
                                          <small class="text-muted text-truncate mt-n1">{{ $reciver->email }}</small>
                                        </div>
                                      </div>
                                    </a>
                                  </div>
                                @endif
                              @endforeach
                            @endif

                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
            </div>
        </div>
    </div>
</div>


@endsection

@push('js')
<script>
  'use strict';

  $("#account_name").on('click',function(){
    $.post("{{ route('user.username.email') }}",{email: $("#email").val(),_token:'{{csrf_token()}}'}, function(data){
      $("#account_name").val(data['name']);
    });
  })

  $('#submit').on('click', function() {
        if (($('#email').val().length != 0) && ($('#wallet_id').val().length != 0) && ($('#amount').val().length != 0) && ($('#account_name').val().length != 0)  && ($('#description').val().length != 0)) {
            var verify = "{{$user->payment_fa_yn}}";
            event.preventDefault();
            $('#receiver_email').text($('#email').val());
            $('#receiver_name').text($('#account_name').val());
            $('#currency').text($('#wallet_id option:selected').text().split('--')[0]);
            $('#re_amount').text($('#amount').val());
            $('#re_description').text($('#description').val());
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
