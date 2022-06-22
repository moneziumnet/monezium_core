@extends('layouts.admin')
@section('content')

    <div class="card">
        <div class="d-sm-flex align-items-center justify-content-between py-3">
        <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Edit Profile') }}</h5>
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.profile') }}">{{ __('Edit Profile') }}</a></li>
        </ol>
        </div>
    </div>

        <div class="card mb-4 mt-3">
          <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
              <h6 class="m-0 font-weight-bold text-primary">{{ __('Edit Profile Form') }}</h6>
          </div>

          <div class="card mt-3 tab-card">
            <div class="card-header tab-card-header">
              <ul class="nav nav-pills card-header-tabs" id="pills-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link" id="one-tab" data-toggle="tab" href="#one" role="tab" aria-controls="One" aria-selected="true">Information</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="two-tab" data-toggle="tab" href="#two" role="tab" aria-controls="Two" aria-selected="false">Contacts</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="three-tab" data-toggle="tab" href="#three" role="tab" aria-controls="Three" aria-selected="false">Roles</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="four-tab" data-toggle="tab" href="#four" role="tab" aria-controls="Four" aria-selected="false">Transaction</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="five-tab" data-toggle="tab" href="#contact_form" role="tab" aria-controls="Four" aria-selected="false">Transaction</a>
                </li>
              </ul>
            </div>
    
            <div class="tab-content" id="myTabContent">
              <div class="tab-pane fade show active p-3" id="one" role="tabpanel" aria-labelledby="one-tab">
                {{-- <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div> --}}
                <form class="geniusform" action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">

                    @include('includes.admin.form-both')

                    {{ csrf_field() }}

                    <div class="form-group">
                        <label>{{ __('Profile Picture') }} <small class="small-font">({{ __('Preferred Size 600 X 600') }})</small></label>
                        <div class="wrapper-image-preview">
                            <div class="box">
                                <div class="back-preview-image" style="background-image: url({{ $data->photo ? asset('assets/images/'.$data->photo):asset('assets/images/placeholder.jpg') }});"></div>
                                <div class="upload-options">
                                    <label class="img-upload-label" for="img-upload"> <i class="fas fa-camera"></i> {{ __('Upload Picture') }} </label>
                                    <input id="img-upload" type="file" class="image-upload" name="photo" accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="inp-name">{{ __('Company Name') }}</label>
                        <input type="text" class="form-control" id="inp-name" name="name" placeholder="{{ __('Enter Name') }}" value="{{$data->name}}" required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="inp-name">{{ __('VAT Number') }}</label>
                        <input type="text" class="form-control" id="vat" name="vat" placeholder="{{ __('Enter VAT Number') }}" value="{{$data->vat}}">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="inp-name">{{ __('Address') }}</label>
                        <input type="text" class="form-control" id="address" name="address" placeholder="{{ __('Enter Address') }}" value="{{$data->address}}">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="inp-name">{{ __('City') }}</label>
                        <input type="text" class="form-control" id="city" name="city" placeholder="{{ __('Enter City') }}" value="{{$data->city}}">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="inp-name">{{ __('Zip Code') }}</label>
                        <input type="text" class="form-control" id="zip" name="zip" placeholder="{{ __('Enter Zip Code') }}" value="{{$data->zip}}">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="inp-name">{{ __('Select Country') }}</label>
                        <select class="form-control mb-3" name="country_id">
                          <option value="">{{ __('Select Country') }}</option>
                          @foreach(DB::table('countries')->get() as $dta)
                          <option value="{{ $dta->id }}" {{ $data->country_id == $dta->id ? 'selected' : '' }}>{{ $dta->name }}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="inp-email">{{ __('Email of Institution') }}</label>
                        <input type="email" class="form-control" id="inp-email" name="email" placeholder="{{ __('Enter Email') }}" value="{{$data->email}}" required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="inp-phone">{{ __('Phone of Institution') }}</label>
                        <input type="text" class="form-control" id="inp-phone" name="phone" placeholder="{{ __('Enter Phone') }}" value="{{$data->phone}}" required>
                      </div>
                    </div>

                   
                  </div>
                    

                    <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>

                </form>
                {{-- </div> --}}
              </div>

              <div class="tab-pane fade p-3" id="two" role="tabpanel" aria-labelledby="two-tab">
                    <div class="table-responsive p-2">
                      <table id="geniustablelist" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                        <thead class="thead-light">
                        <tr>
                            <th>{{__('Contact')}}</th>
                            <th>{{__('Name')}}</th>
                            <th>{{__('Email')}}</th>
                            <th>{{__('Address')}}</th>
                            <th>{{__('Phone')}}</th>
                            <th>{{__('Options')}}</th>
                        </tr>      
                        </thead>
                      </table>
                    </div>

                {{-- <form class="geniusform" action="{{ route('admin.profile.update-contact') }}" method="POST" enctype="multipart/form-data">

                    @include('includes.admin.form-both')

                    {{ csrf_field() }}


                    <div class="row g-3">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="full-name">{{ __('Name') }}</label>
                        <input type="text" class="form-control" id="full_name" name="fullname" placeholder="{{ __('Enter Name') }}" value="{{isset($contact)?$contact->full_name:''}}" required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="dob">{{ __('Date of Birth') }}</label>
                        <input type="text" class="form-control datepicker" id="dob" data-provide="datepicker" readonly data-date-format="dd-mm-yyyy" name="dob" placeholder="{{ __('dd-mm-yyyy') }}"  value="{{isset($contact)?$contact->dob?date('d-m-Y', strtotime($contact->dob)):'':''}}" required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="personal-code">{{ __('Personal Code/Number') }}</label>
                        <input type="text" class="form-control" id="personal-code" name="personal_code" placeholder="{{ __('Enter Personal Code/Number') }}" value="{{isset($contact)?$contact->personal_code:''}}">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="your-email">{{ __('Your Email') }}</label>
                        <input type="text" class="form-control" id="your-email" name="your_email" placeholder="{{ __('Enter Your Email') }}" value="{{isset($contact)?$contact->c_email:''}}">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="your-phone">{{ __('Your Phone') }}</label>
                        <input type="text" class="form-control" id="your-phone" name="your_phone" placeholder="{{ __('Enter Phone Number') }}" value="{{isset($contact)?$contact->c_phone:''}}">
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="your-address">{{ __('Address') }}</label>
                        <input type="text" class="form-control" id="your-address" name="your_address" placeholder="{{ __('Enter Address') }}" value="{{isset($contact)?$contact->c_address:''}}">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="c_city">{{ __('Your City') }}</label>
                        <input type="text" class="form-control" id="c_city" name="c_city" placeholder="{{ __('Enter City') }}" value="{{isset($contact)?$contact->c_city:''}}">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="c_zipcode">{{ __('Zip Code') }}</label>
                        <input type="text" class="form-control" id="c_zipcode" name="c_zipcode" placeholder="{{ __('Enter Zip Code') }}" value="{{isset($contact)?$contact->c_zip_code:''}}">
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
                        <input type="text" class="form-control" id="your-id" name="your_id" placeholder="{{ __('Enter Your ID Number') }}" value="{{isset($contact)?$contact->id_number:''}}">
                      </div>
                    </div>
                   
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="your-id" required>{{ __('Provider Authority Name') }}</label>
                        <input type="text" class="form-control" id="issued_authority" name="issued_authority" placeholder="{{ __('Enter Provider Authority Name') }}" value="{{isset($contact)?$contact->issued_authority:''}}">
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

                </form>              --}}
              </div>
              
              <div class="tab-pane fade p-3" id="three" role="tabpanel" aria-labelledby="three-tab">
                <div class="row">
                @foreach(DB::table('roles')->where('id', Auth()->user()->role_id)->get() as $role)
                  @php
                    $explode = explode(' , ',$role->section);
                  @endphp
                    @foreach($explode as $ex)
                    
                    <div class="col-md-4">
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" name="user_module[]" value="{{$ex}}" checked class="custom-control-input" id="{{$ex}}">
                          <label class="custom-control-label" for="Loan">{{__($ex)}}</label>
                          </div>
                      </div>
                    </div>
                    @endforeach
                @endforeach
                </div> 
                  
             
              </div>

              <div class="tab-pane fade p-3" id="four" role="tabpanel" aria-labelledby="four-tab">
                  <div class="table-responsive p-2">
                    <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                      <thead class="thead-light">
                      <tr>
                        <th>{{__('Date')}}</th>
                        <th>{{__('Transaction ID')}}</th>
                        <th>{{__('Description')}}</th>
                        <th>{{__('Remark')}}</th>
                        <th>{{__('Amount')}}</th>
                        <th>{{__('Charge')}}</th>
                      </tr>      
                      </thead>
                    </table>
                  </div>             
              </div>
              <div class="tab-pane fade p-3" id="contact_form" role="tabpanel" aria-labelledby="five-tab">
                <form class="geniusform" action="{{ route('admin.profile.update-contact') }}" method="POST" enctype="multipart/form-data">

                  @include('includes.admin.form-both')

                  {{ csrf_field() }}


                  <div class="row g-3">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="full-name">{{ __('Name') }}</label>
                      <input type="text" class="form-control" id="full_name" name="fullname" placeholder="{{ __('Enter Name') }}" value="{{isset($contact)?$contact->full_name:''}}" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="dob">{{ __('Date of Birth') }}</label>
                      <input type="text" class="form-control datepicker" id="dob" data-provide="datepicker" readonly data-date-format="dd-mm-yyyy" name="dob" placeholder="{{ __('dd-mm-yyyy') }}"  value="{{isset($contact)?$contact->dob?date('d-m-Y', strtotime($contact->dob)):'':''}}" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="personal-code">{{ __('Personal Code/Number') }}</label>
                      <input type="text" class="form-control" id="personal-code" name="personal_code" placeholder="{{ __('Enter Personal Code/Number') }}" value="{{isset($contact)?$contact->personal_code:''}}">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="your-email">{{ __('Your Email') }}</label>
                      <input type="text" class="form-control" id="your-email" name="your_email" placeholder="{{ __('Enter Your Email') }}" value="{{isset($contact)?$contact->c_email:''}}">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="your-phone">{{ __('Your Phone') }}</label>
                      <input type="text" class="form-control" id="your-phone" name="your_phone" placeholder="{{ __('Enter Phone Number') }}" value="{{isset($contact)?$contact->c_phone:''}}">
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="your-address">{{ __('Address') }}</label>
                      <input type="text" class="form-control" id="your-address" name="your_address" placeholder="{{ __('Enter Address') }}" value="{{isset($contact)?$contact->c_address:''}}">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="c_city">{{ __('Your City') }}</label>
                      <input type="text" class="form-control" id="c_city" name="c_city" placeholder="{{ __('Enter City') }}" value="{{isset($contact)?$contact->c_city:''}}">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="c_zipcode">{{ __('Zip Code') }}</label>
                      <input type="text" class="form-control" id="c_zipcode" name="c_zipcode" placeholder="{{ __('Enter Zip Code') }}" value="{{isset($contact)?$contact->c_zip_code:''}}">
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
                      <input type="text" class="form-control" id="your-id" name="your_id" placeholder="{{ __('Enter Your ID Number') }}" value="{{isset($contact)?$contact->id_number:''}}">
                    </div>
                  </div>
                 
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="your-id" required>{{ __('Provider Authority Name') }}</label>
                      <input type="text" class="form-control" id="issued_authority" name="issued_authority" placeholder="{{ __('Enter Provider Authority Name') }}" value="{{isset($contact)?$contact->issued_authority:''}}">
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
@section('scripts')
<script type="text/javascript">

"use strict";

  var table = $('#geniustable').DataTable({
       ordering: false,
       processing: true,
       serverSide: true,
       searching: true,
       ajax: '{{ route('admin.transactions.datatables') }}',
       columns: [
            { data: 'created_at', name: 'created_at' },
            { data: 'trnx', name: 'trnx' },
            { data: 'details', name: 'details' },
            { data: 'remark', name:'remark' },
            { data: 'amount', name: 'amount' },
            { data: 'charge', name: 'charge' },
        ],
        language : {
            processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
        }
    });
  
  
    var table1 = $('#geniustablelist').DataTable({
       ordering: false,
       processing: true,
       serverSide: true,
       searching: true,
       ajax: '{{ route('admin.contacts.datatables') }}',
       columns: [
            { data: 'contact', name: 'contact' },
            { data: 'fname', name: 'fname' },
            { data: 'email_add', name: 'email_add' },
            { data: 'address', name:'address' },
            { data: 'phone', name: 'phone' },
            { data: 'action', name: 'action' },
        ],
        language : {
            processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
        }
    });
    $(function() {
        $(".btn-area").append('<div class="col-sm-12 col-md-4 pr-3 text-right">'+
            '<a class="btn btn-primary" id="five-tab" href="#contact_form" role="tab" aria-controls="Five" aria-selected="false">'+
        '<i class="fas fa-plus"></i> {{__('Add New Contact')}}'+
        '</a>'+
        '</div>');
    });
$('#myTab a').on('click', function (e) {
  e.preventDefault()
  $(this).tab('show')
})

$('.datepicker').datepicker({
    format: 'dd-mm-yyyy',
    autoclose: true
});
// href="{{route('admin.bank.plan.create')}}"
</script>
@endsection