@extends('layouts.admin')
@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between py-3">
  <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Edit Contact') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.institution.contacts',$contact->user_id) }}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
    <ol class="breadcrumb m-0 py-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.institution.index') }}">{{ __('Institutions List') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.institution.contacts',$contact->user_id) }}">{{ __('Contacts List') }}</a></li>
    </ol>
  </div>
</div>

<div class="card mb-4 mt-3">
  <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
    <h6 class="m-0 font-weight-bold text-primary">{{ __('Edit Contact Form') }}</h6>
  </div>

  <div class="tab-content" id="myTabContent">
    <div class="card-body">
      <form class="geniusform" action="{{ route('admin.profile.update-contact') }}" method="POST" enctype="multipart/form-data">

        @include('includes.admin.form-both')

        {{ csrf_field() }}

        <input type="hidden" name="contact_id" value="{{$id}}">
        <div class="row g-3">

          <div class="col-md-6">
            <div class="form-group">
              <label for="full-name">{{ __('Contact') }}</label>
              <input type="text" pattern="[^()/><\][-;!|]+" class="form-control" id="contact" name="contact" placeholder="{{ __('Contact') }}" value="{{isset($contact)?$contact->contact:''}}" required>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label for="full-name">{{ __('Name') }}</label>
              <input type="text" pattern="[^()/><\][-;!|]+" class="form-control" id="full_name" name="fullname" placeholder="{{ __('Enter Name') }}" value="{{isset($contact)?$contact->full_name:''}}" required>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="dob">{{ __('Date of Birth') }}</label>
              <input type="text" class="form-control datepicker" id="dob" data-provide="datepicker" readonly data-date-format="dd-mm-yyyy" name="dob" placeholder="{{ __('dd-mm-yyyy') }}" value="{{isset($contact)?$contact->dob?date('d-m-Y', strtotime($contact->dob)):'':''}}" required>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="personal-code">{{ __('Personal Code/Number') }}</label>
              <input type="text" pattern="[^()/><\][;!|]+" class="form-control" id="personal-code" name="personal_code" placeholder="{{ __('Enter Personal Code/Number') }}" value="{{isset($contact)?$contact->personal_code:''}}">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="your-email">{{ __('Your Email') }}</label>
              <input type="email" class="form-control" id="your-email" name="your_email" placeholder="{{ __('Enter Your Email') }}" value="{{isset($contact)?$contact->c_email:''}}">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="your-phone">{{ __('Your Phone') }}</label>
              <input type="number" class="form-control" id="your-phone" name="your_phone" placeholder="{{ __('Enter Phone Number') }}" value="{{isset($contact)?$contact->c_phone:''}}">
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label for="your-address">{{ __('Address') }}</label>
              <input type="text" pattern="[^()/><\][;!|]+" class="form-control" id="your-address" name="your_address" placeholder="{{ __('Enter Address') }}" value="{{isset($contact)?$contact->c_address:''}}">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="c_city">{{ __('Your City') }}</label>
              <input type="text" pattern="[^()/><\][;!|]+" class="form-control" id="c_city" name="c_city" placeholder="{{ __('Enter City') }}" value="{{isset($contact)?$contact->c_city:''}}">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="c_zipcode">{{ __('Zip Code') }}</label>
              <input type="text" pattern="[^()/><\][;!|]+" class="form-control" id="c_zipcode" name="c_zipcode" placeholder="{{ __('Enter Zip Code') }}" value="{{isset($contact)?$contact->c_zip_code:''}}">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="your-country">{{ __('Select Country') }}</label>
              <select class="form-control mb-3" name="c_country_id">
                <option value="">{{ __('Select Country') }}</option>
                @foreach(DB::table('countries')->get() as $country)
                <option value="{{ $country->id }}" {{ isset($contact) && ($contact->c_country == $country->id) ? 'selected' : '' }}>{{ $country->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="your-id">{{ __('Your ID Number') }}</label>
              <input type="text" pattern="[^()/><\][;!|]+" class="form-control" id="your-id" name="your_id" placeholder="{{ __('Enter Your ID Number') }}" value="{{isset($contact)?$contact->id_number:''}}">
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label for="your-id" required>{{ __('Provider Authority Name') }}</label>
              <input type="text" pattern="[^()/><\][;!|]+" class="form-control" id="issued_authority" name="issued_authority" placeholder="{{ __('Enter Provider Authority Name') }}" value="{{isset($contact)?$contact->issued_authority:''}}">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="date-of-issue">{{ __('Date of Issue') }}</label>
              <input type="text" class="form-control datepicker" id="issue_date" name="issue_date" data-provide="datepicker" readonly data-date-format="dd-mm-yyyy" placeholder="{{ __('dd-mm-yyyy') }}" value="{{isset($contact)?$contact->date_of_issue?date('d-m-Y', strtotime($contact->date_of_issue)):'':''}}" required>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="date-of-expire">{{ __('Date of Expire') }}</label>
              <input type="text" class="form-control datepicker" id="expire_date" name="expire_date" data-provide="datepicker" readonly data-date-format="dd-mm-yyyy" placeholder="{{ __('dd-mm-yyyy') }}" value="{{isset($contact)?$contact->date_of_expire?date('d-m-Y', strtotime($contact->date_of_expire)):'':''}}" required>
            </div>
          </div>
        </div>


        <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>

      </form>
    </div>
  </div>
</div>

@endsection
