@if ($plandetail->id)
<form action="{{route('user-pricingplan-update-charge',$plandetail->id)}}" method="post">
    @csrf
    @if($plandetail->data)
    @foreach ($plandetail->data as $key => $value)
        <div class="form-group">
            <label for="">{{ucwords(str_replace('_',' ',$key))}}
            @if ($key == 'percent_charge' || $key == 'commission')
                (%)
            @endif
            <span class="text-danger">*</span></label>
            <input type="text" name="{{$key}}" class="form-control" style="width: 100%;" value="{{@$value}}">
        </div>
    @endforeach
        @if ($plandetail->name == 'Transfer Money')
        <code>(@lang('Put 0 for no limit'))</code>
        @endif
    @endif
    {{-- @if (access('update charge')) --}}
    <div class="form-group text-right">
        {{-- <button class="btn btn-primary btn-lg">@lang('Update')</button> --}}
        <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Update') }}</button>
    </div>
    {{-- @endif --}}
</form>
@else
<form action="{{route('user-pricingplan-create-charge')}}" method="post">
    @csrf
    <input  type="hidden" name="name" class="form-control" value="{{$plandetail->name}}">
    <input  type="hidden" name="user_id" class="form-control" value="{{$plandetail->user_id}}">
    <input  type="hidden" name="plan_id" class="form-control" value="0">
    <input  type="hidden" name="slug" class="form-control" value="{{$plandetail->slug}}">

    @if($plandetail->data)
    @foreach ($plandetail->data as $key => $value)
        <div class="form-group">
            <label for="">{{ucwords(str_replace('_',' ',$key))}}
            @if ($key == 'percent_charge' || $key == 'commission')
                (%)
            @endif
            <span class="text-danger">*</span></label>
            <input type="text" name="{{$key}}" class="form-control" style="width: 100%;" value="{{@$value}}">
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
@endif



