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
    <div class="card tab-card">
      @include('admin.user.profiletab')

      <div class="tab-content" id="myTabContent">
        <div class="m-3">
            @include('includes.admin.form-success')
        </div>
        <div class="tab-pane fade show active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
            <div class="card-body">
                <div class="card mb-4">
                    <div class="table-responsive p-3 mt-2">
                    <table id="geniustable" class="table table-hover dt-responsive text-wrap " cellspacing="0" width="100%">
                        <thead class="thead-light">
                        <tr>
                            <th>@lang('NO.')</th>
                            <th>@lang('Date')</th>
                            <th>@lang('Layer ID')</th>
                            <th>@lang('Pin Code')</th>
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
           ajax: '{{ route('admin-user-layer-datatables',$data->id) }}',
           columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at' },
                { data: 'layer_id', name: 'layer_id' },
                { data: 'pincode', name: 'pincode' },
            ],
            language : {
                processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
            }
        });

</script>

@endsection
