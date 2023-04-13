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


<div class="row mt-3">
  <div class="col-lg-12">
    <div class="card mt-1 tab-card">
      @include('admin.user.profiletab')

      <div class="tab-content" id="myTabContent">
        @php
        $currency = DB::table('currencies')->where('id', defaultCurr())->first();

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
                                <div class="h6 mb-0 mt-2 font-weight-bold text-gray-800">Price {{ $currency->symbol.$plan->amount }}  </div>
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
                                @foreach($plans as $value)
                                <option value="{{ $value->id }}">{{ $value->title }} {{ $currency->symbol.$value->amount}} for {{$value->days}} days</option>
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
            @include('admin.user.feetab')

            <div class="table-responsive mt-3">
            <table id="geniustable" class="table table-hover  dt-responsive cell-border text-center row-border table-bordered" cellspacing="0" width="100%">
                <thead class="thead-light">
                 <tr>
                  <th colspan="2" >{{__('Fee Type')}}</th>
                  <th colspan="2" >{{__('Golbal')}}</th>
                  <th colspan="2" >{{__('Customer')}}</th>
                  <th rowspan="2">{{__('Action')}}</th>
                 </tr>
                 <tr>
                    <th >{{__('Name')}}</th>
                    <th>{{__('Type')}}</th>
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

  <div class="modal modal-blur fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-md modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-status bg-primary"></div>
        <div class="modal-body text-center m-4">
        <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
        <h3>@lang('Plan Details')</h3>
        <form method="post" action="{{route('admin.supervisor.charge.all.update', $data->id)}}" class="mt-3">
          @csrf
          <div class="row">
            <div class="col-md-4">
              <label class="h5">{{__("Charge Name")}}</label>
            </div>
            <div class="col-md-2">
              <label class="h5">{{__("Percent Charge (%)")}}</label>
            </div>
            <div class="col-md-2">
              <label class="h5">{{__("Fixed Charge (%)")}}</label>
            </div>
            <div class="col-md-2">
              <label class="h5">{{__("From")}}</label>
            </div>
            <div class="col-md-2">
              <label class="h5">{{__("Till")}}</label>
            </div>
          </div>
          <div style="max-height: 600px;overflow-y: scroll; overflow-x: hidden;">
            @foreach ($global_list as $item)
            <div class="row border-bottom mt-2 p-2 align-items-center">
              <div class="col-md-4">
                <label class="h6">{{$item->name}}</label>
              </div>
              <input  type="hidden" name="name_{{$item->id}}" class="form-control" value="{{$item->name}}">
              <input  type="hidden" name="user_id_{{$item->id}}" class="form-control" value="{{$data->id}}">
              <input  type="hidden" name="slug_{{$item->id}}" class="form-control" value="{{$item->slug}}">
              @php
                $customplan =  DB::table('charges')->where('user_id',$data->id)->where('plan_id', 0)->where('name', $item->name)->first();
                if($customplan) {
                  $charge = $customplan->data;
                }
                else {
                  $charge = json_encode($item->data);
                }
                $item_key = $item->id;
              @endphp

              @foreach (json_decode($charge) as $key => $value )
              @switch($key)
                @case('percent_charge')
                  <div class="col-md-2">
                    <input type="number" step="any" name="{{$key}}_{{$item_key}}" class="form-control" value="{{@$value}}">
                  </div>
                  @break
                @case('fixed_charge')
                  <div class="col-md-2">
                    <input type="number" step="any" name="{{$key}}_{{$item_key}}" class="form-control" value="{{@$value}}">
                  </div>
                  @break
                @case('from')
                  <div class="col-md-2">
                    <input type="number" step="any" name="{{$key}}_{{$item_key}}" class="form-control" value="{{@$value}}">
                  </div>
                  @break
                @case('till')
                  <div class="col-md-2">
                    <input type="number" step="any" name="{{$key}}_{{$item_key}}" class="form-control" value="{{@$value}}">
                  </div>
                  @break
              
                @default
                  
              @endswitch
              {{-- @if($key != 'perc')
              <div class="col-md-2">
                <input type="number" step="any" name="{{$key}}" class="form-control" value="{{@$value}}">
              </div> --}}
              @endforeach
              
            </div>
            @endforeach
          </div>
            <button type="submit" id="submit-btn" class="mt-3 btn btn-primary w-100">{{ __('Save') }}</button>
        </form>
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
           ajax: '{{ route('admin-user-pricingplan-supervisor-datatables',$data->id) }}',
           columns: [
                { data: 'name', name: 'name' },
                { data: 'type', name: 'type' },
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
                    '<a href="javascript:;" data-toggle="modal" data-target="#editModal" class="btn btn-primary" >'+
                '<i class="fas fa-plus"></i> All Edit'+
                '</a>'+
                '</div>');
            });

        function getDetails (id=null)
        {
            if (id) {
                var url = "{{url('admin/user/pricingplan/edit')}}"+'/'+id
                $.get(url,function (res) {
                  if(res == 'empty'){
                    $('.list-group').html("<p>@lang('No details found!')</p>")
                  }else{
                    $('.list-group').html(res)
                  }
                });
            }

              $('#modal-success').modal('show')
        }

        function createDetails(id)
        {
                var url = "{{url('admin/user/pricingplan/supervisor/create')}}"+'/'+'{{$data->id}}'+'/'+`${id}`
                console.log(url);
                $.get(url,function (res) {
                  if(res == 'empty'){
                    $('.list-group').html("<p>@lang('No details found!')</p>")
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
