@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="d-sm-flex align-items-center justify-content-between">
            <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Report Statistic') }}</h5>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>

                <li class="breadcrumb-item"><a
                        href="{{ route('admin.report.transaction.index') }}">{{ __('ICO Token') }}</a></li>
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
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Transaction ID') }}</th>
                                <th>{{ __('Bank Name') }}</th>
                                <th>{{ __('Sender') }}</th>
                                <th>{{ __('Receiver') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Action') }}</th>

                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade confirm-modal" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Update Status') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <p class="text-center">{{ __('You are about to change the status.') }}</p>
                    <p class="text-center">{{ __('Do you want to proceed?') }}</p>
                </div>

                <div class="modal-footer">
                    <a href="javascript:;" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</a>
                    <a href="javascript:;" class="btn btn-success btn-ok">{{ __('Update') }}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-status bg-primary"></div>
                <div class="modal-body text-center py-4">
                    <i class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                    <h3>@lang('ICO Token Details')</h3>
                    <ul class="list-group mt-2 ico-token-details">
                        <li class="list-group-item d-flex justify-content-between" style="word-break:break-all;">
                            @lang('Hash')<span id="hash" style="margin-left: 60px"></span></li>
                        <li class="list-group-item d-flex justify-content-between" style="word-break:break-all;">
                            @lang('Receiver Crypto Address')<span id="address" style="margin-left: 60px"></span></li>
                        <li class="list-group-item d-flex justify-content-between" style="word-break:break-all;">
                            @lang('Customer Crypto Address')<span id="sender_address" style="margin-left: 60px"></span></li>
                        <li class="list-group-item d-flex justify-content-between" style="word-break:break-all;">
                            @lang('Amount')<span id="amount" style="margin-left: 60px"></span></li>
                        <li class="list-group-item d-flex justify-content-between" id="li_document">@lang('Proof')<span>
                                <a id="proof" attributes-list download> {{ __('Download Proof') }} </a> </span></li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col"><a href="javascript:;" class="btn w-100 closed" data-bs-dismiss="modal">
                                    @lang('Close')
                                </a>
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
        $(document).ready(function () {

            $('#geniustable tfoot th').each(function () {
                var title = $(this).text();
                $(this).html('<input type="text" placeholder="Search ' + title + '" />');
            });
        var table = $('#geniustable').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            searching: true,
            initComplete: function () {
            // Apply the search
            this.api()
                .columns()
                .every(function () {
                    var that = this;

                    $('input', this.footer()).on('keyup change clear', function () {
                        if (that.search() !== this.value) {
                            that.search(this.value).draw();
                        }
                    });
                });
            },
            ajax: '{{ route('admin.report.transaction.datatables') }}',
            columns: [
                { data: 'date', name: 'date' },
                { data: 'trnx_no', name: 'trnx_no' },
                { data: 'bank_name', name: 'bank_name' },
                { data: 'sender_name', name: 'sender_name' },
                { data: 'receiver_name', name: 'receiver_name' },
                { data: 'amount', name: 'amount' },
                { data: 'type', name: 'type' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action' },
            ],
            language: {
                processing: '<img src="{{ asset('assets/images/' . $gs->admin_loader) }}">'
            }
        });

        function getDetails(id) {
            var url = "{{url('admin/ico/details')}}"+'/'+id
            $.get(url,function (res) {
                $('.ico-token-details').html(res);
                $('#modal-success').modal('show')
            })
        }

        $('.closed').click(function() {
            $('#modal-success').modal('hide');
        });
    });
    </script>
@endsection
