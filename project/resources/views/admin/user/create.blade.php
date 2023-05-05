@extends('layouts.admin')

@section('content')

<div class="card">
    <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Add New Customer') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.user.index')}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
    <ol class="breadcrumb m-0 py-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.user.index') }}">{{ __('Manage Customer') }}</a></li>
        <li class="breadcrumb-item"><a href="{{route('admin.user.create')}}">{{ __('Add New Customer') }}</a></li>
    </ol>
    </div>
</div>

<div class="row justify-content-center mt-3">
  <div class="col-md-10">
    <!-- Form Basic -->
    <div class="card mb-4">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">{{ __('Add New Customer Form') }}</h6>
      </div>

      <div class="card-body">

        <form class="geniusform row gy-3 gx-4 align-items-center" action="{{route('admin.user.store')}}" method="POST" enctype="multipart/form-data">

            @include('includes.admin.form-both')

            {{ csrf_field() }}
            <div class="col-sm-12">
                <h3 class="page-title">
                  {{__('Account Profile')}}
                  </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-sm-12" id="set-picture">
                        <label>{{ __('Set Picture') }} </label>
                        <div class="wrapper-image-preview">
                            <div class="box">
                                <div class="back-preview-image" style="background-image: url({{ asset('assets/images/placeholder.jpg') }});"></div>
                                <div class="upload-options">
                                    <label class="img-upload-label" for="img-upload"> <i class="fas fa-camera"></i> {{ __('Upload Picture') }} </label>
                                    <input id="img-upload" type="file" class="image-upload" name="photo" accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group col-sm-12">
                        <label for="inp-user-type" class="form-label">{{ __('Select Type') }}</label>
                        <select id="test" class="form-control form--control mb-3" name="form_select" onchange="showDiv( this)">
                            <option value="0"> Private</option>
                            <option value="1"> Corporate</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="inp-name">{{ __('Select Supervisor') }}</label>
          
                        <select class="form-control" name="referral_id" id="referral_id" >
                          <option value="0">{{ __('Select Supervisor') }}</option>
                          @foreach ($supervisor_list as $item)
                            @if(check_user_type_by_id(4, $item->id))
                              <option value="{{$item->id}}" >{{$item->company_name ?? $item->name}}</option>
                            @endif
                          @endforeach
                      </select>
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="inp-user-type" class="form-label">{{ __('Select Pricing Plan') }}</label>
                        <select id="bank_plan" class="form-control form--control mb-3" name="bank_plan" >
                            @foreach ($bankplans as $item)
                            <option value="{{$item->keyword}}"> {{$item->title}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="name" class="form-label">@lang('Your First Name')</label>
                        <input type="text" pattern="[^À-ž()/><\][\\\-;&$@!|]+" id="name" name="firstname" class="form-control form--control" required>
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="name" class="form-label">@lang('Your Last Name')</label>
                        <input type="text" pattern="[^À-ž()/><\][\\\-;&$@!|]+" id="name" name="lastname" class="form-control form--control" required>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="customer_dob" class="form-label">@lang('Your Birthday')</label>
                            <input type="date" id="customer_dob" name="customer_dob" class="form-control form--control" required>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="email" class="form-label">@lang('Your Email')</label>
                            <input type="email" id="email" name="email" class="form-control form--control" required>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="phone" class="form-label">@lang('Your Phone')</label>
                            <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" id="phone" name="phone" class="form-control form--control" required>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="inp-name" class="form-label">{{ __('Address') }}</label>
                            <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control form--control" id="address" name="address" placeholder="{{ __('Enter Address') }}" value="" required>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="inp-name" class="form-label">{{ __('City') }}</label>
                            <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control form--control" id="city" name="city" placeholder="{{ __('Enter City') }}" value="" required>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="inp-name" class="form-label">{{ __('Zip Code') }}</label>
                            <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control form--control" id="zip" name="zip" placeholder="{{ __('Enter Zip Code') }}" value="" required>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="inp-name" class="form-label">{{ __('Select Country') }}</label>
                            <select class="form-control form--control" name="country" required>
                                <option value="">{{ __('Select Country') }}</option>
                                @foreach(DB::table('countries')->get() as $dta)
                                <option value="{{ $dta->id }}">{{ $dta->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="password" class="form-label">@lang('Your Password')</label>
                            <input type="password" id="password" name="password" class="form-control form--control" required>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="confirm-password" class="form-label">@lang('Confirm Password')</label>
                            <input type="password" id="confirm-password" name="password_confirmation" class="form-control form--control" required>
                        </div>
                    </div>
                    <div id="private_div" class="col-sm-12">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="personal-code" class="form-label">{{ __('Personal Code/Number') }}</label>
                                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="private-input form-control form--control" id="personal_code" name="personal_code" placeholder="{{ __('Enter Personal Code/Number') }}" value="" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="your-id" class="form-label">{{ __('Your ID Number') }}</label>
                                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="private-input form-control form--control" id="your_id" name="your_id" placeholder="{{ __('Enter Your ID Number') }}" value="" required>
                                </div>
                            </div>
                            <div class="col-sm-6 mt-3">
                                <div class="form-group">
                                    <label for="your-id" class="form-label" required>{{ __('Provider Authority Name') }}</label>
                                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="private-input form-control form--control" id="issued_authority" name="issued_authority" placeholder="{{ __('Enter Provider Authority Name') }}" value="" required>
                                </div>
                            </div>
                            <div class="col-sm-6 mt-3">
                                <div class="form-group">
                                    <label for="date-of-issue" class="form-label">{{ __('Date of Issue') }}</label>
                                    <input type="date" class="private-input form-control form--control datepicker" id="date_of_issue" name="date_of_issue" placeholder="{{ __('yyyy-mm-dd') }}" value="" required>
                                </div>
                            </div>
                            <div class="col-sm-6 mt-3">
                                <div class="form-group">
                                    <label for="date-of-expire" class="form-label">{{ __('Date of Expire') }}</label>
                                    <input type="date" class="private-input form-control form--control datepicker" id="date_of_expire" name="date_of_expire" placeholder="{{ __('yyyy-mm-dd') }}" value="" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="corporate_div" style="display: none;" class="col-sm-12">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="dob" class="form-label">{{ __('Company Name') }}</label>
                                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="company-input form-control form--control" id="company_name"name="company_name" placeholder="{{ __('Enter Company Name') }}" value="">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    @php
                                        $companytype = ['LIMITED_LIABILITY', 'SOLE_TRADER', 'PARTNERSHIP', 'PUBLIC_LIMITED_COMPANY', 'JOINT_STOCK_COMPANY', 'CHARITY']
                                    @endphp
                                    <label for="inp-user-type" class="form-label">{{ __('Select Company Type') }}</label>
                                    <select id="company_type" class="company-input form-control" name="company_type">
                                        <option value=""> {{__('Select Company Type')}}</option>
                                        @foreach ( $companytype as $type )
                                            <option value="{{$type}}"> {{$type}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 mt-3">
                                <div class="form-group">
                                    <label for="inp-name" class="form-label">{{ __('Company Address') }}</label>
                                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="company-input form-control form--control" id="company_address" name="company_address" placeholder="{{ __('Enter Company Address') }}" value="">
                                </div>
                            </div>
                            <div class="col-sm-6 mt-3">
                                <div class="form-group">
                                    <label for="inp-name" class="form-label">{{ __('Company City') }}</label>
                                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="company-input form-control form--control" id="company_city" name="company_city" placeholder="{{ __('Enter Company City') }}" value="">
                                </div>
                            </div>
                            <div class="col-sm-6 mt-3">
                                <div class="form-group">
                                    <label for="inp-name" class="form-label">{{ __('Company ZipCode') }}</label>
                                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="company-input form-control form--control" id="company_zipcode" name="company_zipcode" placeholder="{{ __('Enter Company ZipCode') }}" value="">
                                </div>
                            </div>
                            <div class="col-sm-6 mt-3">
                                <div class="form-group">
                                    <label for="inp-name" class="form-label">{{ __('Company Country') }}</label>
                                    <select class="company-input form-control form--control" name="company_country">
                                        <option value="">{{ __('Select Country') }}</option>
                                        @foreach(DB::table('countries')->get() as $dta)
                                        <option value="{{ $dta->id }}">{{ $dta->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 mt-3">
                                <div class="form-group">
                                    <label for="dob" class="form-label">{{ __('Registration No') }}</label>
                                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="company-input form-control form--control" id="company_reg_no"name="company_reg_no" placeholder="{{ __('Enter Company Registration No') }}" value="">
                                </div>
                            </div>
                            <div class="col-sm-6 mt-3">
                                <div class="form-group">
                                    <label for="dob" class="form-label">{{ __('VAT No') }}</label>
                                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="company-input form-control form--control" id="company_vat_no"name="company_vat_no" placeholder="{{ __('Enter Company VAT No') }}" value="">
                                </div>
                            </div>
                            <div class="col-sm-6 mt-3">
                                <div class="form-group">
                                    <label for="dob" class="form-label">{{ __('Registration Date') }}</label>
                                    <input type="date" class="company-input form-control form--control" id="company_dob" name="company_dob" placeholder="{{ __('yyyy-mm-dd') }}" value="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="col-sm-12">
            <div class="col-sm-12 mt-3">
                <h3 class="page-title">
                  {{__('KYC Document')}}
                  </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach (json_decode($userForms->data) as $field)
                        @if ($field->type == 1 || $field->type == 3 )
                        <div class="form-group col-sm-12">
                            <label class="form-label {{$field->required == 1 ? 'required':'Optional'}}">@lang($field->label)</label>
                            @if ($field->type == 1)
                            <input type="text"  name="{{strtolower(str_replace(' ', '_', $field->label))}}" class="form-control" autocomplete="off" placeholder="@lang($field->label)" min="1" {{$field->required == 1 ? 'required':'Optional'}}>
                            @else
                            <textarea class="form-control" name="{{strtolower(str_replace(' ', '_', $field->label))}}" placeholder="@lang($field->label)"></textarea>
                            @endif
                        </div>
                        @elseif($field->type == 2)
                        <div class="form-group col-sm-12">
                            <label class="form-label {{$field->required == 1 ? 'required':'Optional'}}">@lang($field->label)</label>
                            <input type="file" name="{{strtolower(str_replace(' ', '_', $field->label))}}" class="form-control" autocomplete="off" {{$field->required == 1 ? 'required':'Optional'}}>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            <div class="col-sm-12 d-flex flex-wrap justify-content-center align-items-center">
                <button type="submit" class="btn btn-primary w-50">
                    @lang('Register Now')
                </button>
            </div>
        </form>
      </div>
    </div>
  </div>

</div>
@endsection


@section('scripts')


@endsection
