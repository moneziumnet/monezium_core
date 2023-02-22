@extends('layouts.admin')

@section('content')
<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between">
  <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Edit Plan') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.plan.index')}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
  <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="javascript:;">{{ __('Subscription Plan') }}</a></li>
  </ol>
  </div>
</div>

<div class="row justify-content-center mt-3">
<div class="col-md-10">
  <div class="card mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
      <h6 class="m-0 font-weight-bold text-primary">{{ __('Edit Subscription Plan Form') }}</h6>
    </div>

    <div class="card-body">
      <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
      <form class="geniusform" action="{{route('admin.plan.update',$data->id)}}" method="POST" enctype="multipart/form-data">

          @include('includes.admin.form-both')

          {{ csrf_field() }}

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="name">{{ __('Name') }}</label>
                <input type="text" pattern="[^À-ž()/><\][\\\-;&$@!|]+" class="form-control" id="name" name="name" placeholder="{{ __('Enter Name') }}" value="{{ $data->name }}" required>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="price">{{ __('Price') }}</label>
                <input type="number" class="form-control" id="price" name="price" placeholder="{{ __('Enter Price') }}" min="0" value="{{ $data->price }}" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="duration">{{ __('Duration') }}</label>
                <input type="number" class="form-control" id="duration" name="duration" placeholder="{{ __('Enter Duration') }}" value="{{ $data->duration }}" required>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="durationtype">{{ __('Duration Type') }}</label>
                <select class="form-control" size="1" name="durationtype" required>
                  <option value="Month" {{ $data->durationtype == __('Month') ? 'selected' : '' }}>{{ __('Month') }}</option>
                  <option value="Year" {{ $data->durationtype == __('Year') ? 'selected' : '' }}>{{ __('Year') }}</option>
                </select>
              </div>
            </div>
          </div>

          <button type="submit" id="submit-btn" class="btn btn-primary w-100 mt-2">{{ __('Submit') }}</button>

      </form>
    </div>
  </div>
</div>

</div>

@endsection

@section('scripts')
  <script type="text/javascript">
    "use strict";
    function isEmpty(el){
        return !$.trim(el.html())
    }


  $("#lang-btn").on('click', function(){

      $("#lang-section").append(''+
                                  '<div class="lang-area mb-3">'+
                                    '<span class="remove lang-remove"><i class="fas fa-times"></i></span>'+
                                    '<div class="row">'+
                                      '<div class="col-md-12">'+
                                      '<input type="text" pattern="[^À-ž()/><\\][\\\\;&$@!|]+" class="form-control" name="attribute[]" placeholder="{{ __('Enter Plan Attribute') }}" value="" required>'+
                                      '</div>'+
                                    '</div>'+
                                  '</div>'+
                              '');

  });

  $(document).on('click','.lang-remove', function(){

      $(this.parentNode).remove();

  });

  </script>

@endsection
