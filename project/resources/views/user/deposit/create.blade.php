@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Deposit Now')}}
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
                    <form id="deposit-form" action="" method="POST" enctype="multipart/form-data">
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
                            <label class="form-label required">{{__('Payment Method')}}</label>
                            <select name="method" id="withmethod" class="form-select" required>
                                <option value="">{{ __('Select Payment Method') }}</option>

                                {{-- @foreach (DB::table('payment_gateways')->where('subint_id', 3)->whereStatus(1)->get() as $gateway)
                                    @if ($gateway->type == 'manual')
                                        <option value="Manual" data-details="{{$gateway->details}}">{{ $gateway->title }}</option>
                                    @endif
                                    @if (in_array($gateway->keyword,$availableGatways))
                                        <option value="{{$gateway->keyword}}">{{ $gateway->name }}</option>
                                    @endif
                                @endforeach --}}
                            </select>
                        </div>

                        <div class="form-group mt-3">
                            <label class="form-label required">{{__('Payment Currency')}}</label>
                            <select name="currency_id" id="withcurrency" class="form-select" required>
                                <option value="">{{ __('Select Payment Currency') }}</option>
                            </select>
                        </div>

                        <div class="col-lg-12 mt-4 manual-payment d-none">
                            <div class="card">
                              <div class="card-body">
                                <div class="row">

                                  <div class="col-lg-12 pb-2 manual-payment-details">
                                  </div>

                                  <div class="col-lg-12">
                                    <label class="form-label required">@lang('Transaction ID')#</label>
                                    <input class="form-control" name="txn_id4" type="text" placeholder="Transaction ID#" id="manual_transaction_id">
                                  </div>

                                </div>
                              </div>
                            </div>
                          </div>

                        <div id="card-view" class="col-lg-12 pt-3 d-none">
                            <div class="row">
                                <input type="hidden" name="cmd" value="_xclick">
                                <input type="hidden" name="no_note" value="1">
                                <input type="hidden" name="lc" value="UK">
                                <input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest">

                                <div class="col-lg-6 mb-3">
                                    <input type="text" class="form-control card-elements" name="cardNumber" placeholder="{{ __('Card Number') }}" autocomplete="off" required autofocus oninput="validateCard(this.value);"/>
                                    <span id="errCard"></span>
                                </div>

                                <div class="col-lg-6 cardRow mb-3">
                                    <input type="text" class="form-control card-elements" placeholder="{{ ('Card CVC') }}" name="cardCVC" oninput="validateCVC(this.value);">
                                    <span id="errCVC"></span>
                                </div>

                                <div class="col-lg-6">
                                    <input type="text" class="form-control card-elements" placeholder="{{ __('Month') }}" name="month" >
                                </div>

                                <div class="col-lg-6">
                                    <input type="text" class="form-control card-elements" placeholder="{{ __('Year') }}" name="year">
                                </div>

                            </div>
                        </div>

                        <input type="hidden" id="currencyCode" name="currency_code" value="{{ $defaultCurrency->code }}">


                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Deposit Amount')}}</label>
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

<script src="https://js.paystack.co/v1/inline.js"></script>

<script type="text/javascript">
'use strict';

$(document).on('change','#withmethod',function(){
	var val = $(this).val();

	if(val == 'stripe')
	{
		$('#deposit-form').prop('action','{{ route('deposit.stripe.submit') }}');
		$('#card-view').removeClass('d-none');
		$('.card-elements').prop('required',true);
        $('#manual_transaction_id').prop('required',false);
        $('.manual-payment').addClass('d-none');
	}

    if(val == 'flutterwave')
	{
		$('#deposit-form').prop('action','{{ route('deposit.flutter.submit') }}');
        $('#card-view').addClass('d-none');
        $('.card-elements').prop('required',false);
        $('#manual_transaction_id').prop('required',false);
        $('.manual-payment').addClass('d-none');
	}

    if(val == 'authorize.net')
	{
		$('#deposit-form').prop('action','{{ route('deposit.authorize.submit') }}');
		$('#card-view').removeClass('d-none');
		$('.card-elements').prop('required',true);
        $('#manual_transaction_id').prop('required',false);
        $('.manual-payment').addClass('d-none');
	}

    if(val == 'paypal') {
        $('#deposit-form').prop('action','{{ route('deposit.paypal.submit') }}');
        $('#card-view').addClass('d-none');
        $('.card-elements').prop('required',false);
        $('#manual_transaction_id').prop('required',false);
        $('.manual-payment').addClass('d-none');
    }

    if(val == 'mollie') {
        $('#deposit-form').prop('action','{{ route('deposit.molly.submit') }}');
        $('#card-view').addClass('d-none');
        $('.card-elements').prop('required',false);
        $('#manual_transaction_id').prop('required',false);
        $('.manual-payment').addClass('d-none');
    }


	if(val == 'paytm') {
        $('#deposit-form').prop('action','{{ route('deposit.paytm.submit') }}');
        $('#card-view').addClass('d-none');
        $('.card-elements').prop('required',false);
        $('#manual_transaction_id').prop('required',false);

        $('.manual-payment').addClass('d-none');
    }

    if(val == 'paystack') {
        $('#deposit-form').prop('action','{{ route('deposit.paystack.submit') }}');
        $('#deposit-form').prop('class','step1-form');
        $('#card-view').addClass('d-none');
        $('.card-elements').prop('required',false);
        $('#manual_transaction_id').prop('required',false);
        $('.manual-payment').addClass('d-none');
    }

    if(val == 'instamojo') {
        $('#deposit-form').prop('action','{{ route('deposit.instamojo.submit') }}');
        $('#card-view').addClass('d-none');
        $('.card-elements').prop('required',false);
        $('#manual_transaction_id').prop('required',false);
        $('.manual-payment').addClass('d-none');
    }

    if(val == 'razorpay') {
        $('#deposit-form').prop('action','{{ route('deposit.razorpay.submit') }}');
        $('#card-view').addClass('d-none');
        $('.card-elements').prop('required',false);
        $('#manual_transaction_id').prop('required',false);
        $('.manual-payment').addClass('d-none');
    }

    if(val == 'Manual'){
      $('#deposit-form').prop('action','{{route('deposit.manual.submit')}}');
      $('.manual-payment').removeClass('d-none');
      $('#card-view').addClass('d-none');
      $('.card-elements').prop('required',false);
      $('#manual_transaction_id').prop('required',true);
      const details = $(this).find(':selected').data('details');
      $('.manual-payment-details').empty();
      $('.manual-payment-details').append(`<font size="3">${details}</font>`)
    }

});

$(document).on('submit','.step1-form',function(){
    var val = $('#sub').val();
    var total = $('#amount').val();
    var paystackInfo = $('#paystackInfo').val();
    var curr = $('#currencyCode').val();
    total = Math.round(total);
        if(val == 0)
        {
        var handler = PaystackPop.setup({
          key: paystackInfo,
          email: $('input[name=email]').val(),
          amount: total * 100,
          currency: curr,
          ref: ''+Math.floor((Math.random() * 1000000000) + 1),
          callback: function(response){
            $('#ref_id').val(response.reference);
            $('#sub').val('1');
            $('#final-btn').click();
          },
          onClose: function(){
            window.location.reload();
          }
        });
        handler.openIframe();
            return false;
        }
        else {
          $('#preloader').show();
            return true;
        }
});



    closedFunction=function() {
        alert('Payment Cancelled!');
    }

     successFunction=function(transaction_id) {
        window.location.href = '{{ url('order/payment/return') }}?txn_id=' + transaction_id;
    }

     failedFunction=function(transaction_id) {
         alert('Transaction was not successful, Ref: '+transaction_id)
    }
</script>


  <script type="text/javascript" src="{{ asset('assets/front/js/payvalid.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/front/js/paymin.js') }}"></script>
  <script type="text/javascript" src="https://js.stripe.com/v3/"></script>
  <script type="text/javascript" src="{{ asset('assets/front/js/payform.js') }}"></script>


  <script type="text/javascript">
  'use strict';

    $("#subinstitude").on('click',function(){
        let subinstitude = $("#subinstitude").val();
        $.post("{{ route('user.deposit.gateway') }}",{id:subinstitude,_token:'{{csrf_token()}}'},function (res) {
            let _optionHtml = '<option value="">Select Payment Method</option>';
            $.each(res, function(i, item) {
                _optionHtml += '<option value="' + item.keyword + '">' + item.name + '</option>';
            });
            $('select#withmethod').html(_optionHtml);
        })
    });

    $("#withmethod").on('change',function(){
        let keywordvalue = $("#withmethod").val();
        let subinstitude = $("#subinstitude").val();
        $.post("{{ route('user.deposit.gatewaycurrency') }}",{id:subinstitude,keyword:keywordvalue,_token:'{{csrf_token()}}'},function (res) {
            let _optionHtml = '<option value="">Select Payment Currency</option>';
            $.each(res, function(i,item) {
                _optionHtml += '<option value="' + item.id + '">' + item.code + '</option>';
            });
            $('select#withcurrency').html(_optionHtml);
        })
    });


    var cnstatus = false;
    var dateStatus = false;
    var cvcStatus = false;

    function validateCard(cn) {
      cnstatus = Stripe.card.validateCardNumber(cn);
      if (!cnstatus) {
        $("#errCard").html('Card number not valid<br>');
      } else {
        $("#errCard").html('');
      }
      btnStatusChange();


    }

    function validateCVC(cvc) {
      cvcStatus = Stripe.card.validateCVC(cvc);
      if (!cvcStatus) {
        $("#errCVC").html('CVC number not valid');
      } else {
        $("#errCVC").html('');
      }
      btnStatusChange();
    }

  </script>


@endpush
