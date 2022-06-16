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
                  <a class="nav-link" id="three-tab" data-toggle="tab" href="#three" role="tab" aria-controls="Three" aria-selected="false">Rules</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="four-tab" data-toggle="tab" href="#four" role="tab" aria-controls="Four" aria-selected="false">Transaction</a>
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
                    <div class="col-md-12">
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

                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="inp-payment-gateway">{{ __('Payment Gateway') }}</label>
                        <select class="form-control mb-3" name="payment_gateway_id" id="payment_gateway_id">
                          <option value="">{{ __('Select Payment Gateway') }}</option>
                          @foreach(DB::table('payment_gateways')->where('status',1)->orderBy('name','asc')->get() as $gateway)
                          <option value="{{ $gateway->id }}" {{ $data->payment_gateway_id == $gateway->id ? 'selected' : '' }}>{{ $gateway->name }}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                  </div>
                    

                    <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>

                </form>
                {{-- </div> --}}
              </div>

              <div class="tab-pane fade p-3" id="two" role="tabpanel" aria-labelledby="two-tab">
                <form class="geniusform" action="{{ route('admin.profile.update-contact') }}" method="POST" enctype="multipart/form-data">

                    @include('includes.admin.form-both')

                    {{ csrf_field() }}


                    <div class="row g-3">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="full-name">{{ __('Name') }}</label>
                        <input type="text" class="form-control" id="full_name" name="fullname" placeholder="{{ __('Enter Name') }}" value="{{$data->full_name}}" required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="dob">{{ __('Date of Birth') }}</label>
                        <input type="text" class="form-control datepicker" id="dob" data-provide="datepicker" readonly data-date-format="dd-mm-yyyy" name="dob" placeholder="{{ __('dd-mm-yyyy') }}"  value="{{$data->dob?date('d-m-Y', strtotime($data->dob)):''}}" required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="personal-code">{{ __('Personal Code/Number') }}</label>
                        <input type="text" class="form-control" id="personal-code" name="personal_code" placeholder="{{ __('Enter Personal Code/Number') }}" value="{{$data->personal_code}}">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="your-email">{{ __('Your Email') }}</label>
                        <input type="text" class="form-control" id="your-email" name="your_email" placeholder="{{ __('Enter Your Email') }}" value="{{$data->c_email}}">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="your-phone">{{ __('Your Phone') }}</label>
                        <input type="text" class="form-control" id="your-phone" name="your_phone" placeholder="{{ __('Enter Phone Number') }}" value="{{$data->c_phone}}">
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="your-address">{{ __('Address') }}</label>
                        <input type="text" class="form-control" id="your-address" name="your_address" placeholder="{{ __('Enter Address') }}" value="{{$data->c_address}}">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="c_city">{{ __('Your City') }}</label>
                        <input type="text" class="form-control" id="c_city" name="c_city" placeholder="{{ __('Enter City') }}" value="{{$data->c_city}}">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="c_zipcode">{{ __('Zip Code') }}</label>
                        <input type="text" class="form-control" id="c_zipcode" name="c_zipcode" placeholder="{{ __('Enter Zip Code') }}" value="{{$data->c_zip}}">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="your-country">{{ __('Select Country') }}</label>
                        <select class="form-control mb-3" name="c_country_id">
                          <option value="">{{ __('Select Country') }}</option>
                          @foreach(DB::table('countries')->get() as $dta)
                          <option value="{{ $dta->id }}" {{ $data->c_country == $dta->id ? 'selected' : '' }}>{{ $dta->name }}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="your-id">{{ __('Your ID Number') }}</label>
                        <input type="text" class="form-control" id="your-id" name="your_id" placeholder="{{ __('Enter Your ID Number') }}" value="{{$data->id_number}}">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="date-of-issue">{{ __('Date of Issue') }}</label>
                        <input type="text" class="form-control datepicker" id="issue_date" name="issue_date" data-provide="datepicker" readonly data-date-format="dd-mm-yyyy" placeholder="{{ __('dd-mm-yyyy') }}" value="{{$data->date_of_issue?date('d-m-Y', strtotime($data->date_of_issue)):''}}" required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="date-of-expire">{{ __('Date of Expire') }}</label>
                        <input type="text" class="form-control datepicker" id="expire_date" name="expire_date" data-provide="datepicker" readonly data-date-format="dd-mm-yyyy" placeholder="{{ __('dd-mm-yyyy') }}" value="{{$data->date_of_issue?date('d-m-Y', strtotime($data->date_of_issue)):''}}" required>
                      </div>
                    </div>
                  </div>
                    

                    <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>

                </form>             
              </div>
              
              <div class="tab-pane fade p-3" id="three" role="tabpanel" aria-labelledby="three-tab">
                <form class="geniusform" action="{{route('admin.gs.update')}}" method="POST" enctype="multipart/form-data">

                  @include('includes.admin.form-both')
      
                  {{ csrf_field() }}
      
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <div class="custom-control custom-switch">
                        <input type="checkbox" name="user_module[]" value="Loan" {{ $modules->moduleCheck('Loan') ? 'checked' : '' }} class="custom-control-input" id="Loan">
                        <label class="custom-control-label" for="Loan">{{__('Loan')}}</label>
                        </div>
                    </div>
                  </div>
      
                  <div class="col-md-6">
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" name="user_module[]" value="DPS" {{ $modules->moduleCheck('DPS') ? 'checked' : '' }} class="custom-control-input" id="DPS">
                          <label class="custom-control-label" for="DPS">{{__('DPS')}}</label>
                          </div>
                      </div>
                  </div>
      
                  <div class="col-md-6">
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" name="user_module[]" value="FDR" {{ $modules->moduleCheck('FDR') ? 'checked' : '' }} class="custom-control-input" id="FDR">
                          <label class="custom-control-label" for="FDR">{{__('FDR')}}</label>
                          </div>
                      </div>
                  </div>
      
                  <div class="col-md-6">
                    <div class="form-group">
                      <div class="custom-control custom-switch">
                        <input type="checkbox" name="user_module[]" value="Request Money" {{ $modules->moduleCheck('Request Money') ? 'checked' : '' }} class="custom-control-input" id="Request Money">
                        <label class="custom-control-label" for="Request Money">{{__('Request Money')}}</label>
                        </div>
                    </div>
                  </div>
      
                  <div class="col-md-6">
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" name="user_module[]" value="Deposit" {{ $modules->moduleCheck('Deposit') ? 'checked' : '' }} class="custom-control-input" id="Deposit">
                          <label class="custom-control-label" for="Deposit">{{__('Deposit')}}</label>
                          </div>
                      </div>
                  </div>
      
                  <div class="col-md-6">
                    <div class="form-group">
                      <div class="custom-control custom-switch">
                        <input type="checkbox" name="user_module[]" value="Wire Transfer" {{ $modules->moduleCheck('Wire Transfer') ? 'checked' : '' }} class="custom-control-input" id="Wire Transfer">
                        <label class="custom-control-label" for="Wire Transfer">{{__('Wire Transfer')}}</label>
                        </div>
                    </div>
                  </div>
      
                  <div class="col-md-6">
                    <div class="form-group">
                      <div class="custom-control custom-switch">
                        <input type="checkbox" name="user_module[]" value="Transfer" {{ $modules->moduleCheck('Transfer') ? 'checked' : '' }} class="custom-control-input" id="Transfer">
                        <label class="custom-control-label" for="Transfer">{{__('Transfer')}}</label>
                        </div>
                    </div>
                  </div>
      
                  <div class="col-md-6">
                    <div class="form-group">
                      <div class="custom-control custom-switch">
                        <input type="checkbox" name="user_module[]" value="Withdraw" {{ $modules->moduleCheck('Withdraw') ? 'checked' : '' }} class="custom-control-input" id="Withdraw">
                        <label class="custom-control-label" for="Withdraw">{{__('Withdraw')}}</label>
                        </div>
                    </div>
                  </div>
      
                  <div class="col-md-6">
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" name="user_module[]" value="Pricing Plan" {{ $modules->moduleCheck('Pricing Plan') ? 'checked' : '' }} class="custom-control-input" id="pricing_plan">
                          <label class="custom-control-label" for="pricing_plan">{{__('Pricing Plan')}}</label>
                          </div>
                      </div>
                  </div>
                  
                  <div class="col-md-6">
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" name="user_module[]" value="Voucher" {{ $modules->moduleCheck('Voucher') ? 'checked' : '' }} class="custom-control-input" id="voucher">
                          <label class="custom-control-label" for="voucher">{{__('Voucher')}}</label>
                          </div>
                      </div>
                  </div>
                  
                  <div class="col-md-6">
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" name="user_module[]" value="Invoice" {{ $modules->moduleCheck('Invoice') ? 'checked' : '' }} class="custom-control-input" id="invoice">
                          <label class="custom-control-label" for="invoice">{{__('Invoice')}}</label>
                          </div>
                      </div>
                  </div>
      
                  <div class="col-md-6">
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" name="user_module[]" value="Escrow" {{ $modules->moduleCheck('Escrow') ? 'checked' : '' }} class="custom-control-input" id="escrow">
                          <label class="custom-control-label" for="escrow">{{__('Escrow')}}</label>
                          </div>
                      </div>
                  </div>
                  
                  <div class="col-md-6">
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" name="user_module[]" value="Exchange Money" {{ $modules->moduleCheck('Exchange Money') ? 'checked' : '' }} class="custom-control-input" id="exchange-money">
                          <label class="custom-control-label" for="exchange-money">{{__('Exchange Money')}}</label>
                          </div>
                      </div>
                  </div>
      
                  <div class="col-md-6">
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" name="user_module[]" value="More" {{ $modules->moduleCheck('More') ? 'checked' : '' }} class="custom-control-input" id="more">
                          <label class="custom-control-label" for="more">{{__('More')}}</label>
                          </div>
                      </div>
                  </div>
      
                </div>
                  
      
                  <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>
      
              </form>           
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
$('#myTab a').on('click', function (e) {
  e.preventDefault()
  $(this).tab('show')
})

$('.datepicker').datepicker({
    format: 'dd-mm-yyyy',
    autoclose: true
});
</script>
@endsection