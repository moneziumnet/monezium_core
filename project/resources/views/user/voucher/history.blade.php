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
          {{__('Redeemed Voucher History')}}
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
                  @if (count($history) == 0)
                      <h3 class="text-center py-5">{{__('No Redeemed Voucher Data Found')}}</h3>
                  @else 
                      <div class="table-responsive">
                          <table class="table table-vcenter table-mobile-lg card-table">
                              <thead>
                              <tr>
                                  <th>{{ __('Voucher Code') }}</th>
                                  <th>{{ __('Amount') }}</th>
                                  <th>{{ __('Reedemed at') }}</th>
                              </tr>
                              </thead>
                              <tbody>
                                @foreach($history as $item)
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

                                        <td data-label="{{ __('Reedemed at') }}">
                                            <div>
                                              {{dateFormat($item->updated_at)}}
                                            </div>
                                        </td>

                                        
                                    </tr>
                                @endforeach
                              </tbody>
                          </table>
                      </div>
                      {{ $history->links() }}
                  @endif
              </div>
          </div>
      </div>
  </div>
</div>

@endsection

@push('js')

@endpush