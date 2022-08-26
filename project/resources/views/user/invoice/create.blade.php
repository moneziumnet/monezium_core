@extends('layouts.user')

@section('contents')

<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <div class="page-pretitle">
            {{__('Overview')}}
          </div>
          <h2 class="page-title">
            {{__('Create Invoice')}}
          </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">

            <a href="{{ route('user.invoice.index') }}" class="btn btn-primary d-sm-inline-block">
                <i class="fas fa-backward me-1"></i> {{__('Invoice List')}}
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

<div class="container-xl">
    <div class="row row-deck row-cards">
        <div class="col-12">
            <div class="card">
            <div class="card-body">
                <form action="" id="form" method="post">
                  @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-label">{{__('Inoice To')}}</div>
                            <input type="text" name="invoice_to" class="form-control shadow-none" value="{{old('invoice_to')}}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-label">{{__('Recipient Email')}}</div>
                            <div class="input-group">
                                <input type="email" name="email" class="form-control shadow-none camera_value" value="{{old('email')}}" required>
                                <button type="button"  data-bs-toggle="tooltip" data-bs-original-title="@lang('Scan QR code')" class="input-group-text scan"><i class="fas fa-qrcode"></i></button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-label">{{__('Address')}}</div>
                            <input type="text" name="address" class="form-control shadow-none" value="{{old('address')}}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-label">{{__('Select Currency')}}</div>
                            <select class="form-select currency shadow-none" name="currency" required>
                                <option value="" selected>{{__('Select')}}</option>
                                @foreach ($currencies as $curr)
                                  <option value="{{$curr->id}}" data-code="{{$curr->code}}">{{$curr->code}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-label">{{__('Select type')}}</div>
                            <select class="form-select shadow-none" name="type" required>
                                <option value="" selected>{{__('Select')}}</option>
                                  <option value="Invoice" >{{__('Invoice')}}</option>
                                  <option value="Proforma" >{{__('Proforma')}}</option>
                                  <option value="Check" >{{__('Check')}}</option>
                            </select>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <div class="form-label">{{__('Item name')}}</div>
                            <input type="text" name="item[]" class="form-control shadow-none itemname" required disabled>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="form-label">{{__('Amount')}}</div>
                            <input type="text" name="amount[]" class="form-control shadow-none amount" required disabled>
                        </div>
                        <div class="col-md-1 mb-3">
                            <div class="form-label">&nbsp;</div>
                            <button type="button" class="btn btn-primary w-100 add disabled"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>

                     <div class="extra-container mb-3"></div>
                        <hr>
                        <div class="row">
                            <div class="col-md-8 text-end">
                                <div class="form-label">{{__('Total Amount :')}}</div>
                            </div>
                            <div class="col-md-3  text-end">
                                <div class="form-label"><span class="totalAmount">0</span> <span class="code"></span></div>
                            </div>

                        </div>

                       <div class="col-md-12 mb-3">
                            <div class="form-label">&nbsp;</div>
                            <button type="submit" class="btn btn-primary w-100">
                                {{__('Create')}}
                            </button>
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
        $('.add').on('click',function(){
            $('.extra-container').append(`

                   <div class="row">
                        <div class="col-md-8 mb-3">
                            <input type="text" name="item[]" class="form-control shadow-none itemname" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <input type="text" name="amount[]" class="form-control shadow-none amount" required>
                        </div>
                        <div class="col-md-1 mb-3">
                            <button type="button" class="btn btn-danger w-100 remove"><i class="fas fa-times"></i></button>
                        </div>
                    </div>

            `);
        })

        $(document).on('click','.remove',function () {
            $(this).closest('.row').remove()
        })

        $('.currency').on('change',function () {
            var selected = $('.currency option:selected')
            if(selected.val() == ''){
               $('.itemname').attr('disabled',true)
               $('.amount').attr('disabled',true)
               $('.add').addClass('disabled')
               return false
            }else{
               $('.itemname').attr('disabled',false)
               $('.amount').attr('disabled',false)
               $('.add').removeClass('disabled')
            }
            $('.code').text(selected.data('code'))
        })



        $(document).on('keyup','.amount',function () {
            var total = 0;
            $('.amount').each(function(e){
                if($(this).val()!=''){
                    total += parseFloat($(this).val());
                }
                $('.totalAmount').text(total.toFixed(4))
            })
        })

    </script>
@endpush
