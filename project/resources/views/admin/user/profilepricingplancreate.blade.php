<form action="{{route('admin.create.charge')}}" method="post">
    @csrf
    <input  type="hidden" name="user_id" class="form-control" value="{{$plandetail->user_id}}">
    <input  type="hidden" name="plan_id" class="form-control" value="{{$plandetail->plan_id}}">
    <input  type="hidden" name="slug" class="form-control" value="{{$plandetail->slug}}">
    @php
        $accountlist = ['Deposit', 'Send', 'Recieve', 'Escrow', 'Withdraw', 'Exchange', 'Payment between accounts']
    @endphp
    <div class="form-group">
    <label class="form-label">{{__('Select Type')}}</label>
    <select name="name" id="wallet_id" class="form-control mb-3" required>
        @foreach ($accountlist as $key => $account )
        <option value="{{$account}}">{{$account}} </option>
        @endforeach
    </select>
    </div>

    @if($plandetail->data)
    @foreach ($plandetail->data as $key => $value)
        <div class="form-group">
            <label for="">{{ucwords(str_replace('_',' ',$key))}}
            @if ($key == 'percent_charge' || $key == 'commission')
                (%)
            @endif
            <span class="text-danger">*</span></label>
            <input type="number" step="any" name="{{$key}}" class="form-control" value="{{@$value}}">
        </div>
    @endforeach
        @if ($plandetail->name == 'Transfer Money')
        <code>(@lang('Put 0 for no limit'))</code>
        @endif
    @endif
    {{-- @if (access('update charge')) --}}
    <div class="form-group text-right">
        {{-- <button class="btn btn-primary btn-lg">@lang('Update')</button> --}}
        <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Create') }}</button>
    </div>
    {{-- @endif --}}
</form>
