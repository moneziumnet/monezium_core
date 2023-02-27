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
                          <div class="panel-footer">
                            <form id="messageform" data-href="{{ route('user.message.load',$conv->id) }}" action="{{route('user.message.store')}}" method="POST">
                                {{csrf_field()}}
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <textarea class="form-control summernote" name="message" style="resize: vertical;" placeholder="{{ __('Your Message') }}" required></textarea>
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
                        <div class="panel-body" id="messages">
                          @foreach($conv->messages as $message)
                            @if($message->user_id != 0)
                            <div class="single-reply-area user">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="reply-area">
                                            <div class="left">
                                                <p>{{$message->message}}</p>
                                            </div>
                                            <div class="right">
                                                <img class="img-circle" src="{{$message->conversation->user->photo != null ? asset('assets/images/'.$message->conversation->user->photo) : asset('assets/user/img/user.jpg')}}" alt="">
                                                <p class="ticket-date">{{$message->conversation->user->name}}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <br>
                            @else
                            <div class="single-reply-area admin">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="reply-area">
                                            <div class="left">
                                                <img class="img-circle" src="{{ $admin->photo ? asset('assets/images/'.$admin->photo) : asset('assets/user/img/user.jpg')}}" alt="">
                                                <p class="ticket-date">{{ __('Admin') }}</p>
                                            </div>
                                            <div class="right">
                                                <p>{{$message->message}}</p>
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

