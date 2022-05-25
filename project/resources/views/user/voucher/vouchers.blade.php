@extends('layouts.user')

@push('css')
    
@endpush

@section('contents')
<div class="container-xl">
  <div class="page-header d-print-none">
    <div class="row align-items-center">
      <div class="col">
        <div class="page-pretitle">
          {{__('Overview')}}
        </div>
        <h2 class="page-title">
          {{__('Vouchers')}}
        </h2>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
      <div class="row row-cards">
          <div class="col-12">
              <div class="card">
                  @if (count($vouchers) == 0)
                      <h3 class="text-center py-5">{{__('No Voucher Data Found')}}</h3>
                  @else 
                      <div class="table-responsive">
                          <table class="table table-vcenter table-mobile-lg card-table">
                              <thead>
                              <tr>
                                  <th>{{ __('Voucher Code') }}</th>
                                  <th>{{ __('Amount') }}</th>
                                  <th>{{ __('Status') }}</th>
                                  <th>{{ __('Date') }}</th>
                              </tr>
                              </thead>
                              <tbody>
                                @foreach($vouchers as $item)
                                    <tr>
                                        <td data-label="{{ __('Voucher Code') }}">
                                            <div>
                                              {{$item->code}}
                                            </div>
                                        </td>

                                        <td data-label="{{ __('Amount') }}">
                                          <div>
                                            {{numFormat($item->amount)}} {{$item->currency->code}}
                                          </div>
                                        </td>

                                        <td data-label="{{ __('Status') }}">
                                          <div>
                                            @if ($item->status == 0)
                                              <span class="badge bg-secondary">@lang('Unused')</span>
                                            @elseif ($item->status == 1)
                                                <span class="badge bg-success">@lang('Used')</span>
                                            @endif

                                          </div>
                                        </td>
                                        
                                        <td data-label="{{ __('Date') }}">
                                            <div>
                                              {{dateFormat($item->created_at)}}
                                            </div>
                                        </td>

                                        
                                    </tr>
                                @endforeach
                              </tbody>
                          </table>
                      </div>
                      {{ $dps->links() }}
                  @endif
              </div>
          </div>
      </div>
  </div>
</div>

@endsection

@push('js')

@endpush