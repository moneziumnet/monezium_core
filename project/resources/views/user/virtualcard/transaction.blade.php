@extends('layouts.user')

@push('css')

@endpush

@section('contents')

<div class="page-body">
  <div class="container-xl">

    <div class="row row-deck row-cards">
        <div class="col-lg-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">@lang('Recent Transaction')</h3>
              </div>
              @php
                $item=array();
                $item=json_decode($transactions, true);
              @endphp
              @if (count($item) == 0)
              <p class="text-center p-2">@lang('NO DATA FOUND')</p>
              @else
              <div class="table-responsive">
                <table class="table card-table table-vcenter table-mobile-md text-nowrap datatable">
                  <thead>
                    <tr>
                      <th class="w-1">@lang('No').
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm text-dark icon-thick" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                          <polyline points="6 15 12 9 18 15" />
                        </svg>
                      </th>
                      <th>@lang('Date')</th>
                      <th>@lang('Remark')</th>
                      <th>@lang('Amount')</th>
                      <th>@lang('Details')</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($item['data'] as $key=>$data)
                    <tr>
                      <td data-label="@lang('No')">
                        <div>
                          <span class="text-muted">{{ $loop->iteration }}</span>
                        </div>
                      </td>
                      <td data-label="@lang('Date')">{{dateFormat($data['created_at'],'d-M-Y')}}</td>
                      <td data-label="@lang('Remark')">
                        @if($data['indicator']=='C')
                            <span class="badge badge-dark">{{__('Credit')}}</span>
                        @elseif($data['indicator']=='D')
                            <span class="badge badge-dark">{{__('Debit')}}</span>
                        @endif
                      </td>
                      <td data-label="@lang('Amount')">
                        <span class="text-success">{{$data['amount']}}</span>
                    </td>
                    <td data-label="@lang('Details')" class="text-end">
                        {{$data['gateway_reference_details']}}
                    </td>
                   </tr>
                    @endforeach

                  </tbody>
                </table>
              </div>
              @endif

            </div>
        </div>
        </div>
  </div>
</div>



@endsection

@push('js')
<script>
  'use strict';
</script>
@endpush
