@extends('layouts.admin')
@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ $data->name }}</h5>
    <ol class="breadcrumb py-0 m-0">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.user.index') }}">{{ __('User List') }}</a></li>
    </ol>
  </div>
</div>


<div class="row mt-3">
  <div class="col-lg-12">
    <div class="card tab-card">
      @include('admin.user.profiletab')

      <div class="tab-content" id="myTabContent">
        @php
        $currency = defaultCurr();
        @endphp
        @include('includes.admin.form-success')
        <div class="tab-pane fade show active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
        <div class="card-body">
          <div class="table-responsive">
            <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
              <thead class="thead-light">
               <tr>
                <th>{{__('Date')}}</th>
                <th>{{__('Transaction ID')}}</th>
                <th>{{__('Description')}}</th>
                <th>{{__('Remark')}}</th>
                <th>{{__('Amount')}}</th>
                <th>{{__('Charge')}}</th>
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
<!--Row-->
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
                { data: 'created_at', name: 'created_at' },
                { data: 'trnx', name: 'trnx' },
                { data: 'details', name: 'details' },
                { data: 'remark', name:'remark' },
                { data: 'amount', name: 'amount' },
                { data: 'charge', name: 'charge' },
            ],
            language : {
                processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
            }
        });

</script>

@endsection