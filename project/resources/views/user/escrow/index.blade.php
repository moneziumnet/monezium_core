@extends('layouts.user')

@section('title', __('My Escrows'))

@section('contents')

<div class="container-fluid">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          </div>
          <h2 class="page-title">
            {{__('Escrow Account')}}
          </h2>
        </div>
      </div>
    </div>

    <div class="page-body">
      <div class="container-fluid">
      <div class="row justify-content " style="max-height: 368px;">
        @if (count($wallets) != 0)
            @foreach ($wallets as $item)
                <div class="col-sm-6 col-md-4 mb-3">
                    <div class="card h-100 card--info-item">
                    <div class="text-end icon rounded-circle">
                        <i class="fas ">
                            {{$item->currency->symbol}}
                        </i>
                    </div>
                    <div class="card-body">
                        <div class="h3 m-0 text-uppercase"> {{__('Escrow')}}</div>
                        <div class="h4 m-0 text-uppercase"> {{ $item->wallet_no }}</div>
                        <div class="text-muted">{{ amount($item->balance,$item->currency->type,2) }}  {{$item->currency->code}}</div>
                    </div>
                    </div>
                </div>

            @endforeach
        @else
            <p class="text-center">@lang('NO Wallet FOUND')</p>
        @endif

      </div>
      </div>
    </div>

<div class="container-fluid">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <div class="page-pretitle">
            {{__('Overview')}}
          </div>
          <h2 class="page-title">
            {{__('My escrows')}}
          </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">

          <a href="{{ route('user.escrow.create') }}" class="btn btn-primary d-sm-inline-block">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
            {{__('Make Escrow')}}
          </a>
        </div>
      </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-fluid">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
                    @if (count($escrows) == 0)
                        <h3 class="text-center py-5">{{__('No Escrow Data Found')}}</h3>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter table-mobile-lg card-table">
                                <thead>
                                    <tr>
                                        <th>{{__('Recipient')}}</th>
                                        <th>{{__('Amount')}}</th>
                                        <th>{{__('Charge')}}</th>
                                        <th>{{__('Details')}}</th>
                                        <th>{{__('Status')}}</th>
                                        <th>{{__('Date')}}</th>
                                        <th>{{__('Action')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                  @foreach($escrows as $item)
                                      <tr>
                                          <td data-label="{{ __('Recipient') }}">
                                              <div>
                                                  {{ $item->recipient->email }}
                                              </div>
                                          </td>

                                          <td data-label="{{ __('Amount') }}">
                                            <div>
                                                {{amount($item->amount,$item->currency->type,2)}} {{$item->currency->code}}
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Charge') }}">
                                            <div>
                                                {{amount($item->charge,$item->currency->type,2)}} {{$item->currency->code}}
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Details') }}">
                                              <div>
                                                {{Str::limit($item->description,80)}}
                                              </div>
                                          </td>


                                          <td data-label="{{ __('Status') }}">
                                            <div>
                                                @if ($item->status == 1)
                                                    <span class="badge bg-success">{{__('Released')}}</span>
                                                @elseif ($item->status == 0)
                                                    <span class="badge bg-warning">{{__('On hold')}}</span>
                                                @elseif ($item->status == 3)
                                                    <span class="badge bg-info">{{__('Disputed')}}</span>
                                                @elseif ($item->status == 4)
                                                    <span class="badge">{{__('Closed')}}</span>
                                                @endif
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Date') }}">
                                            <div>
                                                {{dateFormat($item->created_at)}}
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Action') }}">
                                            <div class="btn-list flex-nowrap">
                                                @if ($item->status != 1 && $item->status != 4)
                                                <a href="javascript:void(0)" data-route="{{route('user.escrow.release',$item->id)}}" class="btn btn-primary btn-sm release">@lang('Release')</a>

                                                  <a href="{{route('user.escrow.dispute',$item->id)}}" class="btn btn-warning btn-sm">@lang('Dispute')</a>
                                                @else
                                                  @lang('N/A')
                                                @endif
                                            </div>
                                          </td>
                                      </tr>
                                  @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $escrows->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-status bg-primary"></div>
            <div class="modal-body text-center py-4">
                <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                <h3>{{__('Confirm Release?')}}</h3>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                    <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">
                        {{__('Cancel')}}
                        </a></div>
                    <div class="col">
                        <form action="" method="get">
                            <button type="submit" class="btn btn-primary w-100 confirm">
                            {{__('Confirm')}}
                            </button>
                        </form>
                    </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>


@endsection

@push('js')
    <script>
        'use strict';
        $('.release').on('click',function() {
            $('#modal-success').find('form').attr('action',$(this).data('route'))
            $('#modal-success').modal('show')
        })
    </script>
@endpush
