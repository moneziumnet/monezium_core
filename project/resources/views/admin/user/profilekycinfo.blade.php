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
    <div class="card  tab-card">
      @include('admin.user.profiletab')

      <div class="tab-content" id="myTabContent">
        <div class="p-3">
          @include('includes.admin.form-success')
        </div>
        <div class="tab-pane fade show active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
            <div class="card-body">
                <div class="card mb-4">
                    <p class="h4 p-3">{{__('KYC Details')}}</p>
                    <div class="table-responsive p-3">
                    <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                        <thead class="thead-light">
                        <tr>
                            <th>{{ __("Name") }}</th>
                            <th>{{ __("Email") }}</th>
                            <th>{{__('KYC')}}</th>
                            <th>{{ __("KYC Info") }}</th>
                            <th>{{ __("KYC Method   ") }}</th>
                        </tr>
                        </thead>
                    </table>
                    </div>
                    <hr>
                    <p class="h4 p-3">{{__('Additional Request History')}}</p>
                    <div class="table-responsive p-3 mt-3">
                      <table id="geniustable1" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                          <thead class="thead-light">
                          <tr>
                              <th>{{ __("Requirement") }}</th>
                              <th>{{ __("Requested Date") }}</th>
                              <th>{{__("Submitted Date")}}</th>
                              <th>{{ __("Status") }}</th>
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

      {{-- STATUS MODAL --}}
      <div class="modal fade confirm-modal" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __("Update Status") }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-center">{{ __("You are about to change the status.") }}</p>
                    <p class="text-center">{{ __("Do you want to proceed?") }}</p>
                </div>

                <div class="modal-footer">
                    <a href="javascript:;" class="btn btn-secondary" data-dismiss="modal">{{ __("Cancel") }}</a>
                    <a href="javascript:;" class="btn btn-success btn-ok">{{ __("Update") }}</a>
                </div>
            </div>
        </div>
    </div>
      {{-- STATUS MODAL ENDS --}}

      {{-- MORE STATUS MODAL --}}
      <div class="modal fade confirm-modal" id="statusModal1" tabindex="-1" role="dialog" aria-labelledby="statusModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __("Update Status") }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-center">{{ __("You are about to change the kyc status.") }}</p>
                    <p class="text-center">{{ __("Do you want to proceed?") }}</p>
                </div>

                <div class="modal-footer">
                    <a href="javascript:;" class="btn btn-secondary" data-dismiss="modal">{{ __("Cancel") }}</a>
                    <a href="javascript:;" class="btn btn-success btn-ok">{{ __("Update") }}</a>
                </div>
            </div>
        </div>
    </div>
      {{-- MORE STATUS MODAL ENDS --}}


      @endsection



      @section('scripts')

      <script type="text/javascript">
          'use strict';

              var table = $('#geniustable').DataTable({
                     ordering: false,
                     processing: true,
                     serverSide: true,
                     searching: false,
                     bPaginate: false,
                     bInfo : false,
                     ajax: '{{ route('admin.user.kyc.datatables', $data->id) }}',
                     columns: [
                              { data: 'name', name: 'name' },
                              { data: 'email', name: 'email' },
                              { data: 'kyc',searchable: false, orderable: false},
                              { data: 'action', searchable: false, orderable: false },
                              { data: 'kyc_method', name: 'kyc_method' },
                           ],
                      language : {
                          processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
                      }
                  });
              var table1 = $('#geniustable1').DataTable({
                  ordering: false,
                  processing: true,
                  serverSide: true,
                  searching: false,
                  ajax: '{{ route('admin.user.more.kyc.datatables', $data->id) }}',
                  columns: [
                          { data: 'title', name: 'title' },
                          { data: 'request_date', name: 'request_date' },
                          { data: 'submitted_date',name: 'submitted_date'},
                          { data: 'action', searchable: false, orderable: false },
                        ],
                  language : {
                      processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
                  }
              });
              $(function() {
                  $("#geniustable1_wrapper .btn-area").append('<div class="col-sm-12 col-md-4 pr-3 text-right">'+
                      '<a class="btn btn-primary" href="{{route('admin.kyc.more.form.create', $data->id)}}">'+
                  '<i class="fas fa-plus"></i> {{ __('Send New Request') }}'+
                  '</a>'+
                  '</div>');
              });
              $('#statusModal1 .btn-ok').on('click', function(e) {
                table1.ajax.reload();
              });


      </script>

      @endsection
