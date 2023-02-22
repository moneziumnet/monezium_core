@extends('layouts.admin')
@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Profile of Sub Institution') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.subinstitution.index')}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
    <h5 class=" mb-0 text-gray-800 pl-3">{{ $data->name }}</h5>
    <ol class="breadcrumb py-0 m-0">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.subinstitution.index') }}">{{ __('Sub Institutions List') }}</a></li>

    </ol>
  </div>
</div>

<div class="row mt-3">
  <div class="col-lg-12">
    <div class="card mt-1 tab-card">
      @include('admin.institution.subprofile.tab')

      <div class="tab-content" id="myTabContent">
        @php
        $currency = defaultCurr();
        @endphp
        <div class="tab-pane fade show p-3 active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
          <div class="card-body">
            <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
            <form class="geniusform" action="{{route('admin.institution.update',$data->id)}}" method="POST" enctype="multipart/form-data">
              @include('includes.admin.form-both')
              {{ csrf_field() }}

              <div class="form-group">
                <label>{{ __('Set Picture') }} <small class="small-font">({{ __('Preferred Size 600 X 600') }})</small></label>
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
                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="inp-name" name="name" placeholder="{{ __('Enter Name') }}" value="{{$data->name}}" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="inp-name">{{ __('VAT Number') }}</label>
                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="vat" name="vat" placeholder="{{ __('Enter VAT Number') }}" value="{{$data->vat}}">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="inp-name">{{ __('Address') }}</label>
                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="address" name="address" placeholder="{{ __('Enter Address') }}" value="{{$data->address}}">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="inp-name">{{ __('City') }}</label>
                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="city" name="city" placeholder="{{ __('Enter City') }}" value="{{$data->city}}">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="inp-name">{{ __('Zip Code') }}</label>
                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="zip" name="zip" placeholder="{{ __('Enter Zip Code') }}" value="{{$data->zip}}">
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
                    <input type="number" class="form-control" id="inp-phone" name="phone" placeholder="{{ __('Enter Phone') }}" value="{{$data->phone}}" required>
                  </div>
                </div>
              </div>
              <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>

@endsection
@section('scripts')
<script type="text/javascript">
</script>
@endsection
