@extends('layouts.user')

@push('css')
@endpush

@section('contents')

    <div class="page-body">
        <div class="container-xl">
            <div class="d-flex my-3">
              <h2 class="me-auto">Transaction details</h2>
              <a class="btn btn-primary" href="{{route('user.card.index')}}">Back</a>
            </div>
            <div class="row row-deck row-cards">
                <div class="col-lg-12">
                    <div class="card">
                        @if (count($transactions) == 0)
                            <p class="text-center p-5">@lang('NO DATA FOUND')</p>
                        @else
                            <div class="table-responsive">
                                <table class="table card-table table-vcenter text-nowrap datatable">
                                    <thead>
                                        <tr>
                                            <th class="w-1">@lang('No').
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-sm text-dark icon-thick" width="24" height="24"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <polyline points="6 15 12 9 18 15" />
                                                </svg>
                                            </th>
                                            <th>@lang('Date')</th>
                                            <th>@lang('Transaction ID')</th>
                                            <th>@lang('Sender')</th>
                                            <th>@lang('Receiver')</th>
                                            <th>@lang('Remark')</th>
                                            <th>@lang('Amount')</th>
                                            <th class="text-end" style="padding-right: 28px;">@lang('Details')</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $i = ($transactions->currentpage() - 1) * $transactions->perpage() + 1;
                                        @endphp
                                        @forelse ($transactions as $key=>$data)
                                            <tr>
                                                <td data-label="@lang('No')">
                                                    <div>
                                                        <span class="text-muted">{{ $i++ }}</span>
                                                    </div>
                                                </td>
                                                <td data-label="@lang('Date')">
                                                    {{ dateFormat($data->created_at, 'd-M-Y') }}</td>
                                                <td data-label="@lang('Transaction ID')">
                                                    {{ __(str_dis($data->trnx)) }}
                                                </td>
                                                <td data-label="@lang('Sender')">
                                                    {{ __(json_decode($data->data)->sender ?? '') }}
                                                </td>
                                                <td data-label="@lang('Receiver')">
                                                    {{ __(json_decode($data->data)->receiver ?? '') }}
                                                </td>
                                                <td data-label="@lang('Remark')">
                                                    <span
                                                        class="badge badge-dark">{{ ucwords(str_replace('_', ' ', $data->remark)) }}</span>
                                                </td>
                                                <td data-label="@lang('Amount')">
                                                    <span
                                                        class="{{ $data->type == '+' ? 'text-success' : 'text-danger' }}">{{ $data->type }}
                                                        {{ amount($data->amount, $data->currency->type, 2) }}
                                                        {{ $data->currency->code }}</span>
                                                </td>
                                                <td data-label="@lang('Details')" class="text-end">
                                                    <button class="btn btn-primary btn-sm details"
                                                        data-data="{{ $data }}">@lang('Details')</button>
                                                </td>
                                            </tr>
                                        @empty
                                            <p>@lang('NO DATA FOUND')</p>
                                        @endforelse

                                    </tbody>
                                </table>
                            </div>
                            {{ $transactions->links() }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-status bg-primary"></div>
                <div class="modal-body text-center py-4">
                    <i class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                    <h3>@lang('Transaction Details')</h3>
                    <p class="trx_details"></p>
                    <ul class="list-group mt-2">
                    </ul>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">
                                    @lang('Close')
                                </a></div>
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
        $('.details').on('click', function() {
            var url = "{{ url('user/transaction/details/') }}" + '/' + $(this).data('data').id
            $('.trx_details').text($(this).data('data').details)
            $.get(url, function(res) {
                if (res == 'empty') {
                    $('.list-group').html('<p>@lang('No details found!')</p>')
                } else {
                    $('.list-group').html(res)
                }
                $('#modal-success').modal('show')
            })
        })
    </script>
@endpush
