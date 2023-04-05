<div class="modal-title" style="border-radius: 10px 10px 0 0">
    <div class="ms-3">
      <p>{{('Request Money Details')}}</p>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body text-center">
    <i class="fas fa-info-circle fa-3x text-primary mb-2"></i>
    <h3>@lang('Request Money Details')</h3>
    <ul class="list-group details-list mt-2">
        <li class="list-group-item">@lang('Request From')<span class="list-item-content">{{$from->company_name ?? $from->name}}</span></li>
        <li class="list-group-item">@lang('Request To')<span class="list-item-content">{{ $to ? ($to->company_name ?? $to->name) : $data->receiver_name }}</span></li>
        <li class="list-group-item">@lang('Amount')<span class="list-item-content">{{ $data->currency->symbol }}{{ amount($data->amount, 1, 2) }} {{ $data->currency->code }}</span></li>
        <li class="list-group-item">@lang('Cost')<span class="list-item-content">{{ $data->currency->symbol }}{{ amount($data->cost + $data->supervisor_cost, 1, 2) }} {{ $data->currency->code }}</span></li>
        <li class="list-group-item">@lang('Amount To Get')<span class="list-item-content">{{ $data->currency->symbol }}{{ amount($data->amount - $data->cost - $data->supervisor_cost, 1, 2) }} {{ $data->currency->code }}</span></li>
        @php
            if($data->status == 1){
                $bclass = "success";
                $bstatus = "Completed";
            } else if($data->status == 2) {
                $bclass = "danger";
                $bstatus = "Cancelled";
            } else {
                $bclass = "warning";
                $bstatus = "Pending";
            }
        @endphp
        <li class="list-group-item">@lang('Status')<span><span class="badge bg-{{$bclass}}">{{ $bstatus }}</span></span></li>
        <li class="list-group-item">@lang('Description')<span class="list-item-content">{{ $data->details ?? 'No description' }}</span></li>
        <li class="list-group-item">@lang('Request Date')<span class="list-item-content">{{ dateFormat($data->created_at) }}</span></li>
    </ul>
</div>
<div class="modal-footer">
    <div class="w-100">
        <div class="row">
            @if ($data->status == 0 && $from != auth()->user())
            <div class="col-md-6">
                <a href="javascript:;" id="sendBtn" data-href="{{route('user.request.money.send', $data->id)}}" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modal-success">
                    {{__('Send')}}
                </a>
            </div>
            <div class="col-md-6">
                <a href="javascript:;" id="cancelBtn" data-href="{{ route('user.request.money.cancel',$data->id) }}" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#modal-cancel">
                    {{__('Cancel')}}
                </a>
            </div>
            @endif
        </div>
    </div>
</div>