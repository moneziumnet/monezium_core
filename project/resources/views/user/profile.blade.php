@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Update Profile')}}
          </h2>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card p-3 py-4 px-sm-4">
                    @includeIf('includes.flash')
                    <form id="request-form" action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                          <div class="col-md-6 mx-auto">
                              <div class="form-group">
                                <label class="font-weight-bold">{{ __('Set Image') }} </label>
                                <div class="wrapper-image-preview">
                                    <div class="box">
                                        <div class="back-preview-image" style="background-image: url({{auth()->user()->photo ? asset('assets/images/'.auth()->user()->photo) : asset('assets/images/placeholder.jpg') }});"></div>
                                        <div class="upload-options">
                                            <label class="img-upload-label" for="img-upload"> <i class="fa fa-camera"></i> {{ __('Upload Picture') }} </label>
                                            <input id="img-upload" type="file" class="image-upload" name="photo" >
                                        </div>
                                    </div>
                                </div>
                            </div>
                          </div>
                        </div>

                        <div class="row g-3">
                          <div class="col-md-6">
                            <div class="form-group">
                              <label class="form-label required">{{__('First Name')}}</label>
                              <input name="firstname" class="form-control form--control" autocomplete="off" placeholder="{{__('User First Name')}}" type="text" value="{{ explode(" ", $user->name)[0] ?? $user->name }}" required readonly>
                            </div>
                          </div>

                          <div class="col-md-6">
                            <div class="form-group">
                              <label class="form-label required">{{__('Last Name')}}</label>
                              <input name="lastname" class="form-control form--control" autocomplete="off" placeholder="{{__('User Last Name')}}" type="text" value="{{ explode(" ",$user->name)[1] ?? ""}}" required readonly>
                            </div>
                          </div>

                          <div class="col-md-6">
                            <div class="form-group">
                              <label class="form-label required">{{__('Email')}}</label>
                              <input name="email" class="form-control form--control" autocomplete="off" placeholder="{{__('Email Address')}}" type="email" value="{{ $user->email }}" required readonly>
                            </div>
                          </div>

                          <div class="col-md-6">
                            <div class="form-group">
                              <label class="form-label required">{{__('Birthday ')}}</label>
                              <input name="dob" class="form-control form--control" autocomplete="off" placeholder="{{__('Your BirthDay')}}" type="date" value="{{$user->dob }}" required>
                            </div>
                          </div>

                          <div class="col-md-6">
                            <div class="form-group">
                              <label class="form-label required">{{__('Phone')}}</label>
                              <input name="phone" class="form-control form--control" autocomplete="off" placeholder="{{__('Phone Number')}}" type="tel" value="{{ $user->phone }}" required>
                            </div>
                          </div>

                          <div class="col-md-6">
                            <div class="form-group">
                              <label class="form-label required">{{__('Zip Code')}}</label>
                              <input name="zip" class="form-control form--control" autocomplete="off" placeholder="{{__('Zip')}}" type="text" value="{{ $user->zip }}" required>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="form-group">
                              <label class="form-label required">{{__('City')}}</label>
                              <input name="city" class="form-control form--control" autocomplete="off" placeholder="{{__('City')}}" type="text" value="{{ $user->city }}" required>
                            </div>
                          </div>

                          <div class="col-md-6">
                            <div class="form-group">
                              <label class="form-label required">{{__('VAT Number')}}</label>
                              <input name="vat" class="form-control form--control" autocomplete="off" placeholder="{{__('VAT')}}" type="text" value="{{ $user->vat }}" required>
                            </div>
                          </div>
                          <div class="col-md-12">
                            <div class="form-group">
                              <label class="form-label required">{{__('Address')}}</label>
                              <input name="address" class="form-control form--control" autocomplete="off" placeholder="{{__('Address')}}" type="text" value="{{ $user->address }}" required>
                            </div>
                          </div>

                          <div class="col-md-6 mt-4">
                            <div class="form-group">
                              <label class="form-label">{{__('Signature')}}</label>
                              <div class="wrapper-image-preview">
                                <div class="box">
                                    <div 
                                      class="back-preview-image mx-auto" 
                                      style="height: 120px;width:75%;background-image: url({{auth()->user()->signature ? asset('assets/images/'.auth()->user()->signature) : asset('assets/images/placeholder.jpg') }});"></div>
                                    <div class="upload-options">
                                        <label class="img-upload-label" for="signature-upload"> <i class="fa fa-camera"></i> {{ __('Upload Picture') }} </label>
                                        <input id="signature-upload" type="file" class="image-upload" accept=".png,.jpg" name="signature" >
                                    </div>
                                </div>
                              </div>
                            </div>
                          </div>

                          <div class="col-md-6 mt-4">
                            <div class="form-group">
                              <label class="form-label">{{__('Stamp')}}</label>
                              {{-- <input name="stamp" class="form-control shadow-none" type="file" accept=".png,.jpg"> --}}
                              <div class="wrapper-image-preview">
                                <div class="box">
                                  <div 
                                    class="back-preview-image mx-auto" 
                                    style="height: 120px;width:75%;background-image: url({{auth()->user()->stamp ? asset('assets/images/'.auth()->user()->stamp) : asset('assets/images/placeholder.jpg') }});"></div>
                                  <div class="upload-options">
                                      <label class="img-upload-label" for="stamp-upload"> <i class="fa fa-camera"></i> {{ __('Upload Picture') }} </label>
                                      <input id="stamp-upload" type="file" class="image-upload" name="stamp" accept=".png,.jpg" >
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>

                          <hr/>
                          <div class="col-md-12 form-group">
                            <label for="inp-user-type" class="form-label">{{ __('Select Type') }}</label>
                            <select id="test" class="form-control form--control mb-3" name="form_select" onchange="showCompanyInput( this)">
                                <option value="0" {{isset($user->company_name) ? '' : 'selected'}}> Private</option>
                                <option value="1" {{isset($user->company_name) ? 'selected' : ''}}> Corporate</option>
                            </select>
                          </div>

                          @php
                            $required = isset($user->company_name) ? 'required' : '';
                          @endphp
                          
                          <div id="hidden_div" class="" style="{{isset($user->company_name) ? '' : 'display:none;'}}">
                            <div class="row">
                              <div class="col-md-6">
                                <div class="form-group mt-2">
                                    <label for="dob" class="form-label">{{ __('Company Name') }}</label>
                                    <input type="text" class="company-input form-control form--control" id="company_name"name="company_name" placeholder="{{ __('Enter Company Name') }}" value="{{ $user->company_name }}" {{$required}}>
                                </div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-group mt-2">
                                    <label for="dob" class="form-label">{{ __('Registration No') }}</label>
                                    <input type="text" class="company-input form-control form--control" id="company_reg_no"name="company_reg_no" placeholder="{{ __('Enter Company Registration No') }}" value="{{ $user->company_reg_no }}" {{$required}}>
                                </div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-group mt-2">
                                    <label for="dob" class="form-label">{{ __('VAT No') }}</label>
                                    <input type="text" class="company-input form-control form--control" id="company_vat_no"name="company_vat_no" placeholder="{{ __('Enter Company VAT No') }}" value="{{ $user->company_vat_no }}" {{$required}}>
                                </div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-group mt-2">
                                    <label for="dob" class="form-label">{{ __('Company Address') }}</label>
                                    <input type="text" class="company-input form-control form--control" id="company_address"name="company_address" placeholder="{{ __('Enter Company Address') }}" value="{{$user->company_address}}" {{$required}}>
                                </div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-group mt-2">
                                    <label for="dob" class="form-label">{{ __('Date of Birth') }}</label>
                                    <input type="date" class="company-input form-control form--control" id="company_dob" name="company_dob" placeholder="{{ __('yyyy-mm-dd') }}" value="{{date("Y-m-d", strtotime($user->company_dob))}}" {{$required}}>
                                </div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-group mt-2">
                                    <label for="personal-code" class="form-label">{{ __('Personal Code/Number') }}</label>
                                    <input type="text" class="company-input form-control form--control" id="personal_code" name="personal_code" placeholder="{{ __('Enter Personal Code/Number') }}" value="{{$user->personal_code}}" {{$required}}>
                                </div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-group mt-2">
                                    <label for="your-id" class="form-label">{{ __('Your ID Number') }}</label>
                                    <input type="text" class="company-input form-control form--control" id="your_id" name="your_id" placeholder="{{ __('Enter Your ID Number') }}" value="{{$user->your_id}}" {{$required}}>
                                </div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-group mt-2">
                                    <label for="your-id" class="form-label">{{ __('Provider Authority Name') }}</label>
                                    <input type="text" class="company-input form-control form--control" id="issued_authority" name="issued_authority" placeholder="{{ __('Enter Provider Authority Name') }}" value="{{$user->issued_authority}}" {{$required}}>
                                </div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-group mt-2">
                                    <label for="date-of-issue" class="form-label">{{ __('Date of Issue') }}</label>
                                    <input type="date" class="company-input form-control form--control datepicker" id="date_of_issue" name="date_of_issue" placeholder="{{ __('yyyy-mm-dd') }}" value="{{date("Y-m-d", strtotime($user->date_of_issue))}}" {{$required}}>
                                </div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-group mt-2">
                                    <label for="date-of-expire" class="form-label">{{ __('Date of Expire') }}</label>
                                    <input type="date" class="company-input form-control form--control datepicker" id="date_of_expire" name="date_of_expire" placeholder="{{ __('yyyy-mm-dd') }}" value="{{ date("Y-m-d", strtotime($user->date_of_expire)) }}" {{$required}}>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="form-footer">
                          <button type="submit" class="btn btn-primary submit-btn">{{__('Submit')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@push('js')
<script type="text/javascript">
  'use strict';

  $('.edit-profile').on('click',function(){
    $('.upload').click();
  });

  function showCompanyInput(select) {
      var company_input_list = $('.company-input');
      if (select.value == 1) {
          document.getElementById('hidden_div').style.display = "block";
          for (let i = 0; i < company_input_list.length; i++) {
              const item = company_input_list[i];
              item.required = true;
          }
      } else {
          document.getElementById('hidden_div').style.display = "none";
          for (let i = 0; i < company_input_list.length; i++) {
              const item = company_input_list[i];
              item.required = false;
          }
      }
  }

</script>
@endpush
