@extends('layouts.admin')


@section('content')
<div class="card">
    <div class="d-sm-flex align-items-center justify-content-between">
      <h5 class=" mb-0 text-gray-800 pl-3">{{ __($title) }}</h5>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.escrow.manage') }}">{{ __('Manage Escrow') }}</a></li>
      </ol>
    </div>
  </div>

<div class="row mt-3">
    <div class="col-12 col-md-12 col-lg-12">
        @include('includes.admin.form-success')
        <div class="card mb-4">
            <div class="table-responsive p-3">
                    <table class="table table-hover dt-responsive" cellspacing="0" width="100%">
                        <thead class="thead-light">
                        <tr>
                            <th>@lang('Sl')</th>
                            <th>@lang('Invited By')</th>
                            <th>@lang('Invited To')</th>
                            <th>@lang('Amount')</th>
                            <th>@lang('Charge')</th>
                            <th>@lang('Charge Bearer')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Action')</th>
                        </tr>
                        </thead>
                        @forelse ($escrows as $key => $item)
                            <tr>
                                <td data-label="@lang('Sl')">{{$key + $escrows->firstItem()}}</td>
                                 <td data-label="@lang('Invited By')">
                                   {{$item->user->email}}
                                 </td>
                                 <td data-label="@lang('Invited To')">{{$item->recipient->email}}</td>
                                 <td data-label="@lang('Amount')">{{amount($item->amount,$item->currency->type,2)}} {{$item->currency->code}}</td>
                                 <td data-label="@lang('Charge')">{{amount($item->charge,$item->currency->type,2)}} {{$item->currency->code}}</td>
                                 <td data-label="@lang('Charge Bearer')">
                                     @if ($item->pay_charge == 1)
                                       {{ $item->user->email}}
                                     @else
                                       {{ $item->recipient->email}}
                                     @endif
                                 </td>
                                 <td data-label="@lang('Status')">
                                    @if ($item->status == 1)
                                      <span class="badge badge-success">@lang('Released')</span>
                                    @elseif ($item->status == 0)
                                        <span class="badge badge-warning">@lang('On Hold')</span>
                                    @elseif ($item->status == 3)
                                        <span class="badge badge-info">@lang('Disputed')</span>
                                    @elseif ($item->status == 4)
                                        <span class="badge badge-dark">@lang('Closed')</span>
                                    @endif
                                 </td>

                                <td data-label="@lang('Action')">
                                    {{-- && access('escrow details') --}}
                                    @if ($item->status == 3 )
                                        <a class="btn btn-primary" href="{{route('admin.escrow.details',$item->id)}}">@lang('Details')</a>
                                    @else
                                        <a class="btn btn-info disabled" >@lang('Details')</a>
                                    @endif
                                </td>
                               
                            </tr>
                         @empty

                            <tr>
                                <td class="text-center" colspan="100%">@lang('No Data Found')</td>
                            </tr>

                        @endforelse
                    </table>
                </div>
            @if($escrows->hasPages())
                {{ $escrows->links('admin.partials.paginate') }}
            @endif
        </div>
    </div>
</div>
@endsection
