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
                            <div class="form-group mr-3">
                                <label class="form-label">{{ __('Type') }}</label>

                                <select name="type" id="type" class="form-control mr-2 shadow-none" >
                                    <option value="">@lang('All')</option>
                                    <option value="External">@lang('External')</option>
                                    <option value="Deposit">@lang('Deposit')</option>
                                </select>
                            </div>
                            <div class="form-group mr-3">
                                <label  class="form-label">{{ __('Bank') }}</label>
                                <select name="bank_name" id="bank_name" class="form-control mr-2 shadow-none" >
                                    <option value="">@lang('All Account')</option>
                                    @foreach ($banklist as $item)
                                        <option value="{{$item}}">{{$item}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mr-3">
                                <label class="form-label">{{ __('Status') }}</label>

                                <select name="status" id="status" class="form-control mr-2 shadow-none" >
                                    <option value="">@lang('All')</option>
                                    <option value="pending">@lang('Pending')</option>
                                    <option value="complete">@lang('Complete')</option>
                                    <option value="reject">@lang('Reject')</option>
                                </select>
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


    <div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-status bg-primary"></div>
            <div class="modal-body text-center py-4">
            <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
            <h3>@lang('Transaction Details')</h3>
            <p class="trx_details"></p>
            <ul class="list-group mt-2">
            </ul>
            </div>
            <div class="modal-footer">
            <div class="w-100">
                <div class="row">
                    <div class="col mt-2">
                        <a href="#" class="btn w-100" data-bs-dismiss="modal">
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
            var table = $('#geniustable').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {

                    url : "{{  route('admin.report.transaction.datatables') }}",
                    data : function (d) {
                        d.sender = $('#sender_name').val(),
                        d.receiver = $('#receiver_name').val(),
                        d.s_time = $('#s_time').val(),
                        d.e_time = $('#e_time').val(),
                        d.trnx_no = $('#trnx_no').val(),
                        d.trnx_type = $('#type').val(),
                        d.status = $('#status').val(),
                        d.bank_name = $('#bank_name').val()
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
            $('#receiver_name').on('keyup', function () {
                table.draw();
            });
            $('#trnx_no').on('keyup', function () {
                table.draw();
            });
            $('#s_time').on('change', function () {
                table.draw();
            });
            $('#e_time').on('change', function () {
                table.draw();
            });
            $('#type').on('change', function () {
                table.draw();
            });
            $('#bank_name').on('change', function () {
                table.draw();
            });
            $('#status').on('change', function () {
                table.draw();
            });
            $('.details').on('click',function () {
                console.log($(this).data('id'))
                var url = "{{url('admin/bank/report/transaction/details')}}"+'/'+$(this).data('id')
                $('.trx_details').text($(this).data('type').type)
                $('#trx_id').val($(this).data('id'))
                $.get(url,function (res) {
                if(res == 'empty'){
                    $('.list-group').html("<p>@lang('No details found!')</p>")
                }else{
                    $('.list-group').html(res)
                }
                $('#modal-success').modal('show')
                })
            });

            $('.closed').click(function() {
                $('#modal-success').modal('hide');
            });
        });


    </script>
@endsection
