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
                                <div class="form-group">
                                    <input type="hidden" name="conversation_id" value="{{$conv->id}}">
                                    <input type="hidden" name="user_id" value="{{$conv->user->id}}">
                                    <textarea class="form-control" name="message" id="wrong-invoice" rows="5" style="resize: vertical;" required="" placeholder="{{ __('Your Message') }}"></textarea>
                                </div>
                                <div class="form-group">
                                    <button class="mybtn1 btn btn-primary mt-2 mb-2">
                                        {{ __('Send') }}
                                    </button>
                                </div>
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

