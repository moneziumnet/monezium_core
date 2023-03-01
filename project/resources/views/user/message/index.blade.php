@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Support Ticket')}}
          </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="javascript:;" class="btn btn-primary w-100 apply-loan" data-bs-toggle="modal" data-bs-target="#modal-message">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
              {{__('Create New Ticket')}}
          </a>
          </div>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
                    @if (count($convs) == 0)
                        <h3 class="text-center py-5">{{__('No Data Found')}}</h3>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter table-mobile-md card-table">
                                <thead>
                                <tr>
                                    <th>{{ __('Subject') }}</th>
                                    <th>{{ __('Department') }}</th>
                                    <th>{{ __('Message') }}</th>
                                    <th>{{ __('Time') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Priority') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                  @foreach($convs as $conv)
                                    <tr class="conv">
                                      <input type="hidden" value="{{$conv->id}}">
                                      <td data-label="{{ __('Subject') }}">
                                        <div>
                                          {{$conv->subject}}
                                        </div>
                                      </td>
                                      <td data-label="{{ __('Department') }}">
                                        <div>
                                          {{$conv->department}}
                                        </div>
                                      </td>
                                      <td data-label="{{ __('Message') }}">
                                        <div>
                                          {{$conv->message}}
                                        </div>
                                      </td>

                                      <td data-label="{{ __('Time') }}">
                                        <div>
                                          {{$conv->created_at->diffForHumans()}}
                                        </div>
                                      </td>
                                      <td data-label="{{ __('Status') }}">
                                        @php
                                            if($conv->status == 'open') {
                                                $color = "text-primary";
                                            }
                                            else {
                                                $color = "text-red";
                                            }
                                        @endphp
                                        <div class="{{$color}}">
                                          {{ucfirst($conv->status)}}
                                        </div>
                                      </td>
                                      <td data-label="{{ __('Priority') }}">
                                        @php
                                            switch ($conv->priority) {
                                                case 'Low':
                                                    $pr_color = "text-green";
                                                    break;
                                                case 'Medium':
                                                    $pr_color = 'text-yellow';
                                                    break;
                                                default:
                                                    $pr_color = "text-red";
                                                    break;
                                            }
                                        @endphp
                                        <div class="{{$pr_color}}">
                                          {{$conv->priority}}
                                        </div>
                                      </td>
                                      <td data-label="{{ __('Action') }}">
                                        <div class="d-flex">
                                          <a href="{{route('user.message.show',$conv->id)}}" class="link view me-1 btn d-block btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-original-title="{{__('Reply')}}"><i class="fa fa-reply"></i></a>
                                          @if ($conv->status == 'open')
                                              <a href="javascript:;" data-bs-toggle="tooltip" data-bs-original-title="{{__('Close')}}" data-route="{{route('user.message.status',[$conv->id ,'closed'])}}" class="link btn btn-primary d-block btn-sm me-1 closed"><i class="fa fa-check"></i></a>
                                          @endif
                                        </div>
                                      </td>

                                    </tr>
                                  @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $convs->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>



<div class="modal modal-blur fade" id="modal-message" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{('Create Ticket')}}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form action="{{ route('user.send.message') }}" method="post" enctype="multipart/form-data">
          @csrf
          <div class="modal-body">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="form-label">{{__('Subject')}}</div>
                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" name="subject" placeholder="{{ __('Subject') }}" autocomplete="off" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-label">{{__('Department')}}</div>
                    <select class="form-select shadow-none" name="department" required>
                        <option value="" selected>{{__('Select')}}</option>
                        @foreach (explode(" , ", $user->section) as $section)
                          <option value="{{$section}}">{{$section}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-label">{{__('Priority')}}</div>
                    <select class="form-select shadow-none" name="priority" required>
                        <option value="" selected>{{__('Select')}}</option>
                        <option value="High">{{__('High')}}</option>
                        <option value="Medium">{{__('Medium')}}</option>
                        <option value="Low">{{__('Low')}}</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-1">
                    <div class="form-label">{{__('Message')}}</div>
                    <textarea class="form-control" id="message" name="message" placeholder="{{ __('Your Message') }}"></textarea>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-label">{{__('Document')}}</div>
                    <input class= "document" name="document[]" class="form-control" type="file" accept=".doc,.docx,.pdf,.png,.jpg">
                </div>
                <div class="col-md-1 mb-3">
                    <div class="form-label">&nbsp;</div>
                    <button type="button" class="btn btn-primary w-100 doc_add"><i class="fas fa-plus"></i></button>
                </div>
            </div>
            <div class="doc-extra-container">
            </div>
            <hr>


          <div class="modal-footer">
              <button type="submit" id="submit-btn" class="btn btn-primary">{{ __('Submit') }}</button>
          </div>
      </form>
      </div>
    </div>
  </div>
</div>

<div class="modal modal-blur fade" id="modal-closed" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="modal-status bg-primary"></div>
        <div class="modal-body text-center py-4">
            <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
            <h3>{{__('Confirm Ticket Closed?')}}</h3>
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

<div class="modal modal-blur fade" id="confirm-delete" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      <div class="modal-status bg-danger"></div>
      <div class="modal-body text-center py-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v2m0 4v.01" /><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" /></svg>
        <h3>{{__('Are you sure')}}?</h3>
        <div class="text-muted">{{__("You are about to delete this Ticket.")}}</div>
      </div>
      <div class="modal-footer">
        <div class="w-100">
          <div class="row">
            <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">
                {{__('Cancel')}}
              </a></div>
            <div class="col">
              <a href="javascript:;" class="btn btn-danger w-100 btn-ok">
                {{__('Delete')}}
              </a>
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

<script type="text/javascript">
    'use strict';

    $('#confirm-delete').on('show.bs.modal', function(e) {
        $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
    });

    $('.doc_add').on('click',function(){
        $('.doc-extra-container').append(`

        <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-label required">{{__('Document')}}</div>
                        <input class= "document" name="document[]" class="form-control" type="file" accept=".doc,.docx,.pdf,.png,.jpg">
                    </div>
                    <div class="col-md-1 mb-3">
                        <div class="form-label">&nbsp;</div>
                        <button type="button" class="btn btn-danger w-100 doc_remove"><i class="fas fa-times"></i></button>
                    </div>
                </div>

        `);
    })
    $(document).on('click','.doc_remove',function () {
        $(this).closest('.row').remove()
    })

    $('.closed').on('click',function() {
        $('#modal-closed').find('form').attr('action',$(this).data('route'))
        $('#modal-closed').modal('show')
    })

</script>

@endpush
