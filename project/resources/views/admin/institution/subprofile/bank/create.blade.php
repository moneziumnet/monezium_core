@extends('layouts.admin')

@section('content')
<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Add New Bank') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.subinstitution.banks',$data->id)}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
    <ol class="breadcrumb m-0 py-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.subinstitution.index') }}">{{ __('Sub Institutions List') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.subinstitution.banks',$data->id)}}">{{ __('Banks List') }}</a></li>

    </ol>
  </div>
</div>

<div class="row justify-content-center mt-3">
<div class="col-md-10">
  <div class="card mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
      <h6 class="m-0 font-weight-bold text-primary">{{ __('Add New Bank Form') }}</h6>
    </div>

    <div class="card-body">
      <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
      <form class="geniusform" action="{{route('admin.subinstitution.banks.store')}}" method="POST" enctype="multipart/form-data">

          @include('includes.admin.form-both')

          {{ csrf_field() }}

          <input type="hidden" name="ins_id" value="{{ $data->id }}">
          <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                    <label for="name">{{ __('Bank Name') }}</label>
                    <input type="text" pattern="[^()/><\][;!|]+" class="form-control" id="name" name="name" placeholder="{{ __('Enter Bank Name') }}" value="" required>
                  </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                    <label for="address">{{ __('Bank Address') }}</label>
                    <input type="text" pattern="[^()/><\][;!|]+" class="form-control" id="address" name="address" placeholder="{{ __('Bank Address') }}"  value="" required>
                  </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                    <label for="min_limit">{{ __('Minimum Amount') }} </label>
                    <input type="number" step="any" class="form-control" id="min_limit" name="min_limit" placeholder="{{ __('0') }}" min="0" value="" required>
                  </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                    <label for="max_limit">{{ __('Maximum Amount') }} </label>
                    <input type="number" step="any" class="form-control" id="max_limit" name="max_limit" placeholder="{{ __('0') }}" min="1" value="" required>
                  </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                    <label for="fixed_charge">{{ __('Fixed Charge') }}</label>
                    <input type="number" step="any" class="form-control" id="fixed_charge" name="fixed_charge" placeholder="{{ __('0') }}" min="0" value="" required>
                  </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                    <label for="percent_charge">{{ __('Percent Charge') }} (%)</label>
                    <input type="number" step="any" class="form-control" id="percent_charge" name="percent_charge" placeholder="{{ __('0') }}" min="0" value="" required>
                  </div>
              </div>

         </div>
         <hr>
         <h6 class="m-0 font-weight-bold text-primary mb-3">{{ __('Gateway Details') }}</h6>

         <div class="row">
            <div class="ml-3">
                <h6 class="mt-2">@lang('Select Type:')</h4>
            </div>
            <div class="col-3 mr-3">
                <select class="col-lg select mb-3 input-field" id="bankgateway" name="bankgateway">
                    <option value=""> {{'Please select'}} </option>
                    @foreach ($bank_gateways as $gateway)
                        <option value="{{$gateway}}"> {{$gateway->name}} </option>
                    @endforeach
                </select>
            </div>

        </div>
        <div class="row" id="gateway_detail">

        </div>
        <div class="row d-flex justify-content-center">
            <button type="submit" id="submit-btn" class="btn btn-primary w-100 mt-3">{{ __('Submit') }}</button>
        </div>

      </form>
    </div>
  </div>
</div>

</div>

@endsection
@section('scripts')
<script type="text/javascript">
    $('#bankgateway').on('change', function() {
        var bk_info = $('#bankgateway').val();
        var id = 1;
        $('#gateway_detail').html('');
        if (bk_info) {

            $.each(JSON.parse(bk_info).information, function(key, value) {
                $('#gateway_detail').append(''+`<div class="col-md-6">
                    <div class="form-group">
                        <label for="iban">${key}</label>
                        <input type="text" pattern="[^()/><\\][;!|]+" class="form-control iban-input" id="iban" name="key[${key}]"  min="1" value="${value}" required>
                        <small class="text-danger iban-validation"></small>
                      </div>
                  </div>`+'');
                  id++;
            })
        }
})
</script>
@endsection

