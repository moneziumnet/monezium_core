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
            {{__('Loan Manage')}}
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
                    @if (count($loans) == 0)
                        <h3 class="text-center py-5">{{__('No Loan Data Found')}}</h3>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter table-mobile-lg card-table">
                                <thead>
                                <tr>
                                    <th>{{ __('Plan No') }}</th>
                                    <th>{{ __('Loan Amount') }}</th>
                                    <th>{{ __('Per Installment') }}</th>
                                    <th>{{ __('Total Installement') }}</th>
                                    <th>{{ __('Next Installment') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('View log') }}</th>
                                    <th>{{ __('Action') }}</th>
                                    <th class="w-1"></th>
                                </tr>
                                </thead>
                                <tbody>
                                  @foreach($loans as $key=>$data)
                                      <tr>
                                          <td data-label="{{ __('Plan No') }}">
                                              <div>
                                                {{ $data->transaction_no }}
                                                <br>
                                              <span class="text-info">{{ $data->plan->title }}</span>
                                              </div>
                                          </td>
                                          <td data-label="{{ __('Loan Amount') }}">
                                            <div>
                                              {{ amount($data->loan_amount, $data->currency->type, 2) }} {{$data->currency->code}}
                                            </div>
                                          </td>
                                          <td data-label="{{ __('Per Installment') }}">
                                            <div>
                                              {{ amount($data->per_installment_amount,$data->currency->type, 2) }} {{$data->currency->code}}
                                            </div>
                                          </td>
                                          <td data-label="{{ __('Total Installement') }}">
                                              <div>
                                                {{ $data->total_installment}}
                                                <br>
                                                <span class="text-info">{{ $data->given_installment }} @lang('Given')</span>
                                              </div>
                                          </td>

                                          <td data-label="{{ __('Next Installment') }}">
                                            <div>
                                              {{ $data->next_installment ?  $data->next_installment->toDateString() : '--'}}
                                            </div>
                                          </td>
                                          <td data-label="{{ __('Status') }}">
                                            <div>
                                              @if ($data->status == 0)
                                                <span class="badge bg-warning">@lang('Pending')</span>
                                              @elseif($data->status == 1)
                                                  <span class="badge bg-success">@lang('Running')</span>
                                              @elseif($data->status == 3)
                                                  <span class="badge bg-info">@lang('Paid')</span>
                                              @else
                                                  <span class="badge bg-danger">@lang('Rejected')</span>
                                              @endif
                                            </div>
                                          </td>
                                          <td data-label="{{__('View Log')}}">
                                            <div class="btn-list flex-nowrap">
                                              <a href="{{ route('user.loans.logs',$data->id) }}" class="btn">
                                                @lang('Logs')
                                              </a>
                                            </div>
                                          </td>
                                          <td data-label="{{__('Action')}}">
                                            <div class="btn-list flex-nowrap">
                                               @if($data->status == 1)
                                                <input type="hidden" name="plan_Id" id="plan_Id" value="{{$data->id}}">

                                              <a href="#" id="finish" class="btn">
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
                        {{ $loans->links() }}
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
        <form action="{{ route('user.loan.finish') }}" method="post">
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
    $('#finish').on('click',function () {
        $('#modal-success').modal('show');
        let id = $('#plan_Id').val();
        $('#planId').val(id);
    })

</script>
@endpush

