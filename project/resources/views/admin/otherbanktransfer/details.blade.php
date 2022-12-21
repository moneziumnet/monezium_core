<div class="modal-status bg-primary"></div>
<div class="modal-body text-center py-4">
    <i class="fas fa-info-circle fa-3x text-primary mb-2"></i>
    <h3>@lang('Bank Transfer Details')</h3>
    <ul class="list-group details-list mt-2">
        <li class="list-group-item">@lang('Bank Name')<span>{{ $data->beneficiary->bank_name }}</span></li>
        <li class="list-group-item">@lang('Account Name')<span>{{ $data->beneficiary->name }}</span></li>
        <li class="list-group-item">@lang('Beneficiary Address')<span>{{ $data->beneficiary->address }}</span></li>
        <li class="list-group-item">@lang('Bank Address')<span>{{ $data->beneficiary->bank_address }}</span></li>
        <li class="list-group-item">@lang('Account IBAN')<span>{{ $data->beneficiary->account_iban }}</span></li>
        <li class="list-group-item">@lang('SWIFT/BIC')<span>{{ $data->beneficiary->swift_bic }}</span></li>
        <li class="list-group-item">@lang('Customer Name')<span>{{ $user->company_name ?? $user->name}}</span></li>
        <li class="list-group-item">@lang('Customer Email')<span>{{ $user->email }}</span></li>
        <li class="list-group-item">@lang('Customer Bank IBAN')<span>{{ $bankaccount->iban }}</span></li>
        <li class="list-group-item">@lang('Customer Bank SWIFT')<span>{{ $bankaccount->swift }}</span></li>
        @php
            $status_color = 'primary';
            if ($webhook_request) {
                if ($webhook_request->status == "processing") {
                    $status_color = 'warning';
                } elseif ($webhook_request->status == "completed") {
                    $status_color = 'success';
                } elseif ($webhook_request->status == "failed") {
                    $status_color = 'danger';
                } 
            }
        @endphp
        <li class="list-group-item send-info">@lang('Status')<span><span class="badge badge-{{$status_color}}">{{$webhook_request ? $webhook_request->status : 'Pending'}}</span></span></li>
        @if ($data->document)
            @php
                $arr_file_name = explode('.', $data->document);
                $extension = $arr_file_name[count($arr_file_name) - 1];
            @endphp
            <li class="list-group-item">@lang('Document')
                <span>
                    @if (in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'pdf']))
                        <a target="_blank"
                            href="https://docs.google.com/gview?url={{ asset('assets/doc/' . $data->document) }}">{{ $data->document }}</a>
                    @else
                        <a target="_blank" href="{{ asset('assets/doc/' . $data->document) }}">{{ $data->document }}</a>
                    @endif
                </span>
            </li>
        @endif
    </ul>
</div>
<div class="modal-footer">
    <div class="w-100">
        <div class="row">
            <div class="col">
            @if ($data->status == 3 && $status_color == 'success')
                <div class="row action-button">
                    <div class="col-md-12 mt-2">
                        <button
                            class="btn btn-success w-100"
                            id="complete_transfer"
                            data-toggle="modal"
                            data-target="#statusModal"
                            data-href="{{route('admin.other.banks.transfer.status', ['id1' => $data->id, 'status' => 1])}}"
                        >{{__("Approve")}}</button>
                    </div>
                </div>
            @elseif ($data->status == 3 && $status_color == 'danger')
                <div class="row action-button">
                    <div class="col-md-12 mt-2">
                        <button
                            class="btn btn-danger w-100"
                            id="reject_transfer"
                            data-toggle="modal"
                            data-target="#statusModal"
                            data-href="{{route('admin.other.banks.transfer.status', ['id1' => $data->id, 'status' => 2])}}"
                        >{{__("Reject")}}</button>
                    </div>
                </div>
            @elseif ($data->status == 3 && $nogateway)
                <div class="row action-button">
                    <div class="col-md-6 mt-2">
                        <button
                            class="btn btn-success w-100"
                            id="complete_transfer"
                            data-toggle="modal"
                            data-target="#statusModal"
                            data-href="{{route('admin.other.banks.transfer.status', ['id1' => $data->id, 'status' => 1])}}"
                        >{{__("Approve")}}</button>
                    </div>
                    <div class="col-md-6 mt-2">
                        <button
                            class="btn btn-danger w-100"
                            id="reject_transfer"
                            data-toggle="modal"
                            data-target="#statusModal"
                            data-href="{{route('admin.other.banks.transfer.status', ['id1' => $data->id, 'status' => 2])}}"
                        >{{__("Reject")}}</button>
                    </div>
                </div>
            @elseif ($data->status == 0 && $status_color == 'primary')
                <div class="row action-button">
                    <div class="col-md-6 mt-2">
                        <button
                            class="btn btn-primary w-100"
                            id="send_request"
                            data-toggle="modal"
                            data-target="#statusModal"
                            data-href="{{route('admin.other.banks.transfer.status', ['id1' => $data->id, 'status' => 3])}}"
                        >{{__("Send Request")}}</button>
                    </div>
                    <div class="col-md-6 mt-2">
                        <button
                            class="btn btn-danger w-100"
                            id="reject_transfer"
                            data-toggle="modal"
                            data-target="#statusModal"
                            data-href="{{route('admin.other.banks.transfer.status', ['id1' => $data->id, 'status' => 2])}}"
                        >{{__("Reject")}}</button>
                    </div>
                </div>
            @elseif ($data->status != 0 )
                <button class="btn w-100 closed" data-bs-dismiss="modal">
                    @lang('Close')
                </button>
            @endif
            </div>
        </div>
    </div>
</div>
