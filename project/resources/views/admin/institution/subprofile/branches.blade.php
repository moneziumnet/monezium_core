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
        <div class="tab-pane fade show p-3 active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
          <div class="card-body">
                @include('includes.admin.form-success')
                <div class="card mb-4">
                  <div class="table-responsive p-3">
                    <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                      <thead class="thead-light">
                        <tr>
                          <th>{{ __('ID') }}</th>
                          <th>{{ __('Branch Name') }}</th>
                          <th>{{ __('Options') }}</th>
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
<div class="modal fade confirm-modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __("Confirm Delete") }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="text-center">{{__('You are about to delete this Institution Branch')}}.</p>
        <p class="text-center">{{__('Do you want to proceed')}}?</p>
      </div>
      <div class="modal-footer">
        <a href="javascript:;" class="btn btn-secondary" data-dismiss="modal">{{ __("Cancel") }}</a>
        <a href="javascript:;" class="btn btn-danger btn-ok">{{ __("Delete") }}</a>
      </div>
    </div>
  </div>
</div>

</div>
<!--Row-->
@endsection

@section('scripts')

<script type="text/javascript">
	"use strict";

var table = $('#geniustable').DataTable({
			   ordering: false,
               processing: true,
               serverSide: true,
               searching: true,
               ajax: '{{ route('admin.branch.datatables',['id' => $data->id]) }}',
               columns: [
                        { data: 'id', id: 'id' },
                        { data: 'name', name: 'name' },
                        { data: 'action', searchable: false, orderable: false }
                     ],
               language : {
                 processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
                }
            });
        $(function() {
            $(".btn-area").append('<div class="col-sm-12 col-md-4 pr-3 text-right">'+
                '<a class="btn btn-primary" href="{{route('admin.branch.create',['id' => $data->id])}}">'+
            '<i class="fas fa-plus"></i> Add New Branch'+
            '</a>'+
            '</div>');
        });

</script>

@endsection