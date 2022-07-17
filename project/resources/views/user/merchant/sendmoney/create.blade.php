@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Send Money')}}
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

                    <div class="card-body">
                      <div class="tab-content">
                        <div class="tab-pane active show" id="other-account">
                            @includeIf('includes.flash')
                            <form action="{{route('user.merchant.send.money.store')}}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <div class="form-group mb-3 mt-3">
                                    <label class="form-label required">{{__('Account Email')}}</label>
                                    <input name="email" id="email" class="form-control" autocomplete="off" placeholder="{{__('user@mail.com')}}" type="text" value="{{ $savedUser ? $savedUser->email : '' }}" min="1" required>
                                </div>


                                <div class="form-group mb-3 mt-3">
                                    <label class="form-label required">{{__('Account Name')}}</label>
                                    <input name="account_name" id="account_name" class="form-control" autocomplete="off" placeholder="{{__('Jhon Doe')}}" type="text" value="{{ $savedUser ? $savedUser->name : '' }}" min="1" required readonly>
                                </div>

                                <div class="form-group mb-3 mt-3">
                                    <label class="form-label required">{{__('Account PhoneNumber')}}</label>
                                    <input name="account_phone" id="account_phone" class="form-control" autocomplete="off" placeholder="{{__('+1234567890')}}" type="text" value="{{ $savedUser ? $savedUser->phone : '' }}" min="1" required readonly>
                                </div>


                                <div class="form-group mb-3 mt-3">
                                    <label class="form-label required">{{__('Select Wallet')}}</label>
                                    <select name="wallet_id" id="wallet_id" class="form-control" required>
                                      <option value="">Select</option>
                                      @if(!empty($wallets))
                                        @foreach($wallets as $wallet)
                                          <option value="{{$wallet->id}}">{{$wallet->currency->code}} --  ({{amount($wallet->balance,$wallet->currency->type,2)}})</option>

                                        @endforeach
                                      @endif
                                    </select>

                                </div>

                                <div class="form-group mt-3">
                                    <label class="form-label required">{{__('Amount')}}</label>
                                    <input name="amount" id="amount" class="form-control" autocomplete="off" placeholder="{{__('0.0')}}" type="number" step="any" value="{{ old('amount') }}" min="1" required>
                                </div>

                                <div class="form-group mt-3">
                                    <label class="form-label required">{{__('Description')}}</label>
                                    <textarea name="description" id="description" class="form-control" placeholder="{{__('Enter description')}}" rows="5" required></textarea>
                                </div>

                                <div class="form-group mt-3">
                                    <label class="form-label required">{{__('Google Authenticator Code')}}</label>
                                    <input type="hidden" name="key" value="{{$secret}}">
                                    <input type="text" class="form-control" name="code" required placeholder="@lang('Enter Google Authenticator Code')">
                                </div>

                                <div class="form-footer">
                                    <button type="submit" class="btn btn-primary w-100">{{__('Submit')}}</button>
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
                                    <a href="{{ route('user.merchant.send.money.savedUser',$reciver->email) }}">
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

  $("#email").on('change',function(){
    console.log('change');
    let accountemail = $("#email").val();

    let url = `${mainurl}/user/username-by-email/${accountemail}`;

    $.get(url, function(data){
      $("#account_name").val(data['name']);
      $("#account_phone").val(data['phone']);
    });
  })
</script>
@endpush
