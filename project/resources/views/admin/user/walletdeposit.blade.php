 <div class="form-group">
                            <label for="fullname">{{ __('Full Name') }}</label>
                            <input type="text" class="form-control" id="fullname" name="fullname" placeholder="{{ __('Enter Name') }}" required>

                            <label >{{ __('Address') }}</label>
                            <input type="text" class="form-control" id="address" name="address" placeholder="{{ __('Enter Your Address') }}"   required>

                            <label >{{ __('Bank') }}</label>
                            <input type="text" class="form-control" id="bank" name="bank" placeholder="{{ __('Enter Bank') }}"   required>

                            <label >{{ __('SWIFT') }} </label>
                            <input type="text" class="form-control" id="swift" name="swift" placeholder="{{ __('Enter Swift') }}"  required>

                            <label >{{ __('Amount') }} </label>
                            <input type="number" class="form-control" id="amount" name="amount" placeholder="{{ __('Enter Amount') }}"  step="any" required>

                            <label >{{ __('Description') }}</label>
                            <textarea type="Text" class="form-control" id="description" name="description" placeholder="{{ __('Enter Description') }}"  rows="5" required></textarea>
                            <input type="hidden" name="wallet_type" value="flat">
 </div>

                        <div class="form-group">
                            <button type="submit" id="submit-btn" class="btn btn-primary w-100 mt-2">{{ __('Deposit') }}</button>
                        </div>
