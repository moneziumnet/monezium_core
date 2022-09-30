@extends('layouts.user')

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      @include('user.invoicetab')
      <div class="row align-items-center mt-3">
        <div class="col">
          <div class="page-pretitle">
            {{__('Overview')}}
          </div>
          <h2 class="page-title">
            {{__('Edit Invoice')}}
          </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">

            <a href="{{ route('user.invoice.incoming.index') }}" class="btn btn-primary d-sm-inline-block">
                <i class="fas fa-backward me-1"></i> {{__('Back')}}
            </a>
          </div>
        </div>
      </div>
    </div>
</div>

<div class="container-xl mt-3 mb-3">
    <div class="row row-deck row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header justify-content-end">
                    <a href="{{route('user.invoice.send.mail',$invoice->id)}}" class="btn btn-primary"><i class="fab fa-telegram-plane me-1"></i> {{__('Send To Email')}}</a>

                    <a href="{{route('user.invoice.cancel',$invoice->id)}}" class="btn btn-danger ms-2"><i class="fas fa-ban me-1"></i> {{__('Cancel Invoice')}}</a>
                </div>
            <div class="card-body">
                <form action="{{route('user.invoice.incoming.update',$invoice->id)}}" id="form" method="post"  enctype="multipart/form-data">
                  @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-label">{{__('Beneficiary')}}</div>
                            <div class="input-group">
                            <select class="form-select shadow-none" name="beneficiary_id" required>
                                  <option value="{{$invoice->beneficiary_id}}" selected>{{$invoice->beneficiary->name}}</option>
                            </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-label">{{__('Select Currency')}}</div>
                            <select class="form-select currency shadow-none" name="currency">
                                  <option value="{{$invoice->currency_id}}" data-code="{{$invoice->currency->code}}" selected>{{$invoice->currency->code}}</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-label">{{__('Select type')}}</div>
                            <select class="form-select shadow-none" name="type" required>
                                  <option value="{{$invoice->type}}" selected>{{__($invoice->type)}}</option>
                            </select>
                        </div>
                    </div>
                    <hr>
                    @foreach ($invoice->items as $value)
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            @if ($loop->first)
                            <div class="form-label">{{__('Item name')}}</div>
                            @endif
                            <input type="text" name="item[]" class="form-control shadow-none itemname" required readonly value="{{$value->name}}">
                        </div>
                        <div class="col-md-4 mb-3">
                            @if ($loop->first)
                            <div class="form-label">{{__('Amount')}}</div>
                            @endif
                            <input type="text" name="amount[]" class="form-control shadow-none amount" required value="{{numFormat($value->amount)}}">
                        </div>
                        @php
                            $item = DB::table('taxes')->where('id', $value->tax_id)->first();
                        @endphp
                        <div class="col-md-1 mb-3">
                            @if ($loop->first)
                            <div class="form-label"> {{__("Tax")}}</div>
                            @endif
                            @if ($item)
                            <input type="text"  class="form-control shadow-none" value={{$item->name}} readonly required>
                            <input type="hidden" name="tax_id[]" class="form-control shadow-none" value={{$item->id}}  required>
                            @else
                            <div id="tax_append"></div>
                            <a class="btn btn-primary w-100 add-tax" href="javascript:void(0)">{{__('Add Tax')}}</a>
                            @endif

                        </div>
                        @if ($loop->first)
                        <div class="col-md-1 mb-3">
                            <div class="form-label">&nbsp;</div>
                            <button type="button" class="btn btn-primary w-100 add"><i class="fas fa-plus"></i></button>
                        </div>
                        @else
                        <div class="col-md-1 mb-3">
                            <button type="button" class="btn btn-danger w-100 remove"><i class="fas fa-times"></i></button>
                        </div>
                        @endif
                    </div>
                    @endforeach

                     <div class="extra-container mb-3"></div>
                        <hr>
                        <div class="row">
                            <div class="col-md-8 text-end">
                                <div class="form-label">{{__('Total Amount :')}}</div>
                            </div>
                            <div class="col-md-3  text-end">
                                <div class="form-label"><span class="totalAmount">{{numFormat($invoice->final_amount)}}</span> <span class="code">{{$invoice->currency->code}}</span></div>
                            </div>

                        </div>
                        <hr>
                        <div class="row">
                            <div class="form-label">{{__('Description')}}</div>
                            <textarea type="text" name="description" class="form-control shadow-none" required>{{$invoice->description}}</textarea>
                        </div>
                       <div class="col-md-12 mb-3">
                            <div class="form-label">&nbsp;</div>
                            <button type="submit" class="btn btn-primary w-100">
                                {{__('Update')}}
                            </button>
                        </div>
                </form>
            </div>
            </div>
        </div>
    </div>
</div>
<div class="modal modal-blur fade" id="modal-success-tax" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="modal-status bg-primary"></div>
        <div class="modal-body text-center py-4">
            <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
            <h3>{{__('Add New Tax')}}</h3>
            <div class="row text-start">
                <div class="col">
                    <form action="" method="post" id="customerSubmit" enctype="multipart/form-data">
                        @csrf
                        <div class = "row">
                            <div class="form-group mt-2">
                                <label class="form-label required">{{__('Tax Name')}}</label>
                                <input name="name" id="name" class="form-control shadow-none" placeholder="{{__('Tax Name')}}" type="text" value="{{ old('name') }}" required>
                            </div>
                            <div class="form-group mt-2">
                                <label class="form-label required">{{__('Tax rate')}}</label>
                                <input name="rate" id="rate" class="form-control shadow-none" placeholder="{{__('0.0')}}" type="number" step="any" value="{{ old('rate') }}" required>
                            </div>
                        </div>
                        <input type="hidden" name="user_id" value="{{auth()->id()}}">
                        <div class="row mt-3">
                            <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">
                                {{__('Cancel')}}
                                </a>
                            </div>
                            <div class="col">
                                <button type="submit" class="btn btn-primary w-100 confirm">
                                {{__('Confirm')}}
                                </button>
                            </div>
                        </div>
                    </form>
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
        $('.add').on('click',function(){
            $('.extra-container').append(`

                   <div class="row">
                        <div class="col-md-6 mb-3">
                            <input type="text" name="item[]" class="form-control shadow-none itemname" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <input type="text" name="amount[]" class="form-control shadow-none amount" required>
                        </div>
                        <div class="col-md-1 mb-3">
                            <div id="tax_append"></div>
                            <a class="btn btn-primary w-100 add-tax" href="javascript:void(0)">{{__('Add Tax')}}</a>
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
               $('.add-tax').addClass('disabled')
               return false
            }else{
               $('.itemname').attr('disabled',false)
               $('.amount').attr('disabled',false)
               $('.add').removeClass('disabled')
               $('.add-tax').removeClass('disabled')
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

        $(document).on('click', '.add-tax', function() {
            $('#modal-success-tax').modal('show')
        })

        $(document).ready(function(){
            // document.getElementById("contract_part").style.display = "none";

            $(document).on('submit','#customerSubmit',function(e){
                e.preventDefault();
                // AJAX request
                console.log($(this).serialize())
                var tax_count = $(this).attr('tax-count');
                $.ajax({
                    method: 'post',
                    url: "{{route('user.invoice.tax')}}",
                    data: $(this).serialize(),
                    success: function(msg) {
                        $(`#tax_append[tax-count="${tax_count}"]`).append(`
                            <input type="text"  class="form-control shadow-none tax" value="${msg.name} ${msg.rate}%" data-rate="${msg.rate}" readonly required>
                            <input type="hidden" name="tax_id[]" class="form-control shadow-none" value=${msg.id}  required>

                            `);
                        $(`.add-tax[tax-count="${tax_count}"]`).remove();
                        $(`#tax_append[tax-count="${tax_count}"]`).attr('id', 'un_tax_append');
                        $('#modal-success-tax').modal('hide');
                        computeTotalAmount();
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        console.log("some error");
                    }
                });
            });

            function computeTotalAmount() {
                var total = 0;
                $('.amount').each(function(e){
                    if($(this).val()!=''){
                        var rate = parseFloat($(this).parent().parent().find('.tax').data('rate'))
                        total += parseFloat($(this).val()) + rate *  parseFloat($(this).val()) / 100;
                    }
                    $('.totalAmount').text(total.toFixed(4))
                })
            }
        });


    </script>
@endpush
