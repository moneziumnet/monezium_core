@extends('layouts.admin')
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
        <div class="tab-pane fade show p-1 active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
          <div class="card-body">
            <form id="request-form" action="{{ route('admin-user.createfile',$data->id) }}" method="POST" enctype="multipart/form-data">
              @include('includes.admin.form-flash')
              {{ csrf_field() }}
              <div class="form-group">
                <label for="inp-name">{{ __('Name') }}</label>
                <input name="document_name" class="form-control" autocomplete="off" placeholder="{{__('Name')}}" type="text" pattern="[^()/><\][;!|]+" required>
              </div>
              <div class="form-group">
                <label for="full-name">{{ __('Choose File') }}</label>
            <input type="file" class="form-control" id="document_file" name="document_file" required>
              </div>
              <div class="form-group">
                <button type="submit" class="btn btn-primary w-100">{{__('Submit')}}</button>
              </div>
            </form>
          </div>



      </div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
<!--Row-->
@endsection
