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
            <a href="{{route('user.message.index')}}" class="btn btn-primary w-100" >
                <i class="fas fa-backward me-1"></i>{{__('Back')}}
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
                <div class="support-ticket-wrapper ">
                    <div class="panel panel-primary">
                        <div class="gocover" style="background: url({{ asset('assets/images/'.$gs->loader) }}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
                        <div class="panel-body">
                            <form id="ticket_submit" data-href="{{ route('user.message.load',$conv->id) }}" action="{{route('user.message.store')}}" method="POST">
                                {{csrf_field()}}
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <textarea class="form-control" id="message" name="message" style="resize: vertical;" placeholder="{{ __('Your Message') }}" ></textarea>
                                    </div>
                                </div>


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

                                <input type="hidden" name="conversation_id" value="{{$conv->id}}">
                                <input type="hidden" name="user_id" value="{{$conv->user->id}}">
                                <div class="form-group">
                                    <button class="mybtn1 btn btn-primary mt-2 mb-2">
                                        {{ __('Send') }}
                                    </button>
                                </div>
                                <hr>
                            </form>
                        </div>
                        <div class="panel-footer" id="messages">
                          @foreach($message_list as $message)
                            @if($message->user_id != 0)
                            <div class="card card-sm border-0 shadow-sm">
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col-auto">
                                            <span class="avatar" style="background-image: url({{$message->conversation->user->photo != null ? asset('assets/images/'.$message->conversation->user->photo) : asset('assets/user/img/user.jpg')}})"></span>
                                        </div>
                                        <div class="col">
                                            <div class="text-truncate">
                                            {{$conv->user->company_name ?? $conv->user->name}}
                                            </div>
                                            <div class="text-muted">{{$conv->user->email}}</div>
                                        </div>
                                        <div class="col-auto right">
                                            <div class="badge bg-primary"> {{dateFormat($message->created_at, 'Y-m-d H:i:s')}}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="user">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="reply-area">
                                                    <div class="left">
                                                        @php
                                                            echo $message->message
                                                        @endphp
                                                    </div>
                                                    <div class="mt-2">
                                                        @if($message->document)
                                                         @foreach (explode(",", $message->document) as $docu)
                                                            <a target="_blank" class="ml-2" href="{{ asset('assets/doc/' . $docu) }}">{{ $docu }}</a>
                                                         @endforeach
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <br>
                            @else
                            <div class="card card-sm border-0 shadow-sm">
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col-auto">
                                            <span class="avatar" style="background-image: url({{ $admin->photo ? asset('assets/images/'.$admin->photo) : asset('assets/user/img/user.jpg')}})"></span>
                                        </div>
                                        <div class="col">
                                            <div class="text-truncate">
                                            {{$admin->name}}
                                            </div>
                                            <div class="text-muted">{{$admin->email}}</div>
                                        </div>
                                        <div class="col-auto right">
                                            <div class="badge bg-primary">{{dateFormat($message->created_at, 'Y-m-d H:i:s')}}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="user">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="reply-area">
                                                    <div class="left">
                                                        @php
                                                            echo $message->message
                                                        @endphp
                                                    </div>
                                                    <div class="mt-2">
                                                        @if($message->document)
                                                         @foreach (explode(",", $message->document) as $docu)
                                                            <a target="_blank" class="ml-2" href="{{ asset('assets/doc/' . $docu) }}">{{ $docu }}</a>
                                                         @endforeach
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br>
                            @endif
                            @endforeach

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

</script>

@endpush

