@extends('layouts.admin')

@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Manage Charges') }}
    <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.bank.plan.index')}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="{{route('admin.bank.plan.index')}}">{{ __('Pricing Plan') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.manage.charge', $plan_id) }}">{{ __('Manage Charges') }}</a></li>
    </ol>
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
                  <th>{{__('Percent')}}</th>
                  <th>{{__('Fixed')}}</th>
                  <th>{{__('From')}}</th>
                  <th>{{__('Till')}}</th>
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

  <div class="modal modal-blur fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-md modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-status bg-primary"></div>
        <div class="modal-body text-center m-4">
        <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
        <h3>@lang('Plan Details')</h3>
        <form method="post" action="{{route('admin.update.all.charge', $plan_id)}}" class="mt-3">
          @csrf
          <div class="row">
            <div class="col-md-4">
              <label class="h5">{{__("Charge Name")}}</label>
            </div>
            <div class="col-md-2">
              <label class="h5">{{__("Percent Charge (%)")}}</label>
            </div>
            <div class="col-md-2">
              <label class="h5">{{__("Fixed Charge")}}</label>
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
              <input  type="hidden" name="user_id_{{$item->id}}" class="form-control" value="0">
              <input  type="hidden" name="slug_{{$item->id}}" class="form-control" value="{{$item->slug}}">
              @php
                $item_key = $item->id;
              @endphp

              @foreach ($item->data as $key => $value )
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
@endsection

@section('scripts')

<script type="text/javascript">
	"use strict";

    var table = $('#geniustable').DataTable({
           ordering: false,
           processing: true,
           serverSide: true,
           searching: true,
           ajax: '{{ route('admin.charge.plan.datatables', $plan_id) }}',
           columns: [
                { data: 'name', name: 'name' },
                { data: 'percent', name: 'percent' },
                { data: 'fixed', name:'fixed' },
                { data: 'from', name: 'from' },
                { data: 'till', name:'till' },
                { data: 'action', name: 'action' },
            ],
            language : {
                processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
            }
        });
        $(function() {
            $(".btn-area").append('<div class="col-sm-6 col-md-2 pr-3 text-right">'+
                '<button class="btn btn-primary"  data-id="'+'{{$plan_id}}'+'" onclick="createglobalplan(\''+'{{$plan_id}}'+'\')" ><i class="fas fa-plus"></i> {{__('Add New Charge')}} </button>'+
            '</a>'+
            '</div>');
        });

        $(function() {
            $(".btn-area").append('<div class="col-sm-6 col-md-2 pr-3 text-right">'+
              '<a href="javascript:;" data-toggle="modal" data-target="#editModal" class="btn btn-primary" >'+
                'All Edit'+
              '</a>'+
            '</div>');
        });
    function createglobalplan(id)
        {
                var url = "{{url('admin/user/pricingplancreate')}}"+'/'+`${id}`
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
