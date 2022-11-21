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
                    <div class="card border-0 shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title text-center">@lang('Two Factor Authenticator')</h5>
                        </div>
                        <div class="card-body">
                            @includeIf('includes.flash')
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
                                <div class="form-group mb-3 mt-3 ms-3 col-md-4">
                                    <h4>{{__('Pamyment Modules')}}</h4>
                                </div>
                                <div class="row ms-4" id="check_box">
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
                                    <div class="form-group mb-3 mt-3 col-md-4">
                                        <input type="checkbox" name="otp_payment[]" value="Receive Request Money"  @if($user->paymentcheck('Receive Request Money')) checked @endif> {{__('Receive Request Money')}}
                                    </div>
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
</div>
<div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="modal-status bg-primary"></div>
        <form action="{{route('user.createTwoFactor')}}" method="POST">
            @csrf
            <div class="modal-body py-4">
                <div class="text-center">

                    <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                    <h3>@lang('Details')</h3>
                </div>
                <div class="form-group mx-auto text-center mt-3">
                    <img class="mx-auto" src="{{$qrCodeUrl}}">
                </div>


                <div class="form-group mt-3" id="code_body">
                    <label class="form-label required">{{__('Enter Google Authenticator Code')}}</label>
                    <input name="code" id="code" class="form-control" placeholder="{{__('Enter Google Authenticator Code')}}" type="text" step="any" value="{{ old('opt_code') }}" required>
                </div>
                <input type="hidden" name="key" value="{{$secret}}">

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
        </form>
        </div>
    </div>
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
        toastr.options =
        {
            "closeButton" : true,
            "progressBar" : true
        }
        toastr.success("Copied.");
    }

    $(document).ready(function() {
    $('#payment_fa_yn').click(function() {
        var checked = $(this).prop('checked');
        $('#check_box').find('input:checkbox').prop('checked', checked);
    });
    })
    $('#login_fa').on('change', function(){
        if ($('#login_fa').val() == 'two_fa_google' &&  '{{$user->twofa}}' != 1) {
            $('#modal-success').modal('show');
        }
    })
    $('#payment_fa').on('change', function(){
        if ($('#payment_fa').val() == 'two_fa_google' &&  '{{$user->twofa}}' != 1) {
            $('#modal-success').modal('show');
        }
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
