@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="d-sm-flex align-items-center justify-content-between py-3">
      <ol class="breadcrumb py-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.system.accounts') }}">{{ __('System Account List') }}</a></li>
      </ol>
    </div>
  </div>



<div class="row mt-3">
  <div class="col-lg-12">
    <div class="card tab-card">
        @include('admin.system.systemcryptotab')

      <div class="tab-content" id="myTabContent">
        @php
        $currency = defaultCurr();
        @endphp
        @include('includes.admin.form-success')
        <div class="tab-pane fade show active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
        <div class="card-body">
            <div class="card mb-4">

                <div class="table-responsive p-3">
                  <div class="btn-list align-items-center">
                    <div class="d-flex justify-content-center row">
                        <div class="form-group mr-3 col-lg-2">
                            <label for="s_time" class="form-label">{{ __('Start Time') }}</label>
                            <input class="form-control shadow-none mr-2" type="date" placeholder="{{__('Start Time')}}" id="s_time" name="s_time" >
                        </div>
                        <div class="form-group mr-3  col-lg-2">
                            <label for="e_time" class="form-label">{{ __('Close time') }}</label>
                            <input class="form-control shadow-none mr-2" type="date" placeholder="{{__('End Time')}}" id="e_time" name="e_time" >
                        </div>
                        <div class="form-group mr-3  col-lg-2">
                            <label for="sender_name" class="form-label">{{ __('Sender') }}</label>
                            <input class="form-control shadow-none mr-2" type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" placeholder="{{__('Sender Name')}}" id="sender_name" name="sender_name" >
                        </div>
                        <div class="form-group mr-3  col-lg-2">
                            <label for="receiver_name" class="form-label">{{ __('Receiver') }}</label>
                            <input class="form-control shadow-none mr-2" type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" placeholder="{{__('Rceiver Name')}}" id="receiver_name" name="receiver_name" >
                        </div>
                        <div class="form-group mr-3  col-lg-2">
                          <label  class="form-label">{{ __('Remark') }}</label>
                          <select name="remark" id="remark" class="form-control mr-2 shadow-none" >
                              <option value="">@lang('All Remark')</option>
                              @foreach ($remark_list as $item)
                                  <option value="{{$item}}">@lang(ucwords(str_replace('_',' ',$item)))</option>
                              @endforeach
                          </select>
                      </div>
                        <div class="form-group mr-3  col-lg-2">
                            <label for="trnx_no" class="form-label">{{ __('TransactionID') }}</label>
                            <input class="form-control shadow-none mr-2" type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" placeholder="{{__('Transaction ID')}}" id="trnx_no" name="trnx_no" >
                        </div>
                    </div>
                </div>
                    <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                    <thead class="thead-light">
                    <tr>
                        <th>{{__('Date')}} / {{__('Transaction ID')}}</th>
                        <th>{{__('Sender')}}</th>
                        <th>{{__('Receiver')}}</th>
                        <th>{{__('Description')}}</th>
                        <th>{{__('Remark')}}</th>
                        <th>{{__('Amount')}}</th>
                        <th>{{__('Charge')}}</th>
                        <th>{{__('Action')}}</th>
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
<!--Row-->
@endsection
@section('scripts')

<script type="text/javascript">
	"use strict";
  $(document).ready(function () {

    var table = $('#geniustable').DataTable({
           ordering: true,
           processing: true,
           serverSide: true,
          //  searching: true,
           ajax: {
            url: '{{ route('admin.system.account.transactions.datatables',$data->id) }}',
            data : function (d) {
                        d.sender = $('#sender_name').val(),
                        d.receiver = $('#receiver_name').val(),
                        d.s_time = $('#s_time').val(),
                        d.e_time = $('#e_time').val(),
                        d.trnx_no = $('#trnx_no').val()
                        d.remark = $('#remark').val()
                    }
          },
           columns: [
                { data: 'created_at', name: 'created_at', 
                  render: function(data, type, row, meta) {
                    if(type === 'display') {
                      data = row.created_at + '<br>' + row.trnx;
                    }
                    return data;
                  }
                },
                { data: 'sender', name: 'sender' },
                { data: 'receiver', name: 'receiver' },
                { data: 'details', name: 'details' },
                { data: 'remark', name:'remark' },
                { data: 'amount', name: 'amount' },
                { data: 'charge', name: 'charge' },
                { data: 'action', name: 'action', orderable: false },
            ],
            language : {
                processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
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
        $('#remark').on('change', function () {
            table.draw();
        });
      });

        function getDetails (id)
        {
            var url = "{{url('admin/user/transaction/details/')}}"+'/'+id
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
