<form action="{{route('admin.bank.plan.detail.update',$detail->id)}}" method="post">
    @csrf
            <div class="form-group">
            <label for="detail_type">{{ __('Name') }}</label>
            <input type="text" pattern="[^()/><\][\\\-;&$@!|]+" class="form-control" id="detail_type" name="detail_type" placeholder="{{ __('Enter Name') }}" value="{{ $detail->type }}" readonly>
            </div>

            <div class="form-group">
            <label for="detail_min">{{ __('Min') }}</label>
            <input type="number" class="form-control" id="detail_min" name="detail_min" placeholder="{{ __('Enter Min Value') }}" min="0" value="{{ $detail->min }}" required>
            </div>

            <div class="form-group">
                <label for="detail_max">{{ __('Max') }}</label>
                <input type="number" class="form-control" id="detail_max" name="detail_max" placeholder="{{ __('Enter Max Value') }}" min="1" value="{{ $detail->max }}" required>
            </div>

            <div class="form-group">
                <label for="detail_daily">{{ __('Maximum Send Money') }} ({{ __('Daily')}})</label>
                <input type="number" class="form-control" id="detail_daily" name="detail_daily" placeholder="{{ __('Enter Max Value') }}" min="1" value="{{ $detail->daily_limit }}" required>
            </div>

            <div class="form-group">
                <label for="detail_monthly">{{ __('Maximum Send Money') }} ({{ __('Monthly')}})</label>
                <input type="number" class="form-control" id="detail_monthly" name="detail_monthly" placeholder="{{ __('Enter Max Value') }}" min="1" value="{{ $detail->monthly_limit }}" required>
            </div>

            <div class="form-group">
                <button type="submit" id="submit-btn" class="btn btn-primary w-100 mt-2">{{ __('Update') }}</button>
            </div>

        </div>
</form>
