@extends('layouts.admin')

@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Edit Branch') }}
    <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.subinstitution.branches',$data->subins_id)}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a>
    </h5>
    <ol class="breadcrumb py-0 m-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.subinstitution.index') }}">{{ __('Sub Institutions List') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.subinstitution.branches',$data->subins_id)}}">{{ __('Braches List') }}</a></li>
    </ol>
  </div>
</div>

<div class="row justify-content-center mt-3">
  <div class="col-md-12">
    <!-- Form Basic -->
    <div class="card mb-4">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">{{ __('Edit Branch Form') }}</h6>
      </div>

      <div class="card-body">

        <form class="geniusform" action="{{route('admin.branch.update',$data->id)}}" method="POST" enctype="multipart/form-data">

          @include('includes.admin.form-both')
          {{ csrf_field() }}
              <div class="form-group">
                <label for="inp-name">{{ __('Branch Name') }}</label>
                <input type="text" pattern="[^()/><\][;!|]+" class="form-control" id="inp-branch-name" name="branch_name" placeholder="{{ __('Enter Branch Name') }}" value="{{$data->name}}" required>
              </div>
          <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Update') }}</button>
        </form>
      </div>
    </div>

  </div>

</div>

@endsection


@section('scripts')


@endsection
