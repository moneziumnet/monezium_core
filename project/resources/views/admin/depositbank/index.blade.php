@extends('layouts.admin')

@section('content')

<div class="card">
	<div class="d-sm-flex align-items-center justify-content-between">
	<h5 class=" mb-0 text-gray-800 pl-3">{{ __('Deposits(Bank)') }}</h5>
	<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
		<li class="breadcrumb-item"><a href="{{ route('admin.deposits.bank.index') }}">{{ __('Deposits(Bank)') }}</a></li>
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
                <th>{{__('Deposit No.')}}</th>
                <th>{{__('Customer Name')}}</th>
                <th>{{__('Customer Email')}}</th>
                <th>{{__('Amount')}}</th>
                <th>{{__('Status')}}</th>
                <th>{{__('Action')}}</th>
			</tr>
		  </thead>
		</table>
	  </div>
	</div>
  </div>
</div>

<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalTitle" aria-hidden="true">
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
        <ul class="list-group details-list mt-2">
            <li class="list-group-item">@lang('Receiver Name')<span id="user_name"></span></li>
            <li class="list-group-item">@lang('Receiver Address')<span id="user_addr"></span></li>
            <li class="list-group-item">@lang('Bank Name')<span id="bank_name"></span></li>
            <li class="list-group-item">@lang('Bank Address')<span id="bank_address"></span></li>
            <li class="list-group-item">@lang('Bank IBAN')<span id="bank_iban"></span></li>
            <li class="list-group-item">@lang('Bank SWIFT')<span id="bank_swift"></span></li>
            <li class="list-group-item">@lang('Deposit No.')<span id="deposit_detail"></span></li>
            <li class="list-group-item" id="li_document" >@lang('Document')<span> <a id="document" attributes-list download > {{__('Download Document')}} </a> </span></li>
        </ul>
        </div>
        <div class="modal-footer">
        <div class="w-100">
            <div class="row">
            <div class="col">
                <button class="btn w-100 closed" data-bs-dismiss="modal">
                    @lang('Close')
                </button>
                <div class="row action-button">
                    <div class="col-md-6">
                        <button class="btn btn-success w-100" id="complete_deposit" data-toggle="modal" data-target="#statusModal" data-href="">{{__("Approve")}}</button>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-danger w-100" id="reject_deposit" data-toggle="modal" data-target="#statusModal" data-href="">{{__("Reject")}}</button>
                    </div>
                </div>
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
           ajax: '{{ route('admin.deposits.bank.datatables') }}',
           columns: [
                { data: 'created_at', name: 'created_at' },
                { data: 'deposit_number', name: 'deposit_number' },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'customer_email', name: 'customer_email' },
                { data: 'amount', name: 'amount' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action' },
            ],
            language : {
                processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
            }
        });

        function getDetails(e) {
            var res_data = JSON.parse(e.target.getAttribute('data-detail'));
            var bankaccount = JSON.parse(e.target.getAttribute('data-bank'));
            var document_url = e.target.getAttribute('data-docu');
            var deposit_detail = e.target.getAttribute('data-number');
            var deposit_status = e.target.getAttribute('data-status');
            var complete_url = e.target.getAttribute('data-complete-url');
            var reject_url = e.target.getAttribute('data-reject-url');
            $('#bank_name').text(res_data.name.replace(/-/gi, ' '));
            $('#bank_address').text(res_data.address.replace(/-/gi, ' '));
            $('#bank_iban').text(bankaccount.iban);
            $('#bank_swift').text(bankaccount.swift);
            $('#user_name').text(bankaccount.user.name);
            $('#user_addr').text(bankaccount.user.address);
            $('#deposit_detail').text(deposit_detail);
            if(deposit_status == "pending"){
                $('#complete_deposit').attr('data-href', complete_url);
                $('#reject_deposit').attr('data-href', reject_url);

                $('#complete_deposit').removeClass("d-none");
                $('#reject_deposit').removeClass("d-none");
                $('.closed').addClass("d-none");
            } else {
                $('#complete_deposit').addClass("d-none");
                $('#reject_deposit').addClass("d-none");
                $('.closed').removeClass("d-none");
            }
            if(document_url) {
                $("#li_document").attr("style","display: block");
                $("#document").attr("href", `{{asset('assets/doc/${document_url}')}}`);
                $("#document").text(document_url);
            }
            else{
                $("#li_document").attr("style","display: none!important");
                $("#document").attr("href", `#`);
            }
            $('#modal-success').modal('show');
        }

        $('.closed').click(function() {
            $('#modal-success').modal('hide');
        });

        $('#complete_deposit').click(function() {
            $('#modal-success').modal('hide');
            $('.btn-ok').attr('href', $(this).data('href'));
        });

        $('#reject_deposit').click(function() {
            $('#modal-success').modal('hide');
            $('.btn-ok').attr('href', $(this).data('href'));
        });

</script>

@endsection
