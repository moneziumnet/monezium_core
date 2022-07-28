@extends('layouts.admin')

@section('content')
<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between">
  <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Edit Plan') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.bank.plan.index')}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
  <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="javascript:;">{{ __('Pricing Plan') }}</a></li>
  </ol>
  </div>
</div>

<div class="row justify-content-center mt-3">
<div class="col-md-10">
  <div class="card mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
      <h6 class="m-0 font-weight-bold text-primary">{{ __('Edit Plan Form') }}</h6>
    </div>

    <div class="card-body">
      <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
      <form class="geniusform" action="{{route('admin.bank.plan.update',$data->id)}}" method="POST" enctype="multipart/form-data">

          @include('includes.admin.form-both')

          {{ csrf_field() }}

          <div class="row">
            <div class="col-md-3">
              <div class="form-group">
                <label for="title">{{ __('Title') }}</label>
                <input type="text" class="form-control" id="title" name="title" placeholder="{{ __('Enter Title') }}" value="{{ $data->title }}" required>
              </div>
            </div>

            <div class="col-md-3">
              <div class="form-group">
                <label for="amount">{{ __('Amount') }}</label>
                <input type="number" class="form-control" id="amount" name="amount" placeholder="{{ __('Enter Amount') }}" min="0" value="{{ $data->amount }}" required>
              </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                  <label for="days">{{ __('Days') }}</label>
                  <input type="number" class="form-control" id="days" name="days" placeholder="{{ __('Enter Days') }}" min="1" value="{{ $data->days }}" required>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label >{{ __('') }}</label>
                    <button type="submit" id="submit-btn" class="btn btn-primary w-100 mt-2">{{ __('Submit') }}</button>
                </div>
            </div>

          </div>
      </form>
        </div>
  </div>
</div>

</div>
<div class="row mt-3">
    <div class="col-lg-12">

      @include('includes.admin.form-success')

      <div class="card mb-4">
        <div class="table-responsive p-3">
          <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
            <thead class="thead-light">
              <tr>
                  <th>{{__('Name')}}</th>
                  <th>{{__('Min')}}</th>
                  <th>{{__('Max')}}</th>
                  <th>{{__('Daily Limit')}}</th>
                  <th>{{__('Monthly Limit')}}</th>
                  <th>{{__('Action')}}</th>
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
        <h3>@lang('Plan Details')</h3>
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
           ajax: '{{ route('admin.bank.plan.detail.datatables', $data->id) }}',
           columns: [
                { data: 'name', name: 'name' },
                { data: 'min', name: 'min' },
                { data: 'max', name:'max' },
                { data: 'daily_limit', name: 'daily_limit' },
                { data: 'monthly_limit', name:'monthly_limit' },
                { data: 'action', name: 'action' },
            ],
            language : {
                processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
            }
        });

    function createglobalplan(id)
        {
                var url = "{{url('admin/bank-plan/detail')}}"+'/'+`${id}`
                console.log(url);
                $.get(url,function (res) {
                  if(res == 'empty'){
                    $('.list-group').html('<p>@lang('No details found!')</p>')
                  }else{
                    $('.list-group').html(res)
                  }
                });
                $('#modal-success').modal('show')
        }
        $('.closed').click(function() {
            $('#modal-success').modal('hide');
        });


</script>

@endsection

