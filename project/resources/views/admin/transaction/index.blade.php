@extends('layouts.admin')

@section('content')

<div class="card">
	<div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Transactions') }}</h5>
    <ol class="breadcrumb m-0 py-0">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="javascript:;">{{ __('Transactions') }}</a></li>
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
        <th>{{__('Date')}} / {{__('Transaction ID')}}</th>
        <th>{{__('Sender')}}</th>
        <th>{{__('Receiver')}}</th>
        <th>{{__('Description')}}</th>
        <th>{{__('Remark')}}</th>
        <th>{{__('Amount')}}</th>
        <th>{{__('Charge')}}</th>
        <th>{{__('Details')}}</th>
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



@endsection



@section('scripts')

<script type="text/javascript">
	"use strict";

    var table = $('#geniustable').DataTable({
           ordering: false,
           processing: true,
           serverSide: true,
           searching: true,
           ajax: '{{ route('admin.transactions.datatables') }}',
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
                { data: 'action', name: 'action' },
            ],
            language : {
                processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
            }
        });

        function getDetails (id)
        {
            var url = "{{url('admin/user/transaction/details/')}}"+'/'+id
            // $('.trx_details').text($(this).data('data').details)
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


