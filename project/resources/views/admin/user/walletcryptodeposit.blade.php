 <div class="form-group">
    <div class="text-center">
        <img id="qrcode" src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl={{$wallet_no}}&choe=UTF-8" class="" alt="">
    </div>
    <div class="text-center mt-2">
        <span id="qrdetails" class="ms-2 check">{{$wallet_no}}</span>
    </div>
    <div class="form-group mb-3 mt-3">
        <label class="form-label required">{{__('Amount')}}</label>
        <input name="amount" id="amount" class="form-control" autocomplete="off" placeholder="{{__('0.0')}}" type="number" step="any" value="{{ old('amount') }}" required>
    </div>

    <input type="hidden" name="user_id" value="{{$user_id}}">
    <input type="hidden" name="currency_id" value="{{$currency_id}}">
    <input type="hidden" name="address" id="address" value="{{$wallet_no}}">
    <input type="hidden" name="wallet_type" value="crypto">
 </div>

                        <div class="form-group">
                            <button type="submit" id="submit-btn" class="btn btn-primary w-100 mt-2">{{ __('Deposit') }}</button>
                        </div>
