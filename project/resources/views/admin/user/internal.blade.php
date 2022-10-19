@extends('layouts.admin')
@section('content')
    <div class="card">
        <div class="d-sm-flex align-items-center justify-content-between py-3">
            @php
                $accounttype = ['0' => 'All', '1' => 'Current', '2' => 'Card', '3' => 'Deposit', '4' => 'Loan', '5' => 'Escrow', '6' => 'Supervisor', '7' => 'Merchant', '8' => 'Crypto', '9' => 'System', '10' => 'Manager'];
                $dcurr = App\Models\Currency::findOrFail($wallet->currency_id);
            @endphp
            <h5 class="mb-0 text-gray-800 pl-3">
                <strong class="mr-3">{{ $accounttype[$wallet->wallet_type] }} {{ $wallet->wallet_no }}</strong>
                ({{ $dcurr->symbol }} {{ amount($wallet->balance, $dcurr->type, 2) }} {{ $dcurr->code }})
            </h5>
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
                    <h3 class="text-center my-3">Internal Payment</h3>
                    @include('includes.admin.form-success')
                    <div class="tab-pane fade show active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
                        <div class="card-body col-md-6 mx-auto">
                            <form action="{{route('admin-wallet-internal-send', [$wallet->user_id, $wallet->id])}}" method="post" id="internal_form">
                                @csrf
                                <div class="form-group mb-3 mt-3">
                                    <label class="form-label required">{{__('Account Email')}}</label>
                                    <div class="input-group">
                                        <input name="email" id="email" class="form-control camera_value" autocomplete="off" type="email" value="" required>
                                    </div>
                                </div>

                                <div class="form-group mb-3 mt-3">
                                    <label class="form-label required">{{__('Account Name')}}</label>
                                    <input name="account_name" id="account_name" class="form-control" autocomplete="off" type="text" value="" required readonly>
                                </div>

                                <div class="form-group mt-3">
                                    <label class="form-label required">{{__('Amount')}}</label>
                                    <input name="amount" id="amount" class="form-control" autocomplete="off" type="number" step="any" value="{{ old('amount') }}" required>
                                </div>

                                <div class="form-group mt-3">
                                    <label class="form-label required">{{__('Description')}}</label>
                                    <textarea name="description" id="description" class="form-control" rows="5" required></textarea>
                                </div>

                                <input type="hidden" name="wallet_id" value="{{$wallet->id}}" />

                                <div class="form-footer">
                                    <button type="submit" id="btn_submit" class="btn btn-primary w-100">{{__('Submit')}}</button>
                                </div>
                                <div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="modal-body py-4">
                                                <div class="text-center">
                                                    <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                                                    <h3>@lang('Details')</h3>
                                                </div>
                                                <ul class="list-group details-list mt-2">
                                                    <li class="list-group-item">@lang('Receiver Name')<span id="receiver_name"></span></li>
                                                    <li class="list-group-item">@lang('Receiver Email')<span id="receiver_email"></span></li>
                                                    <li class="list-group-item">@lang('Amount')<span id="re_amount"></span></li>
                                                    <li class="list-group-item">@lang('Description')<span id="re_description"></span></li>
                                                </ul>
                                            </div>
                                            <div class="modal-footer">
                                                <div class="w-100">
                                                    <div class="row">
                                                        <div class="col">
                                                            <a href="#" class="btn w-100" data-dismiss="modal">@lang('Cancel')</a>
                                                        </div>
                                                        <div class="col">
                                                            <button type="submit" class="btn btn-primary w-100 confirm">@lang('Confirm')</button>
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
    </div>

    <!--Row-->
@endsection
@section('scripts')
    <script type="text/javascript">
        "use strict";
        var send_money_user = null;
        $("#email").on('change',function(){
            $.post("{{ route('admin.username.email') }}",{email: $("#email").val(),_token:'{{csrf_token()}}'}, function(data){
            send_money_user = data;
            if(data['name']){
                $("#account_name").val(data['name']);
            } else {
                $("#account_name").val("");
                toastr.options =
                {
                    "closeButton" : true,
                    "progressBar" : true
                }
                toastr.error("This uer doesn't exist.");
            }
            });
        })
        $('#btn_submit').on('click', function(event) {
            if(!document.getElementById('internal_form').checkValidity()){
                return;
            }
            event.preventDefault();
            if(!send_money_user['name']) {
                toastr.options =
                {
                    "closeButton" : true,
                    "progressBar" : true
                }
                toastr.error("This uer doesn't exist.Try again.");
                $("#email").focus();
                return;
            }
            if (($('#email').val().length != 0) && 
                ($('#amount').val().length != 0) && 
                ($('#account_name').val().length != 0)  && 
                ($('#description').val().length != 0)
            ) {
                $('#receiver_email').text($('#email').val());
                $('#receiver_name').text($('#account_name').val());
                $('#re_amount').text('{{$wallet->currency->symbol}}' + $('#amount').val() + ' {{$wallet->currency->code}}');
                $('#re_description').text($('#description').val());
                $('#modal-success').modal('show')
            }
        })
    </script>
@endsection
