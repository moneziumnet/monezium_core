<div class="form-group">
    <div class="form-group mb-3 mt-3">
        <label class="form-label required">{{__('Amount')}}</label>
        <input name="amount" id="amount" class="form-control" autocomplete="off" placeholder="{{__('0.0')}}" type="number" step="any" value="{{ old('amount') }}" required>
    </div>

    <div class="form-group mb-3 mt-3">
        <label class="form-label required">{{__('Receive Address')}}</label>
        <input name="receiver_address" id="receiver_address" class="form-control" autocomplete="off" placeholder="{{__('0x...')}}" type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" required>
    </div>

    <input type="hidden" name="user_id" value="{{$user_id}}">
    <input type="hidden" name="currency_id" value="{{$currency_id}}">
 </div>

                        <div class="form-group">
                            <button type="submit" id="submit-btn" class="btn btn-primary w-100 mt-2">{{ __('Crypto Withdraw') }}</button>
                        </div>
