@extends('layouts.admin')

@section('content')

<div class="card">
	<div class="d-sm-flex align-items-center justify-content-between">
	<h5 class=" mb-0 text-gray-800 pl-3">{{ __('Crypto Withdraws') }}</h5>
	<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>

		<li class="breadcrumb-item"><a href="{{ route('admin.withdraws.crypto.index') }}">{{ __('Crypto Withdraws') }}</a></li>
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
        <th>{{__('Date')}}</th>
        <th>{{__('Customer Name')}}</th>
        <th>{{__('Customer Crypto Address')}}</th>
        <th>{{__('Receiver Crypto Address')}}</th>
        <th>{{__('Amount')}}</th>
        <th>{{__('Action')}}</th>
			</tr>
		  </thead>
		</table>
	  </div>
	</div>
  </div>
</div>

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

<div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-status bg-primary"></div>
        <div class="modal-body text-center py-4">
        <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
        <h3>@lang('Bank Details')</h3>
        <p class="bank_details"></p>
        <ul class="list-group mt-2">
            <li class="list-group-item d-flex justify-content-between" style="word-break:break-all;">@lang('Customer Crypto Address')<span id="crypto_address" style="margin-left: 60px"></span></li>
            <li class="list-group-item d-flex justify-content-between" style="word-break:break-all;">@lang('Customer Crypto Address')<span id="receiver_address" style="margin-left: 60px"></span></li>
            <li class="list-group-item d-flex justify-content-between" style="word-break:break-all;">@lang('Amount')<span id="amount" style="margin-left: 60px"></span></li>
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
           ajax: '{{ route('admin.withdraws.crypto.datatables') }}',
           columns: [
                { data: 'created_at', name: 'created_at' },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'crypto_address', name: 'crypto_address' },
                { data: 'sender_address', name: 'sender_address' },
                { data: 'amount', name: 'amount' },
                { data: 'action', name: 'action' },
            ],
            language : {
                processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
            }
        });

        function getDetails(res_data, crypto_address) {
            console.log(crypto_address)
            $('#crypto_address').text(crypto_address);
            $('#receiver_address').text(res_data.sender_address);
            $('#amount').text(res_data.amount + res_data.currency.code);
            $('#modal-success').modal('show');
        }

        $('.closed').click(function() {
            $('#modal-success').modal('hide');
        });

</script>

@endsection
