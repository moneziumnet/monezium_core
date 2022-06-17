@extends('layouts.admin')

@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Add New Branch') }} 
      <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.branch.index')}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
    <ol class="breadcrumb m-0 py-0">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.branch.index') }}">{{ __('Branch Management') }}</a></li>
      <li class="breadcrumb-item"><a href="{{route('admin.branch.create')}}">{{ __('Add New Branch') }}</a></li>
    </ol>
  </div>
</div>

<div class="row justify-content-center mt-3">
  <div class="col-md-12">
    <!-- Form Basic -->
    <div class="card mb-4">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">{{ __('Add New Branch Form') }}</h6>
      </div>

      <div class="card-body">

        <form class="geniusform" action="{{route('admin.branch.store')}}" method="POST" enctype="multipart/form-data">

          @include('includes.admin.form-both')
          {{ csrf_field() }}

          <div class="row g-3">
            <div class="col-md-6">
              <div class="form-group">
                <label for="inp-name">{{ __('Sub Institution Name') }}</label>

                <select class="form-control mb-3" name="ins_id" id="ins_id">
                  <option value="">{{ __('Select Sub Institution Name') }}</option>
                  @foreach(DB::table('admins')->where('id','!=',1)->get() as $institute)
                  <option value="{{ $institute->id }}">{{ $institute->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="inp-branch-name">{{ __('Branch Name') }}</label>
                <input type="text" class="form-control" id="inp-branch-name" name="branch_name" placeholder="{{ __('Enter Branch Name') }}" value="" autocomplete="off" required>
              </div>
            </div>
          </div>


          <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>
        </form>
      </div>
    </div>
  </div>

</div>
@endsection


@section('scripts')


@endsection