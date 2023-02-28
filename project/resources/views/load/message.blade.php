@foreach($message_list as $message)
@if($message->user_id != 0)
<div class="card card-sm shadow-sm">
    <div class="p-2 customer-message">
        <div class="row">
            <div class="col-auto">
                <img class="img-profile rounded-circle" src="{{$message->conversation->user->photo != null ? asset('assets/images/'.$message->conversation->user->photo) : asset('assets/user/img/user.jpg')}}">
            </div>
            <div class="col">
                <div class="text-truncate">
                {{$conv->user->company_name ?? $conv->user->name}}
                </div>
                <div class="text-muted">{{$conv->user->email}}</div>
            </div>
            <div class="col-auto right text-white">
                <div class="badge bg-primary"> {{dateFormat($message->created_at, 'Y-m-d H:i:s')}}</div>
            </div>
        </div>
    </div>
    <div class="p-3">
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
<div class="border-0 shadow-sm">
    <div class="p-2 customer-message">
        <div class="row">
            <div class="col-auto">
                <img class="img-profile rounded-circle" src="{{ $admin->photo ? asset('assets/images/'.$admin->photo) : asset('assets/user/img/user.jpg')}}">
            </div>
            <div class="col">
                <div class="text-truncate">
                {{$admin->name}}
                </div>
                <div class="text-muted">{{$admin->email}}</div>
            </div>
            <div class="col-auto right">
                <div class="badge bg-primary text-white">{{dateFormat($message->created_at, 'Y-m-d H:i:s')}}</div>
            </div>
        </div>
    </div>
    <div class="p-3">
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
