@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
    @include('user.ex_payment_tab')
      <div class="row align-items-center mt-3">
        <div class="col">
          <h2 class="page-title">
            {{__('Edit Beneficiary')}}
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
                    <div class="gocover" style="background: url({{ asset('assets/images/'.$gs->loader) }}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
                        @includeIf('includes.flash')
                        <form action="{{route('user.beneficiaries.update', $beneficiary->id)}}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label required">{{__('Name')}}</label>
                                        <input name="name" id="name" class="form-control" autocomplete="off" placeholder="{{__('John Doe')}}" type="text" value="{{ $beneficiary->name }}"  required>
                                    </div>
        
                                    <div class="form-group mb-3 mt-3">
                                        <label class="form-label required">{{__('Email')}}</label>
                                        <input name="email" id="email" class="form-control shadow-none" placeholder="{{__('user@email.com')}}" type="email" value="{{ $beneficiary->email }}" required>
                                    </div>
        
                                    <div class="form-group mb-3 mt-3">
                                        <label class="form-label required">{{__('Address')}}</label>
                                        <input name="address" id="address" class="form-control" autocomplete="off" placeholder="{{__('Enter Beneficiary Address')}}" type="text" value="{{ $beneficiary->address }}"  required>
                                    </div>

                                    <div class="form-group mb-3 mt-3">
                                        <label class="form-label required">{{__('Phone')}}</label>
                                        <input name="phone" id="phone" class="form-control" autocomplete="off" placeholder="{{__('Enter Phone')}}" type="text" value="{{ $beneficiary->phone }}" min="1" required>
                                    </div>
                                    
                                    <div class="form-group mb-3 mt-3">
                                        <label class="form-label required">{{__('Registration NO')}}</label>
                                        <input name="registration_no" id="registration_no" class="form-control" autocomplete="off" placeholder="{{__('Enter Registration NO')}}" type="text" value="{{ $beneficiary->registration_no }}" min="1" required>
                                    </div>

                                    <div class="form-group mb-3 mt-3">
                                        <label class="form-label required">{{__('VAT NO')}}</label>
                                        <input name="vat_no" id="vat_no" class="form-control" autocomplete="off" placeholder="{{__('Enter VAT NO')}}" type="text" value="{{ $beneficiary->vat_no }}" min="1" required>
                                    </div>

                                    <div class="form-group mb-3 mt-3">
                                        <label class="form-label required">{{__('Contact Person')}}</label>
                                        <input name="contact_person" id="contact_person" class="form-control" autocomplete="off" placeholder="{{__('Enter Contact Person')}}" type="text" value="{{ $beneficiary->contact_person }}" min="1" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label required">{{__('Bank Name')}}</label>
                                        <select name="other_bank_id" class="form-select bankId" required>
                                            <option value="">{{ __('Select Bank') }}</option>
                                            @foreach ($othersBank as $key=>$data)
                                                <option value="{{$data->id}}" {{$data->id == $beneficiary->other_bank_id ? 'selected' : ''}} data-requirements="{{ json_decode(json_encode($data->required_information,true)) }}">{{$data->title}}</option>
                                            @endforeach
                                        </select>
                                    </div>
        
                                    <div class="form-group mb-3 mt-3">
                                        <label class="form-label required">{{__('Bank Address')}}</label>
                                        <input name="bank_address" id="bank_address" class="form-control" autocomplete="off" placeholder="{{__('Enter Bank Address')}}" type="text" value="{{  $beneficiary->bank_address }}" required>
                                    </div>
        
                                    <div class="form-group mb-3 mt-3">
                                        <label class="form-label required">{{__('SWIFT/BIC')}}</label>
                                        <input name="swift_bic" id="swift_bic" class="form-control" autocomplete="off" placeholder="{{__('MEINATWW')}}" type="text" value="{{ $beneficiary->swift_bic  }}" min="1" required>
                                    </div>
        
                                    <div class="form-group mb-3 mt-3">
                                        <label class="form-label required">{{__('Account/IBAN')}}</label>
                                        <input name="account_iban" id="account_iban" class="form-control" autocomplete="off" placeholder="{{__('Enter Account/IBAN')}}" type="text" value="{{ $beneficiary->account_iban  }}" min="1" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="required-form-element">

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
    <script>
        'use strict';
        $(".bankId").on('change',function(){
            let requirements = $(this).find('option:selected').data('requirements');

            let output = ``;
            requirements.forEach(element => {

                    if(element.type == 'text') {
                        output +=
                        `
                            <div class="form-group mb-3 mt-3">
                                <label class="form-label ${element.validation}">{{__('${element.field_name}')}}</label>
                                <input type="text" name="${element.field_name}" class="form-control" autocomplete="off" placeholder="{{__('${element.field_name}')}}" min="1" ${element.validation}>
                            </div>
                        `;
                    }else if(element.type == 'textarea'){
                        output +=
                        `
                            <div class="form-group mb-3 mt-3">
                                <label class="form-label ${element.validation}">{{__('${element.field_name}')}}</label>
                                <textarea type="text" name="${element.field_name}" class="form-control" autocomplete="off" placeholder="{{__('${element.field_name}')}}" ${element.validation}></textarea>
                            </div>
                        `;
                    }else if(element.type == 'file'){
                        output +=
                        `
                            <div class="form-group mb-3 mt-3">
                                <label class="form-label ${element.validation}">{{__('${element.field_name}')}}</label>
                                <input type="file" name="${element.field_name}" class="form-control" autocomplete="off" ${element.validation}>
                            </div>
                        `
                    }
                });
                $('#required-form-element').html(output).hide().fadeIn(500);
        })
    </script>
@endpush
