@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Active Two factor authentication')}}
          </h2>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                {{-- @if(Auth::user()->twofa) --}}
                    {{-- <div class="card border-0 shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title text-dark text-center">@lang('Two Factor Authenticator')</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group mx-auto text-center">
                                <a href="javascript:void(0)"  class="btn w-100 btn-md btn--danger" data-bs-toggle="modal" data-bs-target="#disableModal">
                                    @lang('Disable Two Factor Authenticator')</a>
                            </div>
                        </div>
                    </div> --}}
                {{-- @else --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title text-dark text-center">@lang('Two Factor Authenticator')</h5>
                        </div>
                        <div class="card-body">
                            @includeIf('includes.flash')
                            {{-- <form action="{{route('send.money.store-two-auth')}}" method="POST" enctype="multipart/form-data"> --}}
                            <form action="{{route('user.securityform')}}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group mb-3 mt-3">
                                    <input type="checkbox" name="login_fa_yn" id="login_fa_yn" value="Y" @if($user->login_fa_yn == "Y") checked @endif> {{__('Login With 2FA Authorization')}}
                                </div>
                                <div class="form-group mb-3 mt-3">
                                    <label class="form-label">{{__('Select 2FA Option')}}</label>
                                    <select name="login_fa" id="login_fa" class="form-control">
                                      <option value="">Select</option>
                                        <option value="two_fa_email" @if($user->login_fa == "two_fa_email") selected @endif>Two FA by Email</option>
                                        <option value="two_fa_phone" @if($user->login_fa == "two_fa_phone") selected @endif>Two FA by Phone</option>
                                        <option value="two_fa_google" @if($user->login_fa == "two_fa_google") selected @endif>Two FA by Google</option>
                                    </select>

                                </div>

                                <div class="form-group mb-3 mt-3">
                                    <input type="checkbox" name="payment_fa_yn" id="payment_fa_yn" value="Y"  @if($user->payment_fa_yn == "Y") checked @endif> {{__('Payments with 2FA Authorization')}}
                                </div>
                                <div class="form-group mb-3 mt-3">
                                    <label class="form-label">{{__('Select 2FA Option')}}</label>
                                    <select name="payment_fa" id="payment_fa" class="form-control">
                                      <option value="">Select</option>
                                        <option value="two_fa_email" @if($user->payment_fa == "two_fa_email") selected @endif>Two FA by Email</option>
                                        <option value="two_fa_phone" @if($user->payment_fa == "two_fa_phone") selected @endif>Two FA by Phone</option>
                                        <option value="two_fa_google" @if($user->payment_fa == "two_fa_google") selected @endif>Two FA by Google</option>
                                    </select>

                                </div>
                                <div class="row" id="check_box">
                                    <div class="form-group mb-3 mt-3 col-md-4">
                                        <input type="checkbox" name="otp_payment[]" value="Bank Incoming"  @if($user->paymentcheck('Bank Incoming')) checked @endif> {{__('Bank Incoming')}}
                                    </div>
                                    <div class="form-group mb-3 mt-3 col-md-4">
                                        <input type="checkbox" name="otp_payment[]" value="Payment Gateway Incoming"  @if($user->paymentcheck('Payment Gateway Incoming')) checked @endif> {{__('Payment Gateway Incoming')}}
                                    </div>
                                    <div class="form-group mb-3 mt-3 col-md-4">
                                        <input type="checkbox" name="otp_payment[]" value="Crypto Incoming"  @if($user->paymentcheck('Crypto Incoming')) checked @endif> {{__('Crypto Incoming')}}
                                    </div>
                                    <div class="form-group mb-3 mt-3 col-md-4">
                                        <input type="checkbox" name="otp_payment[]" value="Withdraw"  @if($user->paymentcheck('Withdraw')) checked @endif> {{__('Withdraw')}}
                                    </div>
                                    <div class="form-group mb-3 mt-3 col-md-4">
                                        <input type="checkbox" name="otp_payment[]" value="External Payments"  @if($user->paymentcheck('External Payments')) checked @endif> {{__('External Payments')}}
                                    </div>
                                    <div class="form-group mb-3 mt-3 col-md-4">
                                        <input type="checkbox" name="otp_payment[]" value="Withdraw Crypto"  @if($user->paymentcheck('Withdraw Crypto')) checked @endif> {{__('Withdraw Crypto')}}
                                    </div>
                                    <div class="form-group mb-3 mt-3 col-md-4">
                                        <input type="checkbox" name="otp_payment[]" value="Payment between accounts"  @if($user->paymentcheck('Payment between accounts')) checked @endif> {{__('Payment between accounts')}}
                                    </div>
                                    <div class="form-group mb-3 mt-3 col-md-4">
                                        <input type="checkbox" name="otp_payment[]" value="Internal Payment"  @if($user->paymentcheck('Internal Payment')) checked @endif> {{__('Internal Payment')}}
                                    </div>
                                    <div class="form-group mb-3 mt-3 col-md-4">
                                        <input type="checkbox" name="otp_payment[]" value="Request Money"  @if($user->paymentcheck('Request Money')) checked @endif> {{__('Request Money')}}
                                    </div>
                                    <div class="form-group mb-3 mt-3 col-md-4">
                                        <input type="checkbox" name="otp_payment[]" value="Exchange"  @if($user->paymentcheck('Exchange')) checked @endif> {{__('Exchange')}}
                                    </div>
                                </div>

                                <div class="form-footer">
                                    <button type="submit" class="btn btn-primary w-100">{{__('Submit')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                {{-- @endif --}}
            </div>
        </div>

        <!--Enable Modal -->
        {{-- <div id="enableModal" class="modal modal-blur fade" role="dialog" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ">
                <!-- Modal content-->
                <div class="modal-content ">
                    <div class="modal-header">
                        <h4 class="modal-title">@lang('Verify Your Otp')</h4>
                    </div>
                    <form action="{{route('user.createTwoFactor')}}" method="POST">
                        @csrf
                        <div class="modal-body ">
                            <div class="form-group">
                                <input type="hidden" name="key" value="{{$secret}}">
                                <input type="text" class="form-control" name="code" placeholder="@lang('Enter Google Authenticator Code')">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">@lang('close')</button>
                            <button type="submit" class="btn btn-success">@lang('verify')</button>
                        </div>
                    </form>
                </div>

            </div>
        </div> --}}

        <!--Disable Modal -->
        {{-- <div id="disableModal" class="modal modal-blur fade" role="dialog" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">@lang('Verify Your Otp Disable')</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="{{route('user.disableTwoFactor')}}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <input type="text" class="form-control" name="code" placeholder="@lang('Enter Google Authenticator Code')">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">@lang('Close')</button>
                            <button type="submit" class="btn btn-success">@lang('Verify')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> --}}
    </div>
</div>


@endsection

@push('js')

<script>
    "use strict";
    function myFunction() {
        var copyText = document.getElementById("referralURL");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        alert('copied');
    }

    $(document).ready(function() {
    $('#payment_fa_yn').click(function() {
        var checked = $(this).prop('checked');
        $('#check_box').find('input:checkbox').prop('checked', checked);
    });
    })
</script>

<script src="{{asset('assets/user/js/sweetalert2@9.js')}}"></script>

    @if($errors->any())
        @foreach ($errors->all() as $error)
            <script>
                const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                onOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
            })
                Toast.fire({
                icon: 'error',
                title: '{{ $error }}'
                })
            </script>
        @endforeach
    @endif


    @if(Session::has('success'))
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            onOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
    })
    Toast.fire({
        icon: 'success',
        title: '{{Session::get('success')}}'
    })
  </script>
@endif

@endpush
