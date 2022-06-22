@extends('layouts.front')

@push('css')

@section('content')


<section class="account-section pt-100 pb-100">
  <div class="container">
    <div class="account-wrapper bg--body">
      <div class="section-title mb-3">
        <h3 class="title">@lang('Register Institution Now')</h3>
      </div>
      <div class="card mt-3 tab-card">
        <div class="card-header tab-card-header">
          <ul class="nav nav-pills card-header-tabs" id="pills-tab" role="tablist">
            <li class="nav-item">
              <a class="nav-link" id="one-tab" data-toggle="tab" href="#one" role="tab" aria-controls="One" aria-selected="true">Information</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="two-tab" data-toggle="tab" href="#two" role="tab" aria-controls="Two" aria-selected="false">Documents</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="three-tab" data-toggle="tab" href="#three" role="tab" aria-controls="Three" aria-selected="false">Modules</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="four-tab" data-toggle="tab" href="#four" role="tab" aria-controls="Four" aria-selected="false">Contacts</a>
            </li>
          </ul>
        </div>

        <div class="tab-content" id="myTabContent">
          <div class="tab-pane fade show active p-3" id="one" role="tabpanel" aria-labelledby="one-tab">
            <form class="geniusform" action="{{ route('user.institution.profile.submit',$data->id) }}" method="POST" enctype="multipart/form-data">

              {{ csrf_field() }}

              <div class="row g-3">
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="inp-name">{{ __('Company Name') }}</label>
                    <input type="text" class="form-control" id="inp-name" name="name" placeholder="{{ __('Enter Name') }}" value="{{$data->name}}" required>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="inp-name">{{ __('VAT Number') }}</label>
                    <input type="text" class="form-control" id="vat" name="vat" placeholder="{{ __('Enter VAT Number') }}" value="{{$data->vat}}" required>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="inp-name">{{ __('Address') }}</label>
                    <input type="text" class="form-control" id="address" name="address" placeholder="{{ __('Enter Address') }}" value="{{$data->address}}" required>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="inp-name">{{ __('City') }}</label>
                    <input type="text" class="form-control" id="city" name="city" placeholder="{{ __('Enter City') }}" value="{{$data->city}}" required>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="inp-name">{{ __('Zip Code') }}</label>
                    <input type="text" class="form-control" id="zip" name="zip" placeholder="{{ __('Enter Zip Code') }}" value="{{$data->zip}}" required>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="inp-name">{{ __('Select Country') }}</label>
                    <select class="form-control mb-3" name="country_id" required>
                      <option value="">{{ __('Select Country') }}</option>
                      @foreach(DB::table('countries')->get() as $dta)
                      <option value="{{ $dta->id }}" {{ $data->country_id == $dta->id ? 'selected' : '' }}>{{ $dta->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="inp-email">{{ __('Email of Institution') }}</label>
                    <input type="email" class="form-control" id="inp-email" name="email" placeholder="{{ __('Enter Email') }}" value="{{$data->email}}" required>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="inp-phone">{{ __('Phone of Institution') }}</label>
                    <input type="text" class="form-control" id="inp-phone" name="phone" placeholder="{{ __('Enter Phone') }}" value="{{$data->phone}}" required>
                  </div>
                </div>

              <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>
              </div>
            </form>
          </div>

          <div class="tab-pane fade p-3" id="two" role="tabpanel" aria-labelledby="two-tab">

          </div>

          <div class="tab-pane fade p-3" id="three" role="tabpanel" aria-labelledby="three-tab">

          </div>

          <div class="tab-pane fade p-3" id="four" role="tabpanel" aria-labelledby="four-tab">
            <div class="table-responsive p-2">
            </div>
          </div>

        </div>


      </div>
    </div>
  </div>
</section>




@endsection
@section('scripts')
<script type="text/javascript">
  "use strict";
  $('#myTab a').on('click', function(e) {
    e.preventDefault()
    $(this).tab('show')
  })

  $('.datepicker').datepicker({
    format: 'dd-mm-yyyy',
    autoclose: true
  });
</script>
@endsection