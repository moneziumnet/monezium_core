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
    <div class="card mt-1 tab-card">
      @include('admin.user.profiletab')

      <div class="tab-content" id="myTabContent">
        @php
        $currency = defaultCurr();
        @endphp
        @include('includes.admin.form-success')
        <div class="tab-pane fade show p-3 active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
            <div class="row">
                <div class="card-body row-6">
                    <div class="card-header">
                        <h4>{{__('Current Plan')}}</h4>
                    </div>

                    <div class="row mb-3">
                        <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">{{$plan->title}} </div>
                                <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">Price {{ showprice($plan->amount,$currency) }}  </div>
                                <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">Duration {{$plan->days}} days  </div>
                                </div>
                            </div>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>


                <div class="card-body row-6">
                    <div class="card-header">
                        <h4>{{__('Upgrade Plan')}}</h4>
                    </div>
                    <div class="row mb-3">

                    <div class="col-xl-12 col-md-6 mb-4">
                        <form class="geniusform" action="{{ route('admin-user-upgrade-plan',$data->id) }}" method="POST" enctype="multipart/form-data">

                        @include('includes.admin.form-both')

                        {{ csrf_field() }}

                            <div class="form-group">
                            <label for="inp-name">{{ __('Subscription Type') }}</label>
                                <select class="form-control" name="subscription_type" id="subscription_type">
                                <option value="">{{ __('Select Subscription Type') }}</option>
                                @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->title }} {{ showprice($plan->amount,$currency)}} for {{$plan->days}} days</option>
                                @endforeach
                                </select>
                            </div>
                        <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Upgrade') }}</button>
                        </form>
                    </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="table-responsive">
            <table id="geniustable" class="table table-hover  dt-responsive cell-border text-center row-border table-bordered" cellspacing="0" width="100%">
                <thead class="thead-light">
                 <tr>
                  <th rowspan="2" >{{__('Fee Type')}}</th>
                  <th colspan="2" >{{__('Golbal')}}</th>
                  <th colspan="2" >{{__('Customer')}}</th>
                  <th rowspan="2">{{__('Action')}}</th>
                 </tr>
                 <tr>
                    <th >{{__('Percent')}}</th>
                    <th>{{__('Fixed')}}</th>
                    <th >{{__('Percent')}}</th>
                    <th>{{__('Fixed')}}</th>
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
           ajax: '{{ route('admin-user-pricingplan-datatables',$data->id) }}',
           columns: [
                { data: 'name', name: 'name' },
                { data: 'percent', name: 'percent' },
                { data: 'fixed', name:'fixed' },
                { data: 'percent_customer', name: 'percent_customer' },
                { data: 'fixed_customer', name:'fixed_customer' },
                { data: 'action', name: 'action' },
            ],
            language : {
                processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
            }
        });

        $(function() {
        $(".btn-area").append('<div class="col-sm-12 col-md-4 pr-3 text-right">'+
            '<button class="btn btn-primary"  data-id="'+'{{$data->id}}'+'" onclick="createglobalplan(\''+'{{$data->id}}'+'\')" ><i class="fas fa-plus"></i> {{__('Add New Plan')}} </button>'+
        '</a>'+
        '</div>');
    });


        function getDetails (id=null)
        {
            if (id) {
                var url = "{{url('admin/user/pricingplan/edit')}}"+'/'+id
                $.get(url,function (res) {
                  if(res == 'empty'){
                    $('.list-group').html('<p>@lang('No details found!')</p>')
                  }else{
                    $('.list-group').html(res)
                  }
                });
            }

              $('#modal-success').modal('show')
        }

        function createDetails(id)
        {
                var url = "{{url('admin/user/pricingplan/create')}}"+'/'+'{{$data->id}}'+'/'+`${id}`
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

        function createglobalplan(id)
        {
                var url = "{{url('admin/user/pricingplancreate')}}"+'/'+`${id}`
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
