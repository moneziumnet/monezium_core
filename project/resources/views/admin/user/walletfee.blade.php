<form action="{{route('admin.cal.manual.charge')}}" method="post">
    @csrf
    <input  type="hidden" name="wallet_id" class="form-control" value="{{$wallet->id}}">
    <div class="form-group row m-3 align-items-center">
        <div class="col-md-3"><label>{{__('Select Fee')}}</label></div>
        <div class="col-md-9">
            <select name="charge_id" id="wallet_id" class="form-control mb-3" required>
                @foreach ($manual as $key => $data )
                <option value="{{$data->id}}">{{$data->name}} </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-group text-center">
        {{-- <button class="btn btn-primary btn-lg">@lang('Update')</button> --}}
        <button type="submit" id="submit-btn" class="btn btn-primary w-25">{{ __('Accept') }}</button>
    </div>
    {{-- @endif --}}
</form>

