
@extends('layouts.admin')

@section('content')


    <div class="card">
        <div class="d-sm-flex align-items-center py-3 justify-content-between">
        <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Message History') }}</h5>
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{route('admin.user.message')}}">{{ __('Manage Message') }}</a></li>
        </ol>
        </div>
    </div>


    <!-- Row -->
    <div class="row mt-3">
      <!-- Datatables -->
      <div class="col-lg-12">

        @include('includes.admin.form-success')

        <div class="card mb-4">

            <div class="table-responsive p-3">
                <div class="btn-list align-items-center">
                    <div class="d-flex justify-content-center row">
                        <div class="form-group mr-3 col-lg-2">
                            <label for="name" class="form-label">{{ __('Customer') }}</label>
                            <input class="form-control shadow-none mr-2" type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" placeholder="{{__('Name')}}" id="name" name="name" >
                        </div>
                        <div class="form-group mr-3  col-lg-2">
                            <label for="department" class="form-label">{{ __('Department') }}</label>
                            <input class="form-control shadow-none mr-2" type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" placeholder="{{__('Department')}}" id="department" name="department" >
                        </div>
                        <div class="form-group mr-3  col-lg-2">
                            <label for="priority" class="form-label">{{ __('Priority') }}</label>
                            <select name="priority" id="priority" class="form-control mr-2 shadow-none" >
                                <option value="">@lang('All')</option>
                                <option value="High">@lang('High')</option>
                                <option value="Medium">@lang('Medium')</option>
                                <option value="Low">@lang('Low')</option>
                            </select>
                        </div>
                        <div class="form-group mr-3  col-lg-2">
                            <label for="status" class="form-label">{{ __('Status') }}</label>
                            <select name="status" id="status" class="form-control mr-2 shadow-none" >
                                <option value="">@lang('All')</option>
                                <option value="open">@lang('Open')</option>
                                <option value="closed">@lang('Closed')</option>
                            </select>
                        </div>
                    </div>
                </div>
                <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                <thead class="thead-light">
                    <tr>
                        <th>{{__('Name')}}</th>
                        <th>{{__('Subject')}}</th>
                        <th>{{__('Department')}}</th>
                        <th>{{__('Message')}}</th>
                        <th>{{__('Priority')}}</th>
                        <th>{{__('Status')}}</th>
                        <th>{{__('Date')}}</th>
                        <th>{{__('Options')}}</th>
                    </tr>
                </thead>
                </table>
            </div>
        </div>
      </div>
      <!-- DataTable with Hover -->

    </div>

    {{-- Ticket Close MODAL --}}
    <div class="modal fade confirm-modal" id="closeModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ __("Confirm Close") }}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body">
            <p class="text-center">{{__("You are about to Close this Ticket.")}}</p>
            <p class="text-center">{{ __("Do you want to proceed?") }}</p>
          </div>

          <div class="modal-footer">
            <a href="javascript:;" class="btn btn-secondary" data-dismiss="modal">{{ __("Cancel") }}</a>
            <a href="javascript:;" class="btn btn-danger btn-ok">{{ __("Close") }}</a>
          </div>
        </div>
      </div>
    </div>
    {{-- DELETE MODAL ENDS --}}


@endsection


@section('scripts')

<script type="text/javascript">
"use strict";

    var table = $('#geniustable').DataTable({
        ordering: false,
        processing: true,
        serverSide: true,
        searching: false,
        ajax:
        {
            url : "{{  route('admin.message.datatables') }}",
            data : function (d) {
                d.name = $('#name').val(),
                d.department = $('#rdepartment').val(),
                d.priority = $('#priority').val(),
                d.status = $('#status').val()
            }
        },
        columns: [
            { data: 'name', name: 'name' },
            { data: 'subject', name: 'subject' },
            { data: 'department', name: 'department' },
            { data: 'message', name: 'message' },
            { data: 'priority', name: 'priority' },
            { data: 'status', name: 'status'},
            { data: 'created_at', name: 'created_at'},
            { data: 'action', searchable: false, orderable: false }

                ],
        language: {
            processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
        },

    });
    $('#name').on('keyup', function () {
        table.draw();
    });
    $('#department').on('keyup', function () {
        table.draw();
    });
    $('#priority').on('change', function () {
        table.draw();
    });
    $('#status').on('change', function () {
        table.draw();
    });
    </script>

@endsection
