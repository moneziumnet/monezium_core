
@extends('layouts.admin')

@section('content')


    <div class="card">
        <div class="d-sm-flex align-items-center py-3 justify-content-between">
        <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Conversation With') }} {{$conv->user->name}}</h5>
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.user.message')}}">{{ __('Manage Tickets') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.user.message') }}">{{ __('All Tickets') }}</a></li>
        </ol>
        </div>
    </div>


    <!-- Row -->
    <div class="row mt-3">
      <!-- Datatables -->
      <div class="col-lg-12">



        <div class="order-table-wrap support-ticket-wrapper ">
            <div class="panel panel-primary">
            <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
            @include('includes.admin.form-success')
                <form id="ticket_submit" action="{{route('admin.message.store')}}" data-href="{{ route('admin-message-load',$conv->id) }}" method="POST">
                    {{csrf_field()}}
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <textarea class="form-control summernote" name="message" style="resize: vertical;" placeholder="{{ __('Your Message') }}" ></textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-label">{{__('Attachments')}}</div>
                            <input class= "document" name="document[]" class="form-control" type="file" accept=".doc,.docx,.pdf,.png,.jpg">
                        </div>
                        <div class="col-md-1 mb-3">
                            <div class="form-label">&nbsp;</div>
                            <button type="button" class="btn btn-primary w-100 doc_add"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="doc-extra-container">
                    </div>
                    <input type="hidden" name="user_id" value="0">
                    <input type="hidden" name="conversation_id" value="{{$conv->id}}">
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-rounded">
                            {{ __('Add Reply') }}
                        </button>
                    </div>
                </form>
            <hr>
                <div class="panel-body" id="messages">
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
                                    <div class="badge bg-primary ml-2 text-white"> {{dateFormat($message->created_at, 'Y-m-d H:i:s')}}</div>
                                    </div>
                                    <div class="text-muted">{{$conv->user->email}}</div>
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
                                                 {{-- {{strip_tags($message->message)}} --}}
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
                                    <div class="badge bg-primary text-white ml-2">{{dateFormat($message->created_at, 'Y-m-d H:i:s')}}</div>
                                    </div>
                                    <div class="text-muted">{{$admin->email}}</div>
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
                </div>

            </div>
        </div>
      </div>
      <!-- DataTable with Hover -->

    </div>
    <!--Row-->
{{-- DELETE MODAL --}}
<div class="modal fade confirm-modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">{{ __("Confirm Delete") }}</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div class="modal-body">
				<p class="text-center">{{__("You are about to delete this Product.")}}</p>
				<p class="text-center">{{ __("Do you want to proceed?") }}</p>
			</div>

			<div class="modal-footer">
				<a href="javascript:;" class="btn btn-secondary" data-dismiss="modal">{{ __("Cancel") }}</a>
				<a href="javascript:;" class="btn btn-danger btn-ok">{{ __("Delete") }}</a>
			</div>
		</div>
	</div>
</div>

@endsection


@section('scripts')
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
@endsection
