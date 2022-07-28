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


{{-- <div class="row mt-3">
    @forelse ($charges as $charge)
    <div class="col-sm-6 col-lg-4 col-xl-3 currency--card">
      <div class="card card-primary">
        <div class="card-header">
          <h4>{{$charge->name}}</h4>
        </div>
        <div class="card-body">
          <ul class="list-group mb-3">
            @foreach ($charge->data as $key => $value)
              @if ($key == 'percent_charge' || $key == 'fixed_charge')
                <li class="list-group-item d-flex justify-content-between">@lang(ucwords(str_replace('_',' ',$key)).' :')
                  <span class="font-weight-bold">{{@$value}}
                    @if ($key == 'percent_charge')
                        %
                    @else --}}
                      {{-- {{$gs->curr_code}} --}}
                    {{-- @endif
                  </span>
                </li>
              @endif
            @endforeach
          </ul> --}}
          {{-- @if (access('edit charge')) --}}
          {{-- <a href="{{route('admin.edit.charge',$charge->id)}}" class="btn btn-primary btn-block"><i class="fas fa-edit"></i> @lang('Edit Charge')</a> --}}
          {{-- @endif --}}
        {{-- </div>
      </div>
    </div>
    @empty
    <div class="col-md-12 text-center">
        <h5>@lang('No data found')</h5>
    </div>
    @endforelse
</div> --}}
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

</script>

@endsection
