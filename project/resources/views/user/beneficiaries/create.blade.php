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
            {{__('New Beneficiary')}}
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
                        <form action="{{route('user.beneficiaries.store')}}" method="POST" enctype="multipart/form-data" id="iban-submit">
                            @csrf

                            <div class="row">
                                <div class="form-group mb-3 mt-3 col-md-6">
                                    <label class="form-label required">{{__('Beneficiary Type')}}</label>
                                    <select id="bene_type" class="form-select" name="type" required>
                                        <option value="RETAIL"> {{__("INDIVIDUAL")}}</option>
                                        <option value="CORPORATE"> {{__("CORPORATE")}}</option>
                                    </select>
                                </div>

                                <div id='retail' style="display: block" >
								<div class="row">
                                    <div class="form-group mb-3 mt-3 col-md-6">
                                        <label class="form-label required">{{__('First Name')}}</label>
                                        <input name="firstname" id="firstname" class="form-control" autocomplete="off" placeholder="{{__('John')}}" type="text" pattern="[^()/><\][\\\-;!|]+" value="{{ old('firstname') }}" required>
                                    </div>

                                    <div class="form-group mb-3 mt-3 col-md-6">
                                        <label class="form-label required">{{__('Last Name')}}</label>
                                        <input name="lastname" id="lastname" class="form-control" autocomplete="off" placeholder="{{__('Doe')}}" type="text" pattern="[^()/><\][\\\-;!|]+" value="{{ old('lastname') }}" required>
                                    </div>
                                </div>
								</div>
                                <div id='corporate' style="display: none">
                                    <div class="form-group mb-3 mt-3 col-md-12">
                                        <label class="form-label required">{{__('Company Name')}}</label>
                                        <input name="company_name" id="company_name" class="form-control" autocomplete="off" placeholder="{{__('Tech LTD')}}" type="text" pattern="[^()/><\][\\;!|]+" value="{{ old('company_name') }}" >
                                    </div>
                                </div>

                            </div>
                            <hr/>
                            <div class="row">


                                <div class="form-group mb-3 mt-3 col-md-6">
                                    <label class="form-label required">{{__('Email')}}</label>
                                    <input name="email" id="email" class="form-control shadow-none" placeholder="{{__('user@email.com')}}" type="email" value="{{ old('email') }}" required>
                                </div>

                                <div class="form-group mb-3 mt-3 col-md-6">
                                    <label class="form-label required">{{__('Address')}}</label>
                                    <input name="address" id="address" class="form-control" autocomplete="off" placeholder="{{__('Enter Address')}}" type="text" pattern="[^()/><\][\\;!|]+" value="{{ old('address') }}" min="1" required>
                                </div>

                                <div class="form-group mb-3 mt-3 col-md-6">
                                    <label class="form-label required">{{__('Phone')}}</label>
                                    <input name="phone" id="phone" class="form-control" autocomplete="off" placeholder="{{__('Enter Phone')}}" type="number" value="{{ old('phone') }}" min="1" required>
                                </div>

                                <div class="form-group mb-3 mt-3 col-md-6">
                                    <label class="form-label required">{{__('Registration NO')}}</label>
                                    <input name="registration_no" id="registration_no" class="form-control" autocomplete="off" placeholder="{{__('Enter Registration NO')}}" type="text" pattern="[^()/><\][\\;!|]+" value="{{ old('registration_no') }}" min="1" required>
                                </div>

                                <div class="form-group mb-3 mt-3 col-md-6">
                                    <label class="form-label required">{{__('VAT NO')}}</label>
                                    <input name="vat_no" id="vat_no" class="form-control" autocomplete="off" placeholder="{{__('Enter VAT NO')}}" type="text" pattern="[^()/><\][\\;!|]+" value="{{ old('vat_no') }}" min="1" required>
                                </div>

                                <div class="form-group mb-3 mt-3 col-md-6">
                                    <label class="form-label required">{{__('Contact Person')}}</label>
                                    <input name="contact_person" id="contact_person" class="form-control" autocomplete="off" placeholder="{{__('Enter Contact Person')}}" type="text" pattern="[^()/><\][\\;!|]+" value="{{ old('contact_person') }}" min="1" required>
                                </div>
                            </div>
                            <hr/>
                            <div class="row">
                                <div class="form-group mb-3 mt-3 col-md-6">
                                    <label class="form-label required">{{__('Bank Name')}}</label>
                                    <input name="bank_name" id="bank_name" class="form-control" autocomplete="off" placeholder="{{__('Enter Bank Name')}}" type="text" pattern="[^()/><\][\\;!|]+" value="{{ old('bank_name') }}" required>
                                </div>

                                <div class="form-group mb-3 mt-3 col-md-6">
                                    <label class="form-label required">{{__('Bank Address')}}</label>
                                    <input name="bank_address" id="bank_address" class="form-control" autocomplete="off" placeholder="{{__('Enter Bank Address')}}" type="text" pattern="[^()/><\][\\;!|]+" value="{{ old('bank_address') }}" min="1" required>
                                </div>

                                <div class="form-group mb-3 mt-3 col-md-6">
                                    <label class="form-label required">{{__('SWIFT/BIC')}}</label>
                                    <input name="swift_bic" id="swift_bic" class="form-control" autocomplete="off" placeholder="{{__('MEINATWW')}}" type="text" pattern="[^()/><\][\\;!|]+" value="{{ old('swift_bic') }}" min="1" required>
                                </div>

                                <div class="form-group mb-3 mt-3 col-md-6">
                                    <label class="form-label required">{{__('Account/IBAN')}}</label>
                                    <input name="account_iban" id="account_iban" class="form-control iban-input" autocomplete="off" placeholder="{{__('Enter Account/IBAN')}}" type="text" value="{{ old('account_iban') }}" min="1" required>
                                    <small class="text-danger iban-validation"></small>
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
    </script>
@endpush
