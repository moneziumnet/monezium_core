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
    <h5 class=" mb-0 text-gray-800 pl-3">{{ $data->name }}</h5>
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

                    <div class="row g-3">
                      <div class="col-md-6">
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
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="inp-name">{{ __('Name') }}</label>
                          <input type="text" class="form-control" id="inp-name" name="name" placeholder="{{ __('Enter Name') }}" value="{{ $data->name }}" required>
                        </div>

                        <div class="form-group">
                          <label for="inp-email">{{ __('Email') }}</label>
                          <input type="text" class="form-control" id="inp-email" name="email" placeholder="{{ __('Enter Email') }}" value="{{ $data->email }}" disabled="">
                        </div>

                        <div class="form-group">
                          <label for="inp-email">{{ __('Account Number') }}</label>
                          <input type="text" class="form-control" id="inp-acc" name="account_number" value="{{ $data->account_number }}" disabled="">
                        </div>
                        
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="inp-address">{{ __('Address') }}</label>
                          <input type="text" class="form-control" id="inp-address" name="address" placeholder="{{ __('Enter Address') }}" value="{{ $data->address }}" required>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="inp-city">{{ __('City') }}</label>
                          <input type="text" class="form-control" id="inp-city" name="city" placeholder="{{ __('Enter City') }}" value="{{ $data->city }}" required>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="inp-city">{{ __('Zip Code') }}</label>
                          <input type="text" class="form-control" id="inp-zip" name="zip" placeholder="{{ __('Enter Zip Code') }}" value="{{ $data->zip }}" required>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="inp-vat">{{ __('VAT Number') }}</label>
                          <input type="text" class="form-control" id="inp-vat" name="vat" placeholder="{{ __('Enter VAT') }}" value="{{ $data->vat }}">
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="inp-zip">{{ __('Postal Code') }}</label>
                          <input type="text" class="form-control" id="inp-zip" name="zip" placeholder="{{ __('Enter Zip') }}" value="{{ $data->zip }}" required>
                        </div>
                      </div>
                      <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>

                  </form>
                </div>
              </div>

              <!-- Form Sizing -->
              <!-- Horizontal Form -->
            </div>
          </div>

          <!-- <div class="row mb-3">
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">{{ __('LOAN') }}</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800">{{ count($loans) }}</div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-cash-register fa-2x text-danger"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">{{ __('DPS') }}</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800">{{ count($dps) }}</div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-warehouse fa-2x text-success"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>


            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">{{ __('FDR') }}</div>
                      <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">{{ count($dps) }}</div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-user-shield fa-2x text-success"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">{{ __('WITHDRAW') }}</div>
                      <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">{{ count($withdraws) }}</div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-file-signature fa-2x text-danger"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div> -->
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
</script>
@endsection