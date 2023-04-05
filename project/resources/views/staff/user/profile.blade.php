@extends('layouts.staff')
@section('styles')
@endsection
@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ $data->name }}</h5>
    <ol class="breadcrumb py-0 m-0">
      <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('staff.user.index') }}">{{ __('User List') }}</a></li>
    </ol>
  </div>
</div>


<div class="row mt-3">
  <div class="col-lg-12">
    <div class="card mt-1 tab-card">
      @include('staff.user.profiletab')

      <div class="tab-content" id="myTabContent">
        @php
        $currency = defaultCurr();
        @endphp
        @include('includes.admin.form-success')
        <div class="tab-pane fade show p-3 active" id="one" role="tabpanel" aria-labelledby="one-tab">
          <div class="row justify-content-center mt-3">
            <div class="col-md-10">
              <!-- Form Basic -->
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">{{ __('View Customer Information') }}</h6>
                </div>

                <div class="card-body">
                  <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>

                    {{ csrf_field() }}

                    <div class="row g-3">
                      <div class="col-md-6 mb-3">
                        <div class="form-group">
                          <label>{{ __('Customer Avatar') }} </label>
                          <div class="wrapper-image-preview">
                            <div class="box">
                              <div class="back-preview-image" style="background-image: url({{ $data->photo ? asset('assets/images/'.$data->photo) : asset('assets/images/placeholder.jpg') }});"></div>
                              {{-- <div class="upload-options">
                                <label class="img-upload-label" for="img-upload"> <i class="fas fa-camera"></i> {{ __('Upload Picture') }} </label>
                                <input id="img-upload" type="file" class="image-upload" name="photo" accept="image/*">
                              </div> --}}
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-6 mb-3">
                        <div class="form-group">
                          <label for="inp-name">{{ __('First Name') }}</label>
                          <input type="text" pattern="[^À-ž()/><\][\\\-;&$@!|]+" class="form-control" id="inp-name" name="firstname" placeholder="{{ __('Enter First Name') }}" value="{{ explode(" ",$data->name)[0] ?? $data->name }}" readonly>
                        </div>

                        <div class="form-group">
                          <label for="inp-name">{{ __('Last Name') }}</label>
                          <input type="text" pattern="[^À-ž()/><\][\\\-;&$@!|]+" class="form-control" id="inp-name" name="lastname" placeholder="{{ __('Enter Second Name') }}" value="{{ explode(" ",$data->name)[1] ?? '' }}" readonly>
                        </div>

                        <div class="form-group">
                          <label for="inp-email">{{ __('Email') }}</label>
                          <input type="email" class="form-control" id="inp-email" name="email" placeholder="{{ __('Enter Email') }}" value="{{ $data->email }}" disabled="">
                        </div>
                      </div>
                      <div class="col-md-12">
                        <div class="form-group">
                          <label for="inp-user-type" class="form-label">{{ __('User Type') }}</label>
                          <select id="test" class="form-control form--control mb-3" name="form_select" onchange="showCompanyInput( this)" disabled="">
                              <option value="0" {{isset($data->company_name) ? '' : 'selected'}}> Private</option>
                              <option value="1" {{isset($data->company_name) ? 'selected' : ''}}> Corporate</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label class="form-label required">{{__('Birthday ')}}</label>
                          <input name="dob" class="form-control form--control" autocomplete="off" placeholder="{{__('Your BirthDay')}}" type="date" value="{{$data->dob }}" readonly>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="inp-city">{{ __('City') }}</label>
                          <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="inp-city" name="city" placeholder="{{ __('Enter City') }}" value="{{ $data->city }}" readonly>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="inp-city">{{ __('Zip Code') }}</label>
                          <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="inp-zip" name="zip" placeholder="{{ __('Enter Zip Code') }}" value="{{ $data->zip }}" readonly>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="inp-city">{{ __('Country') }}</label>
                          <select class="form-control form--control" name="country" disabled="">
                            <option value="">{{ __('Select Country') }}</option>
                            @foreach(DB::table('countries')->get() as $dta)
                            <option value="{{ $dta->id }}" {{$dta->id == $data->country ? 'selected' : ''}}>{{ $dta->name }}</option>
                            @endforeach
                        </select>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="inp-address">{{ __('Address') }}</label>
                          <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="inp-address" name="address" placeholder="{{ __('Enter Address') }}" value="{{ $data->address }}" readonly>
                        </div>
                      </div>

                      @php
                        $private_required = isset($data->company_name) ? '' : 'required';
                        $corporate_required = isset($data->company_name) ? 'required' : '';
                      @endphp
                      <div id="private_div" class="col-md-12" style="{{isset($data->company_name) ? 'display:none;' : ''}}">
                        <div class="row">
                          <div class="col-md-6 mt-2">
                            <div class="form-group">
                                <label for="personal-code" class="form-label">{{ __('Personal Code/Number') }}</label>
                                <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="private-input form-control form--control" id="personal_code" name="personal_code" placeholder="{{ __('Enter Personal Code/Number') }}" value="{{$data->personal_code}}" {{$private_required}} readonly>
                            </div>
                          </div>
                          <div class="col-md-6 mt-2">
                            <div class="form-group">
                                <label for="your-id" class="form-label">{{ __('Your ID Number') }}</label>
                                <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="private-input form-control form--control" id="your_id" name="your_id" placeholder="{{ __('Enter Your ID Number') }}" value="{{$data->your_id}}" {{$private_required}} readonly>
                            </div>
                          </div>
                          <div class="col-md-6 mt-3">
                            <div class="form-group">
                                <label for="your-id" class="form-label">{{ __('Provider Authority Name') }}</label>
                                <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="private-input form-control form--control" id="issued_authority" name="issued_authority" placeholder="{{ __('Enter Provider Authority Name') }}" value="{{$data->issued_authority}}" {{$private_required}} readonly>
                            </div>
                          </div>
                          <div class="col-md-6 mt-3">
                            <div class="form-group">
                                <label for="date-of-issue" class="form-label">{{ __('Date of Issue') }}</label>
                                <input type="date" class="private-input form-control form--control datepicker" id="date_of_issue" name="date_of_issue" placeholder="{{ __('yyyy-mm-dd') }}" value="{{date("Y-m-d", strtotime($data->date_of_issue))}}" {{$private_required}} readonly>
                            </div>
                          </div>
                          <div class="col-md-6 mt-3">
                            <div class="form-group">
                                <label for="date-of-expire" class="form-label">{{ __('Date of Expire') }}</label>
                                <input type="date" class="private-input form-control form--control datepicker" id="date_of_expire" name="date_of_expire" placeholder="{{ __('yyyy-mm-dd') }}" value="{{ date("Y-m-d", strtotime($data->date_of_expire)) }}" {{$private_required}} readonly>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div id="corporate_div" class="col-md-12" style="{{isset($data->company_name) ? '' : 'display:none;'}}">
                        <div class="row">
                          <div class="col-md-6">
                            <div class="form-group mt-2">
                                <label for="dob" class="form-label">{{ __('Company Name') }}</label>
                                <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="company-input form-control form--control" id="company_name"name="company_name" placeholder="{{ __('Enter Company Name') }}" value="{{ $data->company_name }}" {{$corporate_required}}>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="form-group mt-2">
                                @php
                                    $companytype = ['LIMITED_LIABILITY', 'SOLE_TRADER', 'PARTNERSHIP', 'PUBLIC_LIMITED_COMPANY', 'JOINT_STOCK_COMPANY', 'CHARITY']
                                @endphp
                                <label for="inp-user-type" class="form-label">{{ __('Select Company Type') }}</label>
                                <select id="company_type" class="form-control" name="company_type">
                                    @foreach ( $companytype as $type )
                                        <option value="{{$type}}" {{$data->company_type == $type ? 'selected' : ''}}> {{$type}}</option>
                                    @endforeach
                                </select>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="form-group mt-3">
                                <label for="dob" class="form-label">{{ __('Company Address') }}</label>
                                <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="company-input form-control form--control" id="company_address"name="company_address" placeholder="{{ __('Enter Company Address') }}" value="{{ $data->company_address }}" {{$corporate_required}}>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="form-group mt-3">
                                <label for="dob" class="form-label">{{ __('Company City') }}</label>
                                <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="company-input form-control form--control" id="company_city"name="company_city" placeholder="{{ __('Enter Company City') }}" value="{{ $data->company_city }}" {{$corporate_required}}>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="form-group mt-3">
                                <label for="dob" class="form-label">{{ __('Company ZipCode') }}</label>
                                <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="company-input form-control form--control" id="company_zipcode"name="company_zipcode" placeholder="{{ __('Enter Company Zipcode') }}" value="{{ $data->company_zipcode }}" {{$corporate_required}}>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="form-group mt-3">
                                <label for="company_country" class="form-label">{{ __('Company Country') }}</label>
                                <select class="company-input form-control form--control" name="company_country" {{$corporate_required}}>
                                    <option value="">{{ __('Select Country') }}</option>
                                    @foreach(DB::table('countries')->get() as $dta)
                                    <option value="{{ $dta->id }}" {{$dta->id == $data->company_country ? 'selected' : ''}}>{{ $dta->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="form-group mt-3">
                                <label for="dob" class="form-label">{{ __('Registration No') }}</label>
                                <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="company-input form-control form--control" id="company_reg_no"name="company_reg_no" placeholder="{{ __('Enter Company Registration No') }}" value="{{ $data->company_reg_no }}" {{$corporate_required}}>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="form-group mt-3">
                                <label for="dob" class="form-label">{{ __('VAT No') }}</label>
                                <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="company-input form-control form--control" id="company_vat_no"name="company_vat_no" placeholder="{{ __('Enter Company VAT No') }}" value="{{ $data->company_vat_no }}" {{$corporate_required}}>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="form-group mt-3">
                                <label for="dob" class="form-label">{{ __('Registration Date') }}</label>
                                <input type="date" class="company-input form-control form--control" id="company_dob" name="company_dob" placeholder="{{ __('yyyy-mm-dd') }}" value="{{date("Y-m-d", strtotime($data->company_dob))}}" {{$corporate_required}}>
                            </div>
                          </div>
                        </div>
                      </div>
                </div>
              </div>

              <!-- Form Sizing -->
              <!-- Horizontal Form -->
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
</div>
<!--Row-->
@endsection

@section('scripts')
<script src="{{ asset('assets/admin/js/multiselect.js') }}"></script>

<script type="text/javascript">
  $('#user_type').multiselect({
    columns: 1,
    placeholder: 'User Type'
  });
  function showCompanyInput(select) {
    var company_input_list = $('.company-input');
    var private_input_list = $('.private-input');
    if (select.value == 1) {
        document.getElementById('corporate_div').style.display = "block";
        document.getElementById('private_div').style.display = "none";
        for (let i = 0; i < company_input_list.length; i++) {
            const item = company_input_list[i];
            item.required = true;
        }
        for (let i = 0; i < private_input_list.length; i++) {
            const item = private_input_list[i];
            item.required = false;
        }
    } else {
        document.getElementById('corporate_div').style.display = "none";
        document.getElementById('private_div').style.display = "block";
        for (let i = 0; i < company_input_list.length; i++) {
            const item = company_input_list[i];
            item.required = false;
        }
        for (let i = 0; i < private_input_list.length; i++) {
            const item = private_input_list[i];
            item.required = true;
        }
    }
  }
</script>
@endsection
