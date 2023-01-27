@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="d-sm-flex align-items-center justify-content-between">
            <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Notification') }}</h5>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>

                <li class="breadcrumb-item"><a
                        href="{{ route('admin.actionnotification.index') }}">{{ __('Notification') }}</a></li>
            </ol>
        </div>
    </div>


    <!-- Row -->
    <div class="row mt-3">
        <div class="col-lg-12">
            @include('includes.admin.form-success')
            <div class="card mb-4">
                <div class="table-responsive p-3">
                    <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('User Name') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade confirm-modal" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalTitle"
        aria-hidden="true">
        <form action="" id="notify_form" method="POST" enctype="multipart/form-data">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Update Status') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                    @csrf
                        <div class="modal-body">
                            <p class="text-center">{{ __('You are about to change the status.') }}</p>
                            <p class="text-center">{{ __('Do you want to proceed?') }}</p>
                        </div>
                        <input type="hidden" id="notify_status" name="notify_status">
                        <input type="hidden" id="notify_id" name="notify_id">

                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-success">{{ __('Update') }}</button>
                        </div>
                    </div>
            </div>
        </form>
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
            ajax: '{{ route('admin.actionnotification.datatables') }}',
            columns: [
                { data: 'user_name', name: 'user_name' },
                { data: 'description', name: 'description' },
                { data: 'status', name: 'status' },
            ],
            language: {
                processing: '<img src="{{ asset('assets/images/' . $gs->admin_loader) }}">'
            }
        });


        $('.closed').click(function() {
            $('#modal-success').modal('hide');
        });
        $(document).on('click','.action_notify',function(){
            console.log($(this).data('url'))
            $('#notify_status').val($(this).data('status'));
            $('#notify_id').val($(this).data('id'));
            $('#notify_form').attr('action', $(this).data('url'));
        });
    </script>
@endsection
