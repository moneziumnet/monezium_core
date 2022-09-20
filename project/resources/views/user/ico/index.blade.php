@extends('layouts.user')

@push('css')
@endpush



@section('contents')
    @include('user.ico.tabs')

    <div class="page-body">
        <div class="container-xl mt-3 mb-3">
            <div class="row row-cards">
                <div class="row justify-content" style="max-height: 1600px;overflow-y: auto;">
                    <div class="col-12">

                        @if (count($ico_tokens) == 0)
                            <div class="card">
                                <h3 class="text-center py-5">{{ __('No ICO Token Data Found') }}</h3>
                            </div>
                        @else
                            <div class="card table-responsive mb-4 p-3">
                                <table class="table table-vcenter table-mobile-lg card-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Price') }}</th>
                                            <th>{{ __('Code') }}</th>
                                            <th>{{ __('Symbol') }}</th>
                                            <th>{{ __('Total Supply') }}</th>
                                            <th>{{ __('Balance') }}</th>
                                            <th>{{ __('End Date') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Details') }}</th>
                                            <th>{{ __('Buy') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($ico_tokens as $item)
                                            <tr>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ amount($item->price, 1, 2) }} USD</td>
                                                <td>{{ $item->currency->code }}</td>
                                                <td>{{ $item->currency->symbol }}</td>
                                                <td>{{ $item->total_supply }}</td>
                                                <td>{{ $item->balance }}</td>
                                                <td>{{ dateFormat($item->end_date) }}</td>
                                                <td>
                                                    @if($item->status)
                                                        <span class="badge bg-success">Approved</span>
                                                    @else
                                                        <span class="badge bg-warning">Pending</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-list">
                                                        <button class="btn btn-primary btn-sm details" data-id="{{ $item->id }}">
                                                            {{ __('Details') }}
                                                        </button>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="btn-list">
                                                        @php
                                                            $available = !($item->balance >= $item->total_supply || $item->status == 0 || now()->gt($item->end_date));
                                                        @endphp
                                                        @if($available)
                                                        <a class="btn btn-secondary btn-sm" href="{{ route('user.ico.buy', $item->id) }}">
                                                            {{ __('Buy') }}
                                                        </a>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{$ico_tokens->links()}}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-blur fade" id="modal-details" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-status bg-primary"></div>
                <div class="modal-body text-center py-4">
                    <i class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                    <h3>@lang('ICO Token Details')</h3>
                    <div class="ico-token-details">

                    </div>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <a href="#" class="btn w-100" data-bs-dismiss="modal">
                                    @lang('Close')
                                </a>
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

        $('.details').on('click', function() {
            var url = "{{url('user/ico/details/')}}"+'/'+$(this).data('id')
            $.get(url,function (res) {
                if(res == 'empty'){
                    $('.ico-token-details').html('<p>@lang('No details found!')</p>')
                }else{
                    $('.ico-token-details').html(res)
                }
                $('#modal-details').modal('show')
            })
        })
    </script>
@endpush
