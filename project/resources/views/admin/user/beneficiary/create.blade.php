@extends('layouts.admin')
@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ $data->name }}</h5>
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
        <div class="m-3">
            @include('includes.admin.form-success')
        </div>
        <div class="tab-pane fade show active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
        <div class="card-body">
            <div class="card mb-1">
                <div class="container-xl align-items-center justify-content-between py-3">
                  <h5 class=" mb-0 text-gray-800 pl-3">{{__('Beneficiary Create')}}</h5>
                </div>
            </div>
            <div class="card mb-4">
                    <div class="container-xl">
                        <div class="row row-cards">
                            <div class="col-12">
                                <div class="card p-5">
                                    <div class="gocover" style="background: url({{ asset('assets/images/'.$gs->loader) }}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
                                        @includeIf('includes.flash')
                                        <form action="{{route('admin-user-beneficiary-store')}}" method="POST" enctype="multipart/form-data" id="iban-submit">
                                            @csrf

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mt-2">
                                                        <label class="form-label required">{{__('Beneficiary Type')}}</label>
                                                        <select id="bene_type" class="form-control" name="type" required>
                                                            <option value="RETAIL"> {{__("INDIVIDUAL")}}</option>
                                                            <option value="CORPORATE"> {{__("CORPORATE")}}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id='retail' style="display: flex" class="row">
                                                    <div class="form-group mt-2 col-md-6">
                                                        <label class="form-label required">{{__('First Name')}}</label>
                                                        <input name="firstname" id="firstname" class="form-control" autocomplete="off" placeholder="{{__('John')}}" type="text" pattern="[^À-ž()/><\][\\\-;&$@!|]+" value="{{ old('firstname') }}" required>
                                                    </div>
                                                    <div class="form-group mt-2 col-md-6">
                                                        <label class="form-label required">{{__('Last Name')}}</label>
                                                        <input name="lastname" id="lastname" class="form-control" autocomplete="off" placeholder="{{__('Doe')}}" type="text" pattern="[^À-ž()/><\][\\\-;&$@!|]+" value="{{ old('lastname') }}" required>
                                                    </div>
                                            </div>
                                            <div id='corporate' style="display: none" class="row">
                                                    <div class="form-group mt-2 col-md-12">
                                                        <label class="form-label required">{{__('Company Name')}}</label>
                                                        <input name="company_name" id="company_name" class="form-control" autocomplete="off" placeholder="{{__('Tech LTD')}}" type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" value="{{ old('company_name') }}" >
                                                    </div>
                                            </div>

                                            <hr/>
                                            <div class="row">


                                                <div class="form-group mt-2 col-md-6">
                                                    <label class="form-label required">{{__('Email')}}</label>
                                                    <input name="email" id="email" class="form-control shadow-none" placeholder="{{__('user@email.com')}}" type="email" value="{{ old('email') }}" required>
                                                </div>

                                                <div class="form-group mt-2 col-md-6">
                                                    <label class="form-label required">{{__('Address')}}</label>
                                                    <input name="address" id="address" class="form-control" autocomplete="off" placeholder="{{__('Enter Address')}}" type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" value="{{ old('address') }}" required>
                                                </div>

                                                <div class="form-group mt-2 col-md-6">
                                                    <label class="form-label required">{{__('Phone')}}</label>
                                                    <input name="phone" id="phone" class="form-control" autocomplete="off" placeholder="{{__('Enter Phone')}}" type="number" value="{{ old('phone') }}" required>
                                                </div>

                                                <div class="form-group mt-2 col-md-6">
                                                    <label class="form-label required">{{__('Registration NO')}}</label>
                                                    <input name="registration_no" id="registration_no" class="form-control" autocomplete="off" placeholder="{{__('Enter Registration NO')}}" type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" value="{{ old('registration_no') }}" required>
                                                </div>

                                                <div class="form-group mt-2 col-md-6">
                                                    <label class="form-label required">{{__('VAT NO')}}</label>
                                                    <input name="vat_no" id="vat_no" class="form-control" autocomplete="off" placeholder="{{__('Enter VAT NO')}}" type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" value="{{ old('vat_no') }}" required>
                                                </div>

                                                <div class="form-group mt-2 col-md-6">
                                                    <label class="form-label required">{{__('Contact Person')}}</label>
                                                    <input name="contact_person" id="contact_person" class="form-control" autocomplete="off" placeholder="{{__('Enter Contact Person')}}" type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" value="{{ old('contact_person') }}" required>
                                                </div>
                                            </div>
                                            <hr/>
                                            <div class="row">
                                                <div class="form-group mt-2 col-md-6">
                                                    <label class="form-label required">{{__('Account/IBAN')}}</label>
                                                    <input name="account_iban" id="account_iban" class="form-control iban-input" autocomplete="off" placeholder="{{__('Enter Account/IBAN')}}" type="text" value="{{ old('account_iban') }}" required>
                                                    <small class="text-danger iban-validation"></small>
                                                </div>

                                                <div class="form-group mt-2 col-md-6">
                                                    <label class="form-label required">{{__('Bank Name')}}</label>
                                                    <input name="bank_name" id="bank_name" class="form-control" autocomplete="off" placeholder="{{__('Enter Bank Name')}}" type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" value="{{ old('bank_name') }}" required readonly>
                                                </div>

                                                <div class="form-group mt-2 col-md-6">
                                                    <label class="form-label required">{{__('Bank Address')}}</label>
                                                    <input name="bank_address" id="bank_address" class="form-control" autocomplete="off" placeholder="{{__('Enter Bank Address')}}" type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" value="{{ old('bank_address') }}" required readonly>
                                                </div>

                                                <div class="form-group mt-2 col-md-6">
                                                    <label class="form-label required">{{__('SWIFT/BIC')}}</label>
                                                    <input name="swift_bic" id="swift" class="form-control" autocomplete="off" placeholder="{{__('MEINATWW')}}" type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" value="{{ old('swift_bic') }}" required readonly>
                                                </div>


                                            </div>
                                            <input name="user_id" type="hidden" value="{{$data->id}}" required>


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
        </div>
      </div>
    </div>
  </div>
</div>
@endsection


@section('scripts')
<script>
    'use strict';
        $('#bene_type').on('change', function(){
            if ($('#bene_type').val() == 'RETAIL') {
                document.getElementById('retail').style.display = "flex";
                $("#firstname").prop('required',true);
                $("#lastname").prop('required',true);
                document.getElementById('corporate').style.display = "none";
                $("#company_name").prop('required',false);
            }
            else if ($('#bene_type').val() == 'CORPORATE') {
                document.getElementById('retail').style.display = "none";
                $("#firstname").prop('required',false);
                $("#lastname").prop('required',false);
                document.getElementById('corporate').style.display = "flex";
                $("#company_name").prop('required',true);
            }
        })
        </script>
@endsection

