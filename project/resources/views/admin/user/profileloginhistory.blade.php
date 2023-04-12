@extends('layouts.admin')
@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ $data->company_name ?? $data->name }}</h5>
    <ol class="breadcrumb py-0 m-0">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.user.index') }}">{{ __('User List') }}</a></li>
    </ol>
  </div>
</div>


<div class="row mt-3">
  <div class="col-lg-12">
    <div class="card tab-card">
      @include('admin.user.profiletab')

      <div class="tab-content" id="myTabContent">
        @include('includes.admin.form-success')
        <div class="tab-pane fade show active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
        <div class="card-body">
          <div class="table-responsive">
            <table id="geniustable" class="table table-hover dt-responsive text-wrap " cellspacing="0" width="100%">
              <thead class="thead-light">
               <tr>
                <th>@lang('Date')</th>
                <th>@lang('Description')</th>
                <th >@lang('IP')</th>
                <th>@lang('User Agent')</th>
               </tr>
              </thead>
            </table>
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
	"use strict";

    var table = $('#geniustable').DataTable({
           ordering: false,
           processing: true,
           serverSide: true,
           searching: true,
           ajax: '{{ route('admin-user.login-history-datatables',$data->id) }}',
           columns: [
                { data: 'created_at', name: 'created_at' },
                { data: 'subject', name: 'subject' },
                { data: 'ip', name: 'ip' },
                { data: 'agent', name: 'agent' },
            ],
            language : {
                processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
            }
        });

</script>

@endsection
