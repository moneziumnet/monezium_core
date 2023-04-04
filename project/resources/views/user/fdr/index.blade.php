@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-fluid">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <div class="page-pretitle">
            {{__('Overview')}}
          </div>
          <h2 class="page-title">
            {{__('FDR Manage')}}
          </h2>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-fluid">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
                    @if (count($fdr) == 0)
                        <h3 class="text-center py-5">{{__('No Dps Data Found')}}</h3>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter table-mobile-md card-table">
                                <thead>
                                <tr>
                                    <th>{{ __('Plan No') }}</th>
                                    <th>{{ __('FDR Amount') }}</th>
                                    <th>{{ __('Profit Type') }}</th>
                                    <th>{{ __('Profit') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                  @foreach($fdr as $key=>$data)
                                      <tr>
                                          <td data-label="{{ __('Plan No') }}">
                                            <div>

                                              {{ $data->transaction_no }}
                                              <br>
                                            <span class="text-info">{{ $data->plan->title }}</span>
                                            </div>
                                          </td>

                                          <td data-label="{{ __('FDR Amount') }}">
                                            <div>

                                              {{ amount($data->amount, $data->currency->type, 2)}} {{ $data->currency->code}}
                                              <br>
                                              <span class="text-info">@lang('Profit Rate') {{$data->interest_rate}} (%) </span>
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Profit Type') }}">
                                            <div>
                                              {{ strtoupper($data->profit_type) }}
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Profit') }}">
                                            <div class="text-center text-md-start">

                                              {{ amount($data->profit_amount, $data->currency->type, 2) }} {{ $data->currency->code}}
                                              <br>
                                              @if ($data->profit_type == 'partial')
                                                  <span class="text-info"> @lang('Next Frofit Days') ({{ $data->next_profit_time ? $data->next_profit_time->toDateString() : "--" }})</span>
                                              @else
                                                  <span class="text-info"> @lang('Profit will get after locked period') </span>
                                              @endif
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Status') }}">
                                            <div>
                                              @if ($data->status == 1)
                                                <span class="badge bg-success">@lang('Running')</span>
                                              @else
                                                <span class="badge bg-danger">@lang('Closed')</span>
                                              @endif
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Action') }}">
                                            <div>
                                              @if ($data->status == 1)
                                              <a href="#" id="finish" data-id="{{$data->id}}" class="btn finish">
                                                @lang('Finish')
                                                </a>
                                              @endif
                                            </div>
                                          </td>
                                      </tr>
                                  @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $fdr->links() }}
                    @endif
                </div>
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
            <h3>@lang('Do you want to finish this plan now?')</h3>
        </div>
        <form action="{{ route('user.fdr.finish') }}" method="post">
            @csrf
            <div class="modal-body">
              <div class="form-group">
                  <input type="hidden" name="planId" id="planId" value="">
              </div>
            </div>
            <div class="modal-footer">
                <button type="submit" id="submit-btn" class="btn btn-primary">{{ __('Ok') }}</button>
            </div>
        </form>
    </div>
    </div>
</div>


@endsection

@push('js')
<script>
    $('a.finish').on('click',  function () {
        $this=$(this);
        var data_id = $this.attr('data-id');
        $('#modal-success').modal('show');
        $('#planId').val(data_id);
    })

</script>
@endpush

