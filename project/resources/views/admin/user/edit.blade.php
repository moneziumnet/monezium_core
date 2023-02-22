@extends('layouts.admin')
@section('styles')
    <style type="text/css">
        .ms-options-wrap,
        .ms-options-wrap * {
            box-sizing: border-box;
        }
.ms-options ul li{
    list-style: none;
    margin-left: -40px;
}

.ms-options-wrap > button:focus,
.ms-options-wrap > button {
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

.ms-options-wrap > button:after {
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

.ms-options-wrap > .ms-options {
    position: absolute;
    left: 0;
    width: 100%;
    margin-top: 1px;
    margin-bottom: 20px;
    background: white;
    z-index: 2000;
    border: 1px solid #d1d3e2;
    text-align:left;
}

.ms-options-wrap > .ms-options > .ms-search input {
    width: 100%;
    padding: 4px 5px;
    border: none;
    border-bottom: 1px groove;
    outline: none;
}

.ms-options-wrap > .ms-options .ms-selectall {
    display: inline-block;
    font-size: .9em;
    text-transform: lowercase;
    text-decoration: none;
}
.ms-options-wrap > .ms-options .ms-selectall:hover {
    text-decoration: underline;
}

.ms-options-wrap > .ms-options > .ms-selectall.global {
    margin: 4px 5px;
}

.ms-options-wrap > .ms-options > ul > li.optgroup {
    padding: 5px;
}
.ms-options-wrap > .ms-options > ul > li.optgroup + li.optgroup {
    border-top: 1px solid #aaa;
}

.ms-options-wrap > .ms-options > ul > li.optgroup .label {
    display: block;
    padding: 5px 0 0 0;
    font-weight: bold;
}

.ms-options-wrap > .ms-options > ul label {
    position: relative;
    display: inline-block;
    width: 100%;
    padding: 2px 3px;
    margin: 1px 0;
}

.ms-options-wrap > .ms-options > ul li.selected label,
.ms-options-wrap > .ms-options > ul label:hover {
    background-color: #efefef;
}

.ms-options-wrap > .ms-options > ul input[type="checkbox"] {
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
        <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Edit Customer') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.user.index')}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="javascript:;">{{ __('Customer Edit') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.user.index') }}">{{ __('Customer List') }}</a></li>
            <li class="breadcrumb-item"><a href="{{route('admin-user-edit',$data->id)}}">{{ __('Edit Customer') }}</a></li>
        </ol>
    </div>
</div>

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
                                <input type="text" pattern="[^()/><\][\\\-;&$@!|]+" class="form-control" id="inp-name" name="name" placeholder="{{ __('Enter Name') }}" value="{{ $data->name }}" required>
                            </div>

                            <div class="form-group">
                                <label for="inp-email">{{ __('Email') }}</label>
                                <input type="email" class="form-control" id="inp-email" name="email" placeholder="{{ __('Enter Email') }}" value="{{ $data->email }}" disabled="">
                            </div>
                            <div class="form-group">
                                <label for="inp-phone">{{ __('Phone') }}</label>
                                <input type="number" class="form-control" id="inp-phone" name="phone" placeholder="{{ __('Enter Phone') }}" value="{{ $data->phone }}" required>
                            </div>
                                    @php
                                        $userType = explode(',', $data->user_type);
                                    @endphp

                            <div class="form-group">
                                <label for="inp-name">{{ __('Type') }}</label>

                                <select class="select mb-3" name="user_type[]" multiple  id="user_type">
                                    {{-- <option value="">{{ __('Select Customer Type') }}</option> --}}
                                    @foreach(DB::table('customer_types')->orderBy('type_name','asc')->get() as $c_type)
                                    <option value="{{ $c_type->id }}" @if(in_array($c_type->id, $userType)) selected @endif>{{ $c_type->type_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="inp-address">{{ __('Address') }}</label>
                                <input type="text" pattern="[^()/><\][\\;&$@!|]+" class="form-control" id="inp-address" name="address" placeholder="{{ __('Enter Address') }}" value="{{ $data->address }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="inp-city">{{ __('City') }}</label>
                                <input type="text" pattern="[^()/><\][\\;&$@!|]+" class="form-control" id="inp-city" name="city" placeholder="{{ __('Enter City') }}" value="{{ $data->city }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="inp-vat">{{ __('VAT Number') }}</label>
                                <input type="text" pattern="[^()/><\][\\;&$@!|]+" class="form-control" id="inp-vat" name="vat" placeholder="{{ __('Enter VAT') }}" value="{{ $data->vat }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="inp-zip">{{ __('Postal Code') }}</label>
                                <input type="text" pattern="[^()/><\][\\;&$@!|]+" class="form-control" id="inp-zip" name="zip" placeholder="{{ __('Enter Zip') }}" value="{{ $data->zip }}" required>
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

