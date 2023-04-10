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


    <!-- Row -->
    <div class="row mt-3">
        <div class="col-lg-12">
            @include('includes.admin.form-success')
            <div class="card tab-card mb-4">
                @include('admin.user.profiletab')
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
                        <div class="card-body">
                            <div class="card mb-4">

                                <div class="table-responsive p-3">
                                    <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>{{ __('Date') }} / {{ __('Transaction ID') }}</th>
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
                </div>
            </div>
        </div>
    </div>


    <div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
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
                                <a href="#" class="btn w-100 closed" data-bs-dismiss="modal">
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
                searching: true,
                ajax: "{{  route('admin.pay.transaction.datatables',['id' => $data->id]) }}",
                columns: [
                    { data: 'date', name: 'date', 
                        render: function(data, type, row, meta) {
                        if(type === 'display') {
                            data = row.date + '<br>' + row.trnx_no;
                        }
                        return data;
                    }},
                    // { data: 'trnx_no', name: 'trnx_no' },
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

        });

        function getdetails(e){
            if(!e.target.getAttribute('data-id')) {
                $('.list-group').html("<p>@lang('No details found!')</p>")
                $('#modal-success').modal('show')
                return
            }
            var url = "{{url('admin/user/pay/detail')}}"+'/'+e.target.getAttribute('data-id')
            $('.trx_details').text(e.target.getAttribute('data-type'))
            $.get(url,function (res) {
            if(res == 'empty'){
                $('.list-group').html("<p>@lang('No details found!')</p>")
            }else{
                $('.list-group').html(res)
            }
            $('#modal-success').modal('show')
            })
        }

        $('.closed').click(function() {
            $('#modal-success').modal('hide');
        });

    </script>
@endsection
