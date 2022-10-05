@extends('layouts.admin')

@section('content')

<div class="card">
	<div class="d-sm-flex align-items-center justify-content-between">
	<h5 class=" mb-0 text-gray-800 pl-3">{{ __('Donation') }}</h5>
	<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>

		<li class="breadcrumb-item"><a href="{{ route('admin.donation.index') }}">{{ __('Donation List') }}</a></li>
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
                <th>{{__('Donator')}}</th>
                <th>{{__('Compaign Title')}}</th>
                <th>{{__('Organizer')}}</th>
                <th>{{__('Payment')}}</th>
                <th>{{__('Donated Amount')}}</th>
                <th>{{__('Date')}}</th>
                <th>{{__('Status')}}</th>
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
        <h3>@lang('Donation Details')</h3>
        <p class="donation_details"></p>
        <ul class="list-group mt-2">
            <li class="list-group-item d-flex justify-content-between" style="word-break:break-all;">@lang('Donator')<span id="donator"  style="margin-left: 60px"></span></li>
            <li class="list-group-item d-flex justify-content-between" style="word-break:break-all;">@lang('Campaign Title')<span id="title" style="margin-left: 60px"></span></li>
            <li class="list-group-item d-flex justify-content-between" style="word-break:break-all;">@lang('Organizer')<span id="organizer" style="margin-left: 60px"></span></li>
            <li class="list-group-item d-flex justify-content-between" style="word-break:break-all;">@lang('Payment')<span id="payment" style="margin-left: 60px"></span></li>
            <li class="list-group-item d-flex justify-content-between" style="word-break:break-all;">@lang('Donated Amount')<span id="amount" style="margin-left: 60px"></span></li>
            <li class="list-group-item d-flex justify-content-between" style="word-break:break-all;">@lang('Created Date')<span id="date" style="margin-left: 60px"></span></li>
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
<div class="modal fade confirm-modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">{{ __("Confirm Delete") }}</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p class="text-center">{{__("You are about to delete this Donation.")}}</p>
				<p class="text-center">{{ __("Do you want to proceed?") }}</p>
			</div>
			<div class="modal-footer">
				<a href="javascript:;" class="btn btn-secondary" data-dismiss="modal">{{ __("Cancel") }}</a>
				<a href="javascript:;" class="btn btn-danger btn-ok">{{ __("Delete") }}</a>
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
           ajax: '{{ route('admin.donation.datatables') }}',
           columns: [
                { data: 'donator', name: 'donator' },
                { data: 'title', name: 'title' },
                { data: 'organizer', name: 'organizer' },
                { data: 'payment', name: 'payment' },
                { data: 'amount', name: 'amount' },
                { data: 'date', name: 'date' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action' },
            ],
            language : {
                processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
            }
        });

    function getdetails(e) {
        var res_data = JSON.parse(e.target.getAttribute('data-data'));
            var organizer = e.target.getAttribute('data-organizer');

        $('#title').text(res_data.campaign.title);
        $('#donator').text(res_data.user_name);
        $('#organizer').text(organizer);
        $('#amount').text(res_data.currency.symbol+res_data.amount);
        $('#payment').text(res_data.payment);
        $('#date').text(res_data.created_at);
        $('#modal-success').modal('show');
    }

        $('.closed').click(function() {
            $('#modal-success').modal('hide');
        });

</script>

@endsection
