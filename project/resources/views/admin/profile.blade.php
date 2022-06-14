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

        <div class="card mb-4">
          <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
              <h6 class="m-0 font-weight-bold text-primary">{{ __('Edit Profile Form') }}</h6>
          </div>

          {{-- <div class="row">
            <div class="col-3">
              <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                <a class="nav-link active" id="v-pills-home-tab" data-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true">Home</a>
                <a class="nav-link" id="v-pills-profile-tab" data-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile" aria-selected="false">Profile</a>
                <a class="nav-link" id="v-pills-messages-tab" data-toggle="pill" href="#v-pills-messages" role="tab" aria-controls="v-pills-messages" aria-selected="false">Messages</a>
                <a class="nav-link" id="v-pills-settings-tab" data-toggle="pill" href="#v-pills-settings" role="tab" aria-controls="v-pills-settings" aria-selected="false">Settings</a>
              </div>
            </div>
            <div class="col-9">
              <div class="tab-content" id="v-pills-tabContent">
                <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">...</div>
                <div class="tab-pane fade" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">...</div>
                <div class="tab-pane fade" id="v-pills-messages" role="tabpanel" aria-labelledby="v-pills-messages-tab">...</div>
                <div class="tab-pane fade" id="v-pills-settings" role="tabpanel" aria-labelledby="v-pills-settings-tab">...</div>
              </div>
            </div>
          </div> --}}

            <div class="card-body">
              <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                <li class="nav-item">
                  <a class="nav-link active" id="pills-information-tab" data-toggle="pill" href="#pills-information" role="tab" aria-controls="pills-information" aria-selected="true">Information</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="pills-profile-tab" data-toggle="pill" href="#pills-profile" role="tab" aria-controls="pills-profile" aria-selected="false">Contacts</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="pills-contact-tab" data-toggle="pill" href="#pills-contact" role="tab" aria-controls="pills-contact" aria-selected="false">Rules</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="pills-transaction-tab" data-toggle="pill" href="#pills-transaction" role="tab" aria-controls="pills-transaction" aria-selected="false">Transaction</a>
                </li>
              </ul>
          
                  <div class="tab-pane fade show active" id="pills-informatioin" role="tabpanel" aria-labelledby="pills-information-tab">
                    {{-- <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
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
                        </div> --}}
                  </div>

                  <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">...</div>
                  <div class="tab-pane fade" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">...</div>
            
                  <div class="tab-pane fade" id="pills-transaction" role="tabpanel" aria-labelledby="pills-transaction-tab">
                   
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
</script>
@endsection