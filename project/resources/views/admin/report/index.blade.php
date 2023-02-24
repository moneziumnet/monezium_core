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
                    <div class="btn-list align-items-center">
                        <div class="d-flex justify-content-center">
                            <div class="form-group mr-3">
                                <label class="form-label">{{ __('Type') }}</label>

                                <select name="type" class="form-control mr-2 shadow-none" >
                                    <option value="{{filter('remark','')}}">@lang('All')</option>
                                    <option value="{{filter('remark','')}}">@lang('External')</option>
                                    <option value="{{filter('remark','')}}">@lang('Deposit')</option>
                                </select>
                            </div>
                            <div class="form-group mr-3">
                                <label  class="form-label">{{ __('Bank') }}</label>
                                <select name="bank_name" class="form-control mr-2 shadow-none" >
                                    <option value="{{filter('wallet_id','')}}">@lang('All Account')</option>
                                    <option value="{{filter('wallet_id','')}}">@lang('Openpayd')</option>
                                    <option value="{{filter('wallet_id','')}}">@lang('ClearJunction')</option>
                                    <option value="{{filter('wallet_id','')}}">@lang('Swan')</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center">
                            <div class="form-group mr-3">
                                <label for="s_time" class="form-label">{{ __('Start Time') }}</label>
                                <input class="form-control shadow-none mr-2" type="date" placeholder="{{__('Start Time')}}" id="s_time" name="s_time" >
                            </div>
                            <div class="form-group mr-3">
                                <label for="e_time" class="form-label">{{ __('Close time') }}</label>
                                <input class="form-control shadow-none mr-2" type="date" placeholder="{{__('End Time')}}" id="e_time" name="e_time" >
                            </div>
                            <div class="form-group mr-3">
                                <label for="sender_name" class="form-label">{{ __('Sender') }}</label>
                                <input class="form-control shadow-none mr-2" type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" placeholder="{{__('Sender Name')}}" id="sender_name" name="sender_name" >
                            </div>
                            <div class="form-group mr-3">
                                <label for="receiver_name" class="form-label">{{ __('Receiver') }}</label>
                                <input class="form-control shadow-none mr-2" type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" placeholder="{{__('Rceiver Name')}}" id="receiver_name" name="receiver_name" >
                            </div>
                            <div class="form-group mr-3">
                                <label for="trnx_no" class="form-label">{{ __('Transaction ID') }}</label>
                                <input class="form-control shadow-none mr-2" type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" placeholder="{{__('Transaction ID')}}" id="trnx_no" name="trnx_no" >
                            </div>
                        </div>
                    </div>
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
            // var sender_name = $('#sender_name');

            // minDate = new DateTime($('#s_time'), {
            //     format: 'd-M-Y'
            // });
            // maxDate = new DateTime($('#e_time'), {
            //     format: 'd-M-Y'
            // });

        //     $(function() {
        // $(".btn-area").append('<div class="col-sm-12 col-md-4 pr-3 text-right">'+
        //     '<button class="btn btn-primary"  data-id="'+''+'" onclick="createglobalplan(\''+''+'\')" ><i class="fas fa-plus"></i> {{__('Add New Charge')}} </button>'+
        // '</a>'+
        // '</div>');
        //     });

        //     $(function() {
        //     $(".btn-area").append('<div class="col-sm-12 col-md-4 pr-3 text-right">'+
        //         '<a class="btn btn-primary" href="{{route('admin.user.create')}}">'+
        //     '<i class="fas fa-plus"></i> Add New Customer'+
        //     '</a>'+
        //     '</div>');
        // });

            // $('#geniustable tfoot th').each(function () {
            //     var title = $(this).text();
            //     $(this).html('<input type="text" placeholder="Search ' + title + '" />');
            // });
        var table = $('#geniustable').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            searching: false,
            // initComplete: function () {
            // // Apply the search
            // this.api()
            //     .columns()
            //     .every(function () {
            //         var that = this;

            //         $('input', this.footer()).on('keyup change clear', function () {
            //             if (that.search() !== this.value) {
            //                 that.search(this.value).draw();
            //             }
            //         });
            //     });
            // },
            ajax: {

                url : "{{  route('admin.report.transaction.datatables') }}",
                data : function (d) {
                    d.sender = $('#sender_name').val()
                }

            },
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

        $('#sender_name').on('keyup', function () {
            table.draw();
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
