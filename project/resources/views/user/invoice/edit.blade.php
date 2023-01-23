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

            <a href="{{ route('user.invoice.index') }}" class="btn btn-primary d-sm-inline-block">
                <i class="fas fa-backward me-1"></i> {{__('Back')}}
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
                <div class="card-header justify-content-end">
                    <button data-email="{{$invoice->email}}" data-id="{{$invoice->id}}" class="btn btn-primary send-email"><i class="fab fa-telegram-plane me-1"></i> {{__('Send To Email')}}</button>
                    <a href="{{route('user.invoice.cancel',$invoice->id)}}" class="btn btn-danger ms-2"><i class="fas fa-ban me-1"></i> {{__('Cancel Invoice')}}</a>
                </div>
            <div class="card-body">
                <form action="{{route('user.invoice.update',$invoice->id)}}" id="form" method="post"  enctype="multipart/form-data">
                  @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-label">{{__('Beneficiary')}}</div>
                            <div class="input-group">
                            <select class="form-select shadow-none" name="beneficiary_id" required>
                                <option value="" selected>{{__('Select beneficiary')}}</option>
                                @foreach ($beneficiaries as $beneficiary)
                                  <option value="{{$beneficiary->id}}" {{$invoice->beneficiary_id == $beneficiary->id ? 'selected' : ''}}>{{$beneficiary->name}}</option>
                                @endforeach
                            </select>
                            <button type="button"  data-bs-toggle="tooltip" data-bs-original-title="@lang('Add New Beneficiary')" class="input-group-text beneficiary"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-label">{{__('Select Currency')}}</div>
                            <select class="form-select currency shadow-none" name="currency">
                                <option value="" selected>{{__('Select')}}</option>
                                @foreach ($currencies as $curr)
                                  <option value="{{$curr->id}}" data-code="{{$curr->code}}" {{$invoice->currency_id == $curr->id ? 'selected':''}}>{{$curr->code}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-label">{{__('Select type')}}</div>
                            <select class="form-select shadow-none" name="type" required>
                                <option value="" selected>{{__('Select')}}</option>
                                  <option value="Invoice" {{'Invoice' == $invoice->type ? 'selected':''}}>{{__('Invoice')}}</option>
                                  <option value="Proforma" {{'Proforma' == $invoice->type ? 'selected':''}}>{{__('Proforma')}}</option>
                                  <option value="Check" {{'Check' == $invoice->type ? 'selected':''}}>{{__('Check')}}</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-label">{{__('Select template')}}</div>
                            <select class="form-select shadow-none" name="template" required>
                                <option value="" selected>{{__('Select')}}</option>
                                  <option value="0" {{$invoice->template == 0 ? 'selected':''}}>{{__('Basic')}}</option>
                                  <option value="1" {{$invoice->template == 1 ? 'selected':''}}>{{__('Classic')}}</option>
                                  <option value="2" {{$invoice->template == 2 ? 'selected':''}}>{{__('Pro')}}</option>
                            </select>
                        </div>
                    </div>
                    <hr>
                    @if (check_user_type(3))
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-label">{{__('Select Product')}}</div>
                            <select class="form-select shadow-none" name="product_id" id="product" >
                                <option value="" selected>{{__('Select')}}</option>
                                @foreach ($products as $product)
                                  <option value="{{$product->id}}" {{$invoice->product_id == $product->id ? 'selected' : ''}}>{{$product->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row" id="contract_part" class="display: none!important;">

                        <div class="col-md-6 mb-3">
                            <div class="form-label">{{__('Select Contract')}}</div>
                            <select class="form-select shadow-none" name="contract_id" id="contract">
                                <option value="" selected>{{__('Select')}}</option>
                                @foreach ($contracts as $contract)
                                  <option value="{{$contract->id}}" data-aoa = "{{$contract->contract_aoa}}" {{$invoice->contract_id == $contract->id ? 'selected' : ''}}>{{$contract->title}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-label">{{__('Select Contract AOA')}}</div>
                            <select class="form-select shadow-none" name="aoa_id" id="aoa" >
                                <option value="{{$invoice->contract_aoa_id}}" selected>{{__($invoice->aoa->title)}}</option>
                            </select>
                        </div>
                    </div>
                    @endif
                    <hr>
                    @foreach ($invoice->items as $value)
                    <div class="row">
                        <div class="col-md-5 mb-3">
                            @if ($loop->first)
                            <div class="form-label">{{__('Item name')}}</div>
                            @endif
                            <input type="text" pattern="[^()/><\][;!|]+" name="item[]" class="form-control shadow-none itemname" required value="{{$value->name}}">
                        </div>
                        <div class="col-md-4 mb-3">
                            @if ($loop->first)
                            <div class="form-label">{{__('Amount')}}</div>
                            @endif
                            <input type="number" step="any" name="amount[]" class="form-control shadow-none amount" required value="{{numFormat($value->amount)}}">
                        </div>
                        @php
                            $item = DB::table('taxes')->where('id', $value->tax_id)->first();
                        @endphp
                        <div class="col-md-2 mb-3">
                            @if ($loop->first)
                            <div class="form-label"> {{__("Tax")}}</div>
                            @endif
                            @if($item)
                            <input type="text"  class="form-control shadow-none tax" value="{{$item->name}} {{$item->rate}}%" data-rate="{{$item->rate}}" readonly required>
                            <input type="hidden" name="tax_id[]" class="form-control shadow-none" value={{$item->id}}  required>
                            @else
                            <div id="tax_append" tax-count="{{$loop->count}}"></div>
                            <input type="hidden" name="tax_id[]" value="0" class="add-tax tax" data-rate="0" tax-count="{{$loop->count}}">
                            <a class="btn btn-primary w-100 add-tax" href="javascript:void(0)" tax-count="{{$loop->count}}">{{__('Add Tax')}}</a>
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
                            <div class="col-md-6 mb-3">
                                <div class="form-label">{{__('Document')}}</div>
                                <input class= "document" name="document[]" class="form-control" type="file" accept=".doc,.docx,.pdf,.png,.jpg">
                            </div>
                            <div class="col-md-1 mb-3">
                                <div class="form-label">&nbsp;</div>
                                <button type="button" class="btn btn-primary w-100 doc_add"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="doc-extra-container">

                        </div>
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
<div class="modal modal-blur fade" id="modal-send-email" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="modal-status bg-primary"></div>
        <div class="modal-body text-center py-4">
            <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
            <h3>{{__('Send E-mail')}}</h3>
            <div class="row text-start">
                <div class="col">
                    <form action="{{ route('user.invoice.send.mail') }}" method="post">
                        @csrf
                        <div class="row">
                            <div class="form-group mt-2">
                                <label class="form-label required">{{__('Email Address')}}</label>
                                <input name="email" id="email" class="form-control shadow-none" placeholder="{{__('test@gmail.com')}}" type="email" required>
                            </div>
                        </div>
                        <input name="invoice_id" id="invoice_id" type="hidden" required>
                        <div class="row mt-3">
                            <div class="col">
                                <button type="submit" class="btn btn-primary w-100 confirm">
                                {{__('Send')}}
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
                                <input name="name" id="name" class="form-control shadow-none" placeholder="{{__('Tax Name')}}" type="text" pattern="[^()/><\][;!|]+" value="{{ old('name') }}" required>
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

<div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="modal-status bg-primary"></div>
        <div class="modal-body text-center py-4">
            <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
            <h3>{{__('Create New Beneficiary')}}</h3>
            <div class="row text-start">
                <div class="col">
                    <form action="{{route('user.invoice.beneficiary.create')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label required">{{__('Beneficiary Type')}}</label>
                                <select id="bene_type" class="form-select" name="type" required>
                                    <option value="RETAIL"> {{__("Individual")}}</option>
                                    <option value="CORPORATE"> {{__("CORPORATE")}}</option>
                                </select>
                            </div>
                            <div id='retail' style="display: block" >

                                <div class="form-group mt-3 col-md-6">
                                    <label class="form-label required">{{__('First Name')}}</label>
                                    <input name="firstname" id="firstname" class="form-control" autocomplete="off" placeholder="{{__('John')}}" type="text" pattern="[^()/><\][-;!|]+" value="{{ old('firstname') }}" required>
                                </div>

                                <div class="form-group mt-3 col-md-6">
                                    <label class="form-label required">{{__('Last Name')}}</label>
                                    <input name="lastname" id="lastname" class="form-control" autocomplete="off" placeholder="{{__('Doe')}}" type="text" pattern="[^()/><\][-;!|]+" value="{{ old('lastname') }}" required>
                                </div>
                            </div>
                            <div id='corporate' style="display: none">
                                <div class="form-group mt-3 col-md-6">
                                    <label class="form-label required">{{__('Company Name')}}</label>
                                    <input name="company_name" id="company_name" class="form-control" autocomplete="off" placeholder="{{__('Tech LTD')}}" type="text" pattern="[^()/><\][;!|]+" value="{{ old('company_name') }}" >
                                </div>
                            </div>
                            <hr class="my-3"/>
                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label required">{{__('Email')}}</label>
                                <input name="email" id="email" class="form-control shadow-none" placeholder="{{__('user@email.com')}}" type="email" value="{{ old('email') }}" required>
                            </div>
                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label required">{{__('Address')}}</label>
                                <input name="address" id="address" class="form-control shadow-none" placeholder="{{__('Address')}}" type="text" pattern="[^()/><\][;!|]+" value="{{ old('address') }}" required>
                            </div>
                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label required">{{__('Phone Number')}}</label>
                                <input name="phone" id="phone" class="form-control shadow-none" placeholder="{{__('+123456789')}}" type="number" value="{{ old('phone') }}" required>
                            </div>
                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label required">{{__('Registration NO')}}</label>
                                <input name="registration_no" id="registration_no" class="form-control shadow-none" placeholder="{{__('Registration NO')}}" type="text" pattern="[^()/><\][;!|]+" value="{{ old('registration_no') }}" required>
                            </div>
                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label required">{{__('VAT NO')}}</label>
                                <input name="vat_no" id="vat_no" class="form-control shadow-none" placeholder="{{__('VAT NO')}}" type="text" pattern="[^()/><\][;!|]+" value="{{ old('vat_no') }}" required>
                            </div>
                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label required">{{__('Contact Person')}}</label>
                                <input name="contact_person" id="contact_person" class="form-control shadow-none" placeholder="{{__('Contact Person')}}" type="text" pattern="[^()/><\][;!|]+" value="{{ old('contact_person') }}" required>
                            </div>
                            <hr class="my-3"/>
                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label required">{{__('Bank Name')}}</label>
                                <input name="bank_name" id="bank_name" class="form-control" autocomplete="off" placeholder="{{__('Enter Bank Name')}}" type="text" pattern="[^()/><\][;!|]+" value="{{ old('bank_name') }}" required>
                            </div>
                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label required">{{__('Bank Address')}}</label>
                                <input name="bank_address" id="bank_address" class="form-control" autocomplete="off" placeholder="{{__('Enter Bank Address')}}" type="text" pattern="[^()/><\][;!|]+" value="{{ old('bank_address') }}" min="1" required>
                            </div>
                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label required">{{__('SWIFT/BIC')}}</label>
                                <input name="swift_bic" id="swift_bic" class="form-control" autocomplete="off" placeholder="{{__('MEINATWW')}}" type="text" pattern="[^()/><\][;!|]+" value="{{ old('swift_bic') }}" min="1" required>
                            </div>
                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label required">{{__('Account/IBAN')}}</label>
                                <input name="account_iban" id="account_iban" class="form-control" autocomplete="off" placeholder="{{__('Enter Account/IBAN')}}" type="text" pattern="[^()/><\][;!|]+" value="{{ old('account_iban') }}" min="1" required>
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
        $('#bene_type').on('change', function(){
            if ($('#bene_type').val() == 'RETAIL') {
                document.getElementById('retail').style.display = "block";
                $("#firstname").prop('required',true);
                $("#lastname").prop('required',true);
                document.getElementById('corporate').style.display = "none";
                $("#company_name").prop('required',false);
            }
            else if ($('#bene_type').val() == 'CORPORATE') {
                document.getElementById('retail').style.display = "none";
                $("#firstname").prop('required',false);
                $("#lastname").prop('required',false);
                document.getElementById('corporate').style.display = "block";
                $("#company_name").prop('required',true);
            }
        })
        var tax_count = {{count($invoice->items)}};

        function calcAmount() {
            var total = 0;
            $('.amount').each(function(e){
                if($(this).val()!=''){
                    var rate = parseFloat($(this).parent().parent().find('.tax').data('rate'))
                    total += parseFloat($(this).val()) + rate *  parseFloat($(this).val()) / 100;
                }
                $('.totalAmount').text(total.toFixed(4))
            })
        }

        $('.add').on('click',function(){
            tax_count++;
            $('.extra-container').append(`

                   <div class="row">
                        <div class="col-md-5 mb-3">
                            <input type="text" pattern="[^()/><\][;!|]+" name="item[]" class="form-control shadow-none itemname" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <input type="number" step="any" name="amount[]" class="form-control shadow-none amount" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div id="tax_append" tax-count="${tax_count}"></div>
                            <input type="hidden" name="tax_id[]" value="0" class="add-tax tax" data-rate="0" tax-count="${tax_count}">
                            <a class="btn btn-primary w-100 add-tax" tax-count="${tax_count}" href="javascript:void(0)">{{__('Add Tax')}}</a>
                        </div>
                        <div class="col-md-1 mb-3">
                            <button type="button" class="btn btn-danger w-100 remove"><i class="fas fa-times"></i></button>
                        </div>
                    </div>

            `);
        })

        $('.doc_add').on('click',function(){
            $('.doc-extra-container').append(`

            <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-label required">{{__('Document')}}</div>
                            <input class= "document" name="document[]" class="form-control" type="file" accept=".doc,.docx,.pdf">
                        </div>
                        <div class="col-md-1 mb-3">
                            <div class="form-label">&nbsp;</div>
                            <button type="button" class="btn btn-danger w-100 doc_remove"><i class="fas fa-times"></i></button>
                        </div>
                    </div>

            `);
        })

        $('#contract').on('click', function() {
            var contract = $('#contract option:selected');
            let _optionHtml = '<option value="">Select</option>';
            $.each(contract.data('aoa'), function(i, item) {
                _optionHtml += '<option value="' + item.id + '">' + item.title + '</option>';
            });
            $('select#aoa').html(_optionHtml);

        })

        $('#product').on('click', function() {
            if($('#product').val()) {
                document.getElementById("contract_part").style.display = "flex";
            }
            else {
                document.getElementById("contract_part").style.display = "none";
                $('#contract').val('');
                $('#aoa').val('');
            }
        })


        $(document).on('click','.remove',function () {
            $(this).closest('.row').remove();
            calcAmount();
        })

        $(document).on('click','.doc_remove',function () {
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
            calcAmount();
        })
        $('.beneficiary').on('click',function() {
            $('#modal-success').modal('show')
        })
        $(document).on('click', '.add-tax', function() {
            $('#modal-success-tax #customerSubmit').attr('tax-count',$(this).attr('tax-count'));
            $('#modal-success-tax').modal('show')
        })

        $(document).ready(function(){
            calcAmount();
            var product_id = '{{$invoice->product_id}}';
            if(product_id == '') {
                document.getElementById("contract_part").style.display = "none";
            }
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
                        console.log(msg);
                        $(`#tax_append[tax-count="${tax_count}"]`).append(`
                            <input type="text"  class="form-control shadow-none tax" value="${msg.name} ${msg.rate}%" data-rate="${msg.rate}" readonly required>
                            <input type="hidden" name="tax_id[]" class="form-control shadow-none" value=${msg.id}  required>
                            `);
                        $(`.add-tax[tax-count="${tax_count}"]`).remove();
                        $(`#tax_append[tax-count="${tax_count}"]`).attr('id', 'un_tax_append');
                        $('#modal-success-tax').modal('hide');
                        calcAmount();
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        console.log("some error");
                    }
                });
            });
        });
        $('.send-email').on('click', function() {
            $('#modal-send-email').modal('show');
            $('#modal-send-email #email').val($(this).data('email'));
            $('#modal-send-email #invoice_id').val($(this).data('id'));
        });

    </script>
@endpush
