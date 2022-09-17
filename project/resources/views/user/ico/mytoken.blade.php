@extends('layouts.user')

@push('css')
@endpush



@section('contents')
    @include('user.ico.tabs')

    <div class="page-body">
        <div class="container-xl mt-3 mb-3">
            <div class="row row-cards">
                <div class="row justify-content" style="max-height: 1600px;overflow-y: auto;">
                    @if (count($ico_tokens) == 0)
                        <div class="card">
                            <h3 class="text-center py-5">{{ __('No ICO Token Data Found') }}</h3>
                        </div>
                    @else
                        @foreach ($ico_tokens as $key => $val)
                            <div class="col-lg-3 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <div class="col-8">
                                                <h5 class="h4 mb-2 font-weight-bolder">
                                                    {{ __('Name: ') }}{{ $val->name }}
                                                </h5>
                                            </div>
                                            <div class="col-4 text-end">
                                                <a class="mr-0" data-bs-toggle="dropdown" aria-haspopup="true"
                                                    aria-expanded="false">
                                                    <i class="fas fa-chevron-circle-down"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-left">
                                                    <a class="dropdown-item" href="{{ route('user.ico.edit', $val->id) }}">
                                                        <i class="fas fa-pencil-alt me-2"></i>{{ __('Edit') }}</a>
                                                    <a class="dropdown-item" href="{{ route('user.ico.delete', $val->id) }}">
                                                        <i class="fas fa-trash-alt me-2"></i>{{ __('Delete') }}</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-12">
                                                <h5 class="mb-1">{{ __('Price: ') }}
                                                    {{ amount($val->price, 1, 2) }} USD</h5>
                                                <h5 class="mb-1">{{ __('Code: ') }} {{ $val->currency->code }}</h5>
                                                <h5 class="mb-1">{{ __('Symbol:') }} {{ $val->currency->symbol }}</h5>
                                                <h5 class="mb-1">{{ __('Total Supply:') }} {{ $val->total_supply }}</h5>
                                                <h5 class="mb-1">{{ __('Balance:') }} {{ $val->balance }}</h5>
                                                <h5 class="mb-3">{{ __('End Date:') }} {{ dateFormat($val->end_date) }}</h5>
                                                <a href ="{{asset('assets/doc/'.$val->white_paper)}}" class="btn btn-primary btn-sm" attributes-list download >{{ __('Download White Paper')}} </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
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

        })
    </script>
@endpush
