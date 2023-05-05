@extends('layouts.admin')
@section('styles')
<style type="text/css">
  .ms-options-wrap,
  .ms-options-wrap * {
    box-sizing: border-box;
  }

  .ms-options ul li {
    list-style: none;
    margin-left: -40px;
  }

  .ms-options-wrap>button:focus,
  .ms-options-wrap>button {
    position: relative;
    width: 100%;
    text-align: left;
    border: 1px solid #d1d3e2;
    background-color: #fff;
    padding: 5px 20px 5px 5px;
    margin-top: 1px;
    font-size: 13px;
    color: #6e707e;
    outline: none;
    white-space: nowrap;
  }

  .ms-options-wrap>button:after {
    content: ' ';
    height: 0;
    position: absolute;
    top: 50%;
    right: 5px;
    width: 0;
    border: 6px solid rgba(0, 0, 0, 0);
    border-top-color: #999;
    margin-top: -3px;
  }

  .ms-options-wrap>.ms-options {
    position: absolute;
    left: 0;
    width: 100%;
    margin-top: 1px;
    margin-bottom: 20px;
    background: white;
    z-index: 2000;
    border: 1px solid #d1d3e2;
    text-align: left;
  }

  .ms-options-wrap>.ms-options>.ms-search input {
    width: 100%;
    padding: 4px 5px;
    border: none;
    border-bottom: 1px groove;
    outline: none;
  }

  .ms-options-wrap>.ms-options .ms-selectall {
    display: inline-block;
    font-size: .9em;
    text-transform: lowercase;
    text-decoration: none;
  }

  .ms-options-wrap>.ms-options .ms-selectall:hover {
    text-decoration: underline;
  }

  .ms-options-wrap>.ms-options>.ms-selectall.global {
    margin: 4px 5px;
  }

  .ms-options-wrap>.ms-options>ul>li.optgroup {
    padding: 5px;
  }

  .ms-options-wrap>.ms-options>ul>li.optgroup+li.optgroup {
    border-top: 1px solid #aaa;
  }

  .ms-options-wrap>.ms-options>ul>li.optgroup .label {
    display: block;
    padding: 5px 0 0 0;
    font-weight: bold;
  }

  .ms-options-wrap>.ms-options>ul label {
    position: relative;
    display: inline-block;
    width: 100%;
    padding: 2px 3px;
    margin: 1px 0;
  }

  .ms-options-wrap>.ms-options>ul li.selected label,
  .ms-options-wrap>.ms-options>ul label:hover {
    background-color: #efefef;
  }

  .ms-options-wrap>.ms-options>ul input[type="checkbox"] {
    margin-right: 5px;
    position: absolute;
    left: 4px;
    top: 7px;
  }
</style>
@endsection
@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ $data->company_name ?? $data->name }}</h5>
    <ol class="breadcrumb py-0 m-0">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.user.index') }}">{{ __('User List') }}</a></li>
    </ol>
  </div>
</div>


<div class="row mt-3">
  <div class="col-lg-12">
    <div class="card mt-1 tab-card">
      @include('admin.user.profiletab')

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
                  <h6 class="m-0 font-weight-bold text-primary">{{ __('Edit Customer Form') }}</h6>
                </div>

                <div class="card-body">
                  <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
                  <form class="geniusform" action="{{ route('admin-user-edit',$data->id) }}" method="POST" enctype="multipart/form-data">

                    @include('includes.admin.form-both')

                    {{ csrf_field() }}
                    @php
                    $userType = explode(',', $data->user_type);
                    @endphp
                    <div class="row g-3">
                      <div class="col-md-6 mb-3">
                        <div class="form-group">
                          <label>{{ __('Set Picture') }} <small class="small-font">({{ __('Maximum size is 2 MB.') }})</small></label>
                          <div class="wrapper-image-preview">
                            <div class="box">
                              <div class="back-preview-image" style="background-image: url({{ $data->photo ? asset('assets/images/'.$data->photo) : asset('assets/images/placeholder.jpg') }});"></div>
                              <div class="upload-options">
                                <label class="img-upload-label" for="img-upload"> <i class="fas fa-camera"></i> {{ __('Upload Picture') }} </label>
                                <input id="img-upload" type="file" class="image-upload" name="photo" accept="image/*">
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-6 mb-3">
                        <div class="form-group">
                          <label for="inp-name">{{ __('First Name') }}</label>
                          <input type="text" pattern="[^À-ž()/><\][\\\-;&$@!|]+" class="form-control" id="inp-name" name="firstname" placeholder="{{ __('Enter First Name') }}" value="{{ explode(" ",$data->name)[0] ?? $data->name }}" required>
                        </div>

                        <div class="form-group">
                          <label for="inp-name">{{ __('Last Name') }}</label>
                          <input type="text" pattern="[^À-ž()/><\][\\\-;&$@!|]+" class="form-control" id="inp-name" name="lastname" placeholder="{{ __('Enter Second Name') }}" value="{{ explode(" ",$data->name)[1] ?? '' }}" required>
                        </div>

                        <div class="form-group">
                          <label for="inp-email">{{ __('Email') }}</label>
                          <input type="email" class="form-control" id="inp-email" name="email" placeholder="{{ __('Enter Email') }}" value="{{ $data->email }}" disabled="">
                        </div>
                      </div>
                      <div class="col-md-12">
                        <div class="form-group">
                          <label for="inp-user-type" class="form-label">{{ __('Select Type') }}</label>
                          <select id="test" class="form-control form--control mb-3" name="form_select" onchange="showCompanyInput( this)">
                              <option value="0" {{isset($data->company_name) ? '' : 'selected'}}> Private</option>
                              <option value="1" {{isset($data->company_name) ? 'selected' : ''}}> Corporate</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label class="form-label required">{{__('Birthday ')}}</label>
                          <input name="dob" class="form-control form--control" autocomplete="off" placeholder="{{__('Your BirthDay')}}" type="date" value="{{$data->dob }}" required>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="inp-city">{{ __('City') }}</label>
                          <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="inp-city" name="city" placeholder="{{ __('Enter City') }}" value="{{ $data->city }}" required>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="inp-city">{{ __('Zip Code') }}</label>
                          <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="inp-zip" name="zip" placeholder="{{ __('Enter Zip Code') }}" value="{{ $data->zip }}" required>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="inp-city">{{ __('Country') }}</label>
                          <select class="form-control form--control" name="country">
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
                          <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="inp-address" name="address" placeholder="{{ __('Enter Address') }}" value="{{ $data->address }}" required>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="inp-phone">{{ __('Phone Number') }}</label>
                          <input type="text" pattern="^[0-9]+$" class="form-control" id="inp-phone" name="phone" placeholder="{{ __('Enter Phone number') }}" value="{{ $data->phone }}" required>
                        </div>
                      </div>
                      <div class="col-md-6">

                        <div class="form-group">
                          <label for="inp-name">{{ __('Select Supervisor') }}</label>
          
                          <select class="form-control" name="referral_id" id="referral_id" >
                            <option value="0">{{ __('Select Supervisor') }}</option>
                            @foreach ($user_list as $item)
                              @if(check_user_type_by_id(4, $item->id))
                                <option value="{{$item->id}}" @if($item->id == $data->referral_id) selected @endif>{{$item->company_name ?? $item->name}}</option>
                              @endif
                            @endforeach
                        </select>
                        </div>
                      </div>
                      <div class="col-md-12">
                      
                        <div class="form-group">
                          <label for="inp-name">{{ __('Type') }}</label>
          
                          <select class="select mb-3" name="user_type[]" multiple id="user_type">
                            @foreach(DB::table('customer_types')->orderBy('type_name','asc')->get() as $c_type)
                              <option value="{{ $c_type->id }}" @if(in_array($c_type->id, $userType)) selected @endif>{{ $c_type->type_name }}</option>
                            @endforeach
                          </select>
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
                                <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="private-input form-control form--control" id="personal_code" name="personal_code" placeholder="{{ __('Enter Personal Code/Number') }}" value="{{$data->personal_code}}" {{$private_required}}>
                            </div>
                          </div>
                          <div class="col-md-6 mt-2">
                            <div class="form-group">
                                <label for="your-id" class="form-label">{{ __('Your ID Number') }}</label>
                                <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="private-input form-control form--control" id="your_id" name="your_id" placeholder="{{ __('Enter Your ID Number') }}" value="{{$data->your_id}}" {{$private_required}}>
                            </div>
                          </div>
                          <div class="col-md-6 mt-3">
                            <div class="form-group">
                                <label for="your-id" class="form-label">{{ __('Provider Authority Name') }}</label>
                                <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="private-input form-control form--control" id="issued_authority" name="issued_authority" placeholder="{{ __('Enter Provider Authority Name') }}" value="{{$data->issued_authority}}" {{$private_required}}>
                            </div>
                          </div>
                          <div class="col-md-6 mt-3">
                            <div class="form-group">
                                <label for="date-of-issue" class="form-label">{{ __('Date of Issue') }}</label>
                                <input type="date" class="private-input form-control form--control datepicker" id="date_of_issue" name="date_of_issue" placeholder="{{ __('yyyy-mm-dd') }}" value="{{date("Y-m-d", strtotime($data->date_of_issue))}}" {{$private_required}}>
                            </div>
                          </div>
                          <div class="col-md-6 mt-3">
                            <div class="form-group">
                                <label for="date-of-expire" class="form-label">{{ __('Date of Expire') }}</label>
                                <input type="date" class="private-input form-control form--control datepicker" id="date_of_expire" name="date_of_expire" placeholder="{{ __('yyyy-mm-dd') }}" value="{{ date("Y-m-d", strtotime($data->date_of_expire)) }}" {{$private_required}}>
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
                      <button type="submit" id="submit-btn" class="btn btn-primary w-100 mt-3 mx-2">{{ __('Submit') }}</button>
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
<!--Row-->
@endsection

@section('scripts')
<script src="{{ asset('assets/admin/js/multiselect.js') }}"></script>

<script type="text/javascript">
  $('#user_type').multiselect({
    columns: 1,
    placeholder: 'Select User Type'
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

@section('styles')
<style type="text/css">
  .ms-options-wrap,
  .ms-options-wrap * {
    box-sizing: border-box;
  }

  .ms-options ul li {
    list-style: none;
    margin-left: -40px;
  }

  .ms-options-wrap>button:focus,
  .ms-options-wrap>button {
    position: relative;
    width: 100%;
    text-align: left;
    border: 1px solid #d1d3e2;
    background-color: #fff;
    padding: 5px 20px 5px 5px;
    margin-top: 1px;
    font-size: 13px;
    color: #6e707e;
    outline: none;
    white-space: nowrap;
  }

  .ms-options-wrap>button:after {
    content: ' ';
    height: 0;
    position: absolute;
    top: 50%;
    right: 5px;
    width: 0;
    border: 6px solid rgba(0, 0, 0, 0);
    border-top-color: #999;
    margin-top: -3px;
  }

  .ms-options-wrap>.ms-options {
    position: absolute;
    left: 0;
    width: 100%;
    margin-top: 1px;
    margin-bottom: 20px;
    background: white;
    z-index: 2000;
    border: 1px solid #d1d3e2;
    text-align: left;
  }

  .ms-options-wrap>.ms-options>.ms-search input {
    width: 100%;
    padding: 4px 5px;
    border: none;
    border-bottom: 1px groove;
    outline: none;
  }

  .ms-options-wrap>.ms-options .ms-selectall {
    display: inline-block;
    font-size: .9em;
    text-transform: lowercase;
    text-decoration: none;
  }

  .ms-options-wrap>.ms-options .ms-selectall:hover {
    text-decoration: underline;
  }

  .ms-options-wrap>.ms-options>.ms-selectall.global {
    margin: 4px 5px;
  }

  .ms-options-wrap>.ms-options>ul>li.optgroup {
    padding: 5px;
  }

  .ms-options-wrap>.ms-options>ul>li.optgroup+li.optgroup {
    border-top: 1px solid #aaa;
  }

  .ms-options-wrap>.ms-options>ul>li.optgroup .label {
    display: block;
    padding: 5px 0 0 0;
    font-weight: bold;
  }

  .ms-options-wrap>.ms-options>ul label {
    position: relative;
    display: inline-block;
    width: 100%;
    padding: 2px 3px;
    margin: 1px 0;
  }

  .ms-options-wrap>.ms-options>ul li.selected label,
  .ms-options-wrap>.ms-options>ul label:hover {
    background-color: #efefef;
  }

  .ms-options-wrap>.ms-options>ul input[type="checkbox"] {
    margin-right: 5px;
    position: absolute;
    left: 4px;
    top: 7px;
  }
</style>
@endsection
