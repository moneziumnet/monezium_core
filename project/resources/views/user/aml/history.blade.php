@extends('layouts.user')


@section('contents')
<div class="container-fluid">
  <div class="page-header d-print-none">
    @include('user.aml.tab')
    <div class="row align-items-center">
      <div class="col">
        <div class="page-pretitle">
          {{__('Overview')}}
        </div>
        <h2 class="page-title">
          {{__('Kyc Request History')}}
        </h2>
      </div>
    </div>
  </div>
</div>


<div class="page-body">
  <div class="container-fluid">
      <div class="row row-cards mb-3">
          <div class="col-12">
              <div class="card">
                @includeIf('includes.flash')
                  @if (count($history) == 0)
                      <h3 class="text-center py-5">{{__('No Request History Found')}}</h3>
                  @else
                      <div class="table-responsive">
                          <table class="table table-vcenter table-mobile-md card-table datatable">
                              <thead>
                                <tr>
                                  <th>{{__('No')}}</th>
                                  <th>{{__('Requirement')}}</th>
                                  <th>{{__('Requested Date')}}</th>
                                  <th>{{__('Submitted Date')}}</th>
                                  <th>{{__('Status')}}</th>
                                </tr>
                              </thead>
                              <tbody>
                                @php
                                    $counter = 1;
                                @endphp
                              @foreach($history as $item)

                                  <tr>
                                      <td data-label="{{ __('No') }}">
                                      <div>
                                        {{$counter}}
                                      </div>
                                    </td>
                                    <td data-label="{{ __('Requirement') }}">
                                        <div>
                                          {{$item->title}}
                                        </div>
                                      </td>
                                      <td data-label="{{ __('Requested Date') }}">
                                        <div>
                                          {{$item->request_date}}
                                        </div>
                                      </td>
                                      <td data-label="{{ __('Submitted Date') }}">
                                        <div>
                                          {{$item->submitted_date}}
                                        </div>
                                      </td>

                                      <td data-label="{{ __('Status') }}">
                                        <div>
                                            @if ($item->status == 0 )
                                                <span class="badge bg-warning">Pending</span>
                                            @elseif ($item->status == 1)
                                                <span class="badge bg-success">Approved</span>
                                            @elseif ($item->status == 2)
                                                <span class="badge bg-danger">Rejected</span>
                                            @elseif ($item->status == 3)
                                                <span class="badge bg-info">Review</span>
                                            @endif
                                        </div>
                                      </td>
                                  </tr>
                                  @php
                                  $counter++;
                              @endphp
                              @endforeach
                              </tbody>
                          </table>
                      </div>
                      {{ $history ->links() }}
                  @endif
              </div>
          </div>
      </div>
  </div>

@endsection


