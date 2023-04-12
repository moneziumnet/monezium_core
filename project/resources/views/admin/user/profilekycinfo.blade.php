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
    <div class="card  tab-card">
      @include('admin.user.profiletab')

      <div class="tab-content" id="myTabContent">
        <div class="p-3">
          @include('includes.admin.form-success')
        </div>
        <div class="tab-pane fade show active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
            <div class="card-body">
                <div class="card mb-4">
                    <p class="h4 p-3 border-bottom">{{__('KYC Details')}}</p>
                    <div class="row ml-3 mb-2 mt-2" id="user_kyc">
                        <div class="col-md-4 ml-3">
                            <div class="row">
                                <div class="h5 mt-2">{{  __('KYC Status')  }}</div>
                                <div class="btn-group ml-5">
                                    <button type="button" class="btn btn-sm btn-rounded dropdown-toggle btn-{{$status_sign}}" data-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    {{_($status)}}
                                    </button>
                                    <div class="dropdown-menu">
                                    <a class="dropdown-item drop-change" href="javascript:;" data-status="1" data-val="{{ __('Approved') }}" data-href="{{route('admin.user.kyc',['id1' => $data->id, 'id2' => 1])}}">{{ __('Approved') }}</a>
                                    <a class="dropdown-item drop-change" href="javascript:;" data-status="2" data-val="{{ __('Rejected') }}" data-href="{{route('admin.user.kyc',['id1' => $data->id, 'id2' => 2])}}">{{ __('Rejected') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-around">
                                <P class="h5 mt-2">{{__('KYC Info')}}</P>
                                <div class="btn-group mb-1">
                                        <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        {{__("Actions")}}
                                        </button>
                                        <div class="dropdown-menu" x-placement="bottom-start">
                                        <a href="{{$url}}"  class="dropdown-item">{{__("Details")}}</a>
                                        </div>
                                    </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-around">
                                <P class="h5 mt-2">{{__('KYC Method')}}</P>
                                <p class="mt-2">{{__(strtoupper($data->kyc_method))}}</p>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="col-md-8 mt-2">
                        <form action="{{route('admin.manage.kyc.add.more')}}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="user_id" value="{{$data->id}}">

                            <div class="row justify-content-between">
                                <h5 class="p-3  d-flex align-items-center">{{__('Send AML/KYC Request')}}</h5>
                                <div class="row ml-2">
                                    <label for="manual_kyc" class="col-form-label d-flex align-items-center mb-2">{{ __('Select') }}</label>
                                    <div class="col d-flex align-items-center mt-1">
                                    <select class="form-control shadow-none" name="manual_kyc" id="manual_kyc" required>
                                        <option value="" >{{__('Please select')}}</option>
                                        @foreach ($kycforms as $value )
                                            <option value="{{$value->id}}" >{{__($value->name)}}</option>
                                        @endforeach
                                    </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary h-auto ml-4 mb-3 mt-2"><i class="fas fa-plus mr-1"></i>{{ __('Send New Request') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card mb-4">

                    <p class="h4 p-3 border-bottom">{{__('Additional Request History')}}</p>
                    <div class="table-responsive p-3">
                      <table id="geniustable1" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                          <thead class="thead-light">
                          <tr>
                              <th>{{ __("Requirement") }}</th>
                              <th>{{ __("Requested Date") }}</th>
                              <th>{{__("Submitted Date")}}</th>
                              <th>{{__("Details")}}</th>
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

              var table = $('#geniustable1').DataTable({
                  ordering: false,
                  processing: true,
                  serverSide: true,
                  searching: false,
                  ajax: '{{ route('admin.user.more.kyc.datatables', $data->id) }}',
                  columns: [
                          { data: 'title', name: 'title' },
                          { data: 'request_date', name: 'request_date' },
                          { data: 'submitted_date',name: 'submitted_date'},
                          { data: 'detail', searchable: false, orderable: false },
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


      </script>

      @endsection
