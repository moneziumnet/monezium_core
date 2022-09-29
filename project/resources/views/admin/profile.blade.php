@extends('layouts.admin')
@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Edit Profile') }} </h5>
    <ol class="breadcrumb py-0 m-0">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.profile') }}">{{ __('Edit Profile') }}</a></li>
    </ol>
  </div>
</div>

<div class="card mb-4 mt-3">


  <div class="card mt-1 tab-card">
    {{-- <div class="card-header tab-card-header">
      <ul class="nav nav-pills card-header-tabs" id="pills-tab" role="tablist">
        <li class="nav-item">
          <a class="nav-link active" id="one-tab" data-toggle="tab" href="#one" role="tab" aria-controls="One" aria-selected="true">Information</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="two-tab" data-toggle="tab" href="#two" role="tab" aria-controls="Two" aria-selected="false">Contacts</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="three-tab" data-toggle="tab" href="#three" role="tab" aria-controls="Three" aria-selected="false">Modules</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="four-tab" data-toggle="tab" href="#four" role="tab" aria-controls="Four" aria-selected="false">Documents</a>
        </li>

      </ul>
    </div> --}}

    <div class="tab-content" id="myTabContent">
      <div class="tab-pane fade show p-3 active" id="one" role="tabpanel" aria-labelledby="one-tab">
        {{-- <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);">
      </div> --}}
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
      <div class="row mt-3">
        <div class="col-lg-12">
          <div class="card mb-4">
            <div class="table-responsive p-3">
              <div class="col-sm-12 text-right">
                <a class="btn btn-primary" id="five-tab" data-toggle="tab" href="#five" role="tab" aria-controls="Five" aria-selected="false">
                  <i class="fas fa-plus"></i> {{__('Add New Contact')}}
                </a>
              </div>
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
          </div>
        </div>
      </div>
    </div>

    <div class="tab-pane fade p-3" id="three" role="tabpanel" aria-labelledby="three-tab">
      <form class="geniusform" action="{{route('admin.profile.moduleupdate')}}" method="POST" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" name="section[]" value="Sub Institutions management" {{ $data->sectionCheck('Sub Institutions management') ? 'checked' : '' }} class="custom-control-input" id="manage_ins">
                <label class="custom-control-label" for="manage_ins">{{__('Sub Institutions management')}}</label>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" name="section[]" value="Manage Customers" {{ $data->sectionCheck('Manage Customers') ? 'checked' : '' }} class="custom-control-input" id="manage_customers">
                <label class="custom-control-label" for="manage_customers">{{__('Manage Customers')}}</label>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" name="section[]" value="Loan Management" {{ $data->sectionCheck('Loan Management') ? 'checked' : '' }} class="custom-control-input" id="loan_management">
                <label class="custom-control-label" for="loan_management">{{__('Loan Management')}}</label>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" name="section[]" value="DPS Management" {{ $data->sectionCheck('DPS Management') ? 'checked' : '' }} class="custom-control-input" id="dps_management">
                <label class="custom-control-label" for="dps_management">{{__('DPS Management')}}</label>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" name="section[]" value="FDR Management" {{ $data->sectionCheck('FDR Management') ? 'checked' : '' }} class="custom-control-input" id="fdr_management">
                <label class="custom-control-label" for="fdr_management">{{__('FDR Management')}}</label>
              </div>
            </div>
          </div>

          <!-- <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Other Banks" {{ $data->sectionCheck('Other Banks') ? 'checked' : '' }} class="custom-control-input" id="other_banks">
                  <label class="custom-control-label" for="other_banks">{{__('Other Banks')}}</label>
                  </div>
              </div>
            </div> -->

          <div class="col-md-6">
            <div class="form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" name="section[]" value="Manage Escrow" {{ $data->sectionCheck('Manage Escrow') ? 'checked' : '' }} class="custom-control-input" id="manage_escrow">
                <label class="custom-control-label" for="manage_escrow">{{__('Manage Escrow')}}</label>
              </div>
            </div>
          </div>


          <div class="col-md-6">
            <div class="form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" name="section[]" value="Bank Transfer" {{ $data->sectionCheck('Bank Transfer') ? 'checked' : '' }} class="custom-control-input" id="money_transfer">
                <label class="custom-control-label" for="money_transfer">{{__('Bank Transfer')}}</label>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" name="section[]" value="Wire Transfer" {{ $data->sectionCheck('Wire Transfer') ? 'checked' : '' }} class="custom-control-input" id="wire_transfer">
                <label class="custom-control-label" for="wire_transfer">{{__('Wire Transfer')}}</label>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" name="section[]" value="Request Money" {{ $data->sectionCheck('Request Money') ? 'checked' : '' }} class="custom-control-input" id="request_money">
                <label class="custom-control-label" for="request_money">{{__('Request Money')}}</label>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" name="section[]" value="Withdraw" {{ $data->sectionCheck('Withdraw') ? 'checked' : '' }} class="custom-control-input" id="management_withdraw">
                <label class="custom-control-label" for="management_withdraw">{{__('Withdraw')}}</label>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" name="section[]" value="Deposit" {{ $data->sectionCheck('Deposit') ? 'checked' : '' }} class="custom-control-input" id="management_deposit">
                <label class="custom-control-label" for="management_deposit">{{__('Deposit')}}</label>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" name="section[]" value="Transactions" {{ $data->sectionCheck('Transactions') ? 'checked' : '' }} class="custom-control-input" id="transactions">
                <label class="custom-control-label" for="transactions">{{__('Transactions')}}</label>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" name="section[]" value="Deposits" {{ $data->sectionCheck('Deposits') ? 'checked' : '' }} class="custom-control-input" id="Deposits">
                <label class="custom-control-label" for="Deposits">{{__('Deposits')}}</label>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" name="section[]" value="Currency Setting" {{ $data->sectionCheck('Currency Setting') ? 'checked' : '' }} class="custom-control-input" id="currency_setting">
                <label class="custom-control-label" for="currency_setting">{{__('Currency Setting')}}</label>
              </div>
            </div>
          </div>

          <!-- <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Manage Blog" {{ $data->sectionCheck('Manage Blog') ? 'checked' : '' }} class="custom-control-input" id="manage_blog">
                  <label class="custom-control-label" for="manage_blog">{{__('Manage Blog')}}</label>
                  </div>
              </div>
            </div> -->

          <div class="col-md-6">
            <div class="form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" name="section[]" value="General Setting" {{ $data->sectionCheck('General Setting') ? 'checked' : '' }} class="custom-control-input" id="general_setting">
                <label class="custom-control-label" for="general_setting">{{__('General Setting')}}</label>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" name="section[]" value="Home page Setting" {{ $data->sectionCheck('Home page Setting') ? 'checked' : '' }} class="custom-control-input" id="homepage_manage">
                <label class="custom-control-label" for="homepage_manage">{{__('Home page Setting')}}</label>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" name="section[]" value="Email Setting" {{ $data->sectionCheck('Email Setting') ? 'checked' : '' }} class="custom-control-input" id="email_setting">
                <label class="custom-control-label" for="email_setting">{{__('Email Setting')}}</label>
              </div>
            </div>
          </div>

          <!-- <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="Message" {{ $data->sectionCheck('Message') ? 'checked' : '' }} class="custom-control-input" id="Message">
                  <label class="custom-control-label" for="Message">{{__('Message')}}</label>
                  </div>
              </div>
            </div> -->

          <div class="col-md-6">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="section[]" value="KYC Management" {{ $data->sectionCheck('KYC Management') ? 'checked' : '' }} class="custom-control-input" id="manage_kyc">
                  <label class="custom-control-label" for="manage_kyc">{{__('KYC Management')}}</label>
                  </div>
              </div>
            </div>

          <div class="col-md-6">
            <div class="form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" name="section[]" value="Language Manage" {{ $data->sectionCheck('Language Manage') ? 'checked' : '' }} class="custom-control-input" id="language_setting">
                <label class="custom-control-label" for="language_setting">{{__('Language Manage')}}</label>
              </div>
            </div>
          </div>
        </div>
        <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>
      </form>
    </div>

    <div class="tab-pane fade p-3" id="four" role="tabpanel" aria-labelledby="four-tab">
      @include('includes.admin.form-flash')
      {{ csrf_field() }}
      <div class="table-responsive p-2">
        <div class="col-sm-12 text-right">
          <a class="btn btn-primary" id="six-tab" data-toggle="tab" href="#six" role="tab" aria-controls="Six" aria-selected="false">
            <i class="fas fa-plus"></i> {{__('Add Documents')}}
          </a>
        </div>
        <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
          <thead class="thead-light">
            <tr>

              <th>{{__('Name')}}</th>
              <th>{{__('Download')}}</th>
              <th>{{__('Action')}}</th>
            </tr>
          </thead>
      </table>
    </div>
  </div>
  <div class="tab-pane fade p-3" id="five" role="tabpanel" aria-labelledby="five-tab">
    <form class="geniusform" action="{{ route('admin.profile.update-contact')}}" method="POST" enctype="multipart/form-data">
      @include('includes.admin.form-both')
      {{ csrf_field() }}

      <input type="hidden" name="contact_id" value="">
      <div class="row g-3">
        <div class="col-md-6">
          <div class="form-group">
            <label for="full-name">{{ __('Contact Type') }}</label>
            <input type="text" class="form-control" id="contact" name="contact" placeholder="{{ __('Contact Type') }}" value="" required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="full-name">{{ __('Name') }}</label>
            <input type="text" class="form-control" id="full_name" name="fullname" placeholder="{{ __('Enter Name') }}" value="" required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="dob">{{ __('Date of Birth') }}</label>
            <input type="text" class="form-control datepicker" id="dob" data-provide="datepicker" readonly data-date-format="dd-mm-yyyy" name="dob" placeholder="{{ __('dd-mm-yyyy') }}" value="" required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="personal-code">{{ __('Personal Code/Number') }}</label>
            <input type="text" class="form-control" id="personal-code" name="personal_code" placeholder="{{ __('Enter Personal Code/Number') }}" value="">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="your-email">{{ __('Your Email') }}</label>
            <input type="text" class="form-control" id="your-email" name="your_email" placeholder="{{ __('Enter Your Email') }}" value="">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="your-phone">{{ __('Your Phone') }}</label>
            <input type="text" class="form-control" id="your-phone" name="your_phone" placeholder="{{ __('Enter Phone Number') }}" value="">
          </div>
        </div>

        <div class="col-md-6">
          <div class="form-group">
            <label for="your-address">{{ __('Address') }}</label>
            <input type="text" class="form-control" id="your-address" name="your_address" placeholder="{{ __('Enter Address') }}" value="">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="c_city">{{ __('Your City') }}</label>
            <input type="text" class="form-control" id="c_city" name="c_city" placeholder="{{ __('Enter City') }}" value="">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="c_zipcode">{{ __('Zip Code') }}</label>
            <input type="text" class="form-control" id="c_zipcode" name="c_zipcode" placeholder="{{ __('Enter Zip Code') }}" value="">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="your-country">{{ __('Select Country') }}</label>
            <select class="form-control mb-3" name="c_country_id">
              <option value="">{{ __('Select Country') }}</option>
              @foreach(DB::table('countries')->get() as $country)
              <option value="{{ $country->id }}">{{ $country->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="your-id">{{ __('Your ID Number') }}</label>
            <input type="text" class="form-control" id="your-id" name="your_id" placeholder="{{ __('Enter Your ID Number') }}" value="">
          </div>
        </div>

        <div class="col-md-6">
          <div class="form-group">
            <label for="your-id" required>{{ __('Provider Authority Name') }}</label>
            <input type="text" class="form-control" id="issued_authority" name="issued_authority" placeholder="{{ __('Enter Provider Authority Name') }}" value="">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="date-of-issue">{{ __('Date of Issue') }}</label>
            <input type="text" class="form-control datepicker" id="date_of_issue" name="date_of_issue" data-provide="datepicker" readonly data-date-format="dd-mm-yyyy" placeholder="{{ __('dd-mm-yyyy') }}" value="" required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="date-of-expire">{{ __('Date of Expire') }}</label>
            <input type="text" class="form-control datepicker" id="date_of_expire" name="date_of_expire" data-provide="datepicker" readonly data-date-format="dd-mm-yyyy" placeholder="{{ __('dd-mm-yyyy') }}" value="" required>
          </div>
        </div>
      </div>
      <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>
    </form>

  </div>
  <div class="tab-pane fade p-3" id="six" role="tabpanel" aria-labelledby="six-tab">
  <form class="geniusformd" action="{{ route('admin.document.add-document')}}" method="POST" enctype="multipart/form-data">
      {{ csrf_field() }}
      <div class="row g-3">
        <div class="col-md-6">
          <div class="form-group">
            <label for="full-name">{{ __('Document Name') }}</label>
            <input type="text" class="form-control" id="document_name" name="document_name" placeholder="{{ __('Document Name') }}" value="" required>
          </div>
        </div>

        <div class="col-md-6">
          <div class="form-group">
            <label for="full-name">{{ __('Choose File') }}</label>
            <input type="file" class="form-control" id="document_file" name="document_file" required>
          </div>
        </div>
      </div>

      <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>
    </form>
  </div>
</div>


</div>

<div class="modal fade confirm-modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __("Confirm Delete") }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="text-center">{{ __("Do you want to proceed?") }}</p>
      </div>
      <div class="modal-footer">
        <a href="javascript:;" class="btn btn-secondary" data-dismiss="modal">{{ __("Cancel") }}</a>
        <a href="javascript:;" class="btn btn-danger btn-ok">{{ __("Delete") }}</a>
      </div>
    </div>
  </div>
</div>

<div class="modal fade confirm1-modal" id="deleteModal1" tabindex="-1" role="dialog" aria-labelledby="deleteModalTitle1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __("Confirm Delete") }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="text-center">{{ __("Do you want to proceed?") }}</p>
      </div>
      <div class="modal-footer">
        <a href="javascript:;" class="btn btn-secondary" data-dismiss="modal">{{ __("Cancel") }}</a>
        <a href="javascript:;" class="btn btn-danger btn-ok">{{ __("Delete") }}</a>
      </div>
    </div>
  </div>
</div>


@endsection
@section('scripts')
<script type="text/javascript">
  "use strict";
  $('.confirm1-modal').on('show.bs.modal', function(e) {
    $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
  });

  $('.confirm1-modal .btn-ok').on('click', function(e) {
    if(admin_loader == 1)
    {
      $('.Loader').show();
    }

      $.ajax({
      type:"GET",
      url:$(this).attr('href'),
      success:function(data)
      {
            $('.confirm1-modal').modal('hide');
            table1.ajax.reload();
            $('.alert-danger').hide();
            $('.alert-success').show();
            $('.alert-success p').html(data);

            if(admin_loader == 1)
            {
              $('.Loader').hide();
            }

      }
      });
      return false;
  });

  var table1 = $('#geniustable').DataTable({
    ordering: false,
    processing: true,
    serverSide: true,
    searching: true,
    ajax: '{{ route('admin.documents.datatables') }}',
       columns: [

        { data: 'name', name: 'name' },
        { data: 'download', name: 'download' },
        { data: 'action', name: 'action' },
    ],
    language: {
      processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
    }
  });

  var table = $('#geniustablelist').DataTable({
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
    language: {
      processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
    }
  });

  $('.datepicker').datepicker({
    format: 'dd-mm-yyyy',
    autoclose: true
  });
</script>
@endsection
