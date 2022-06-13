@extends('layouts.admin')


@section('content')

<div class="card">
<div class="d-sm-flex align-items-center justify-content-between">
<h5 class=" mb-0 text-gray-800 pl-3">{{ __('Add New Withdraw Method') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.withdraw')}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{route('admin.withdraw')}}">{{ __('Withdraw Method') }}</a></li>
</ol>
</div>
</div>

    <div class="row justify-content-center mt-3">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">{{ __('Add New Withdraw Method') }}</h6>
                </div>

                <div class="card-body">
                    <form action="" method="post">
                        @include('includes.admin.form-both')

                        @csrf

                            <div class="row">
                            
                                <div class="form-group col-md-12 col-12">

                                    <label for="">@lang('Method Name') <span class="text-danger">*</span> </label>
                                    <input type="text" name="name" class="form-control" autocomplete="off">
                                
                                </div> 
                                
                
                                <div class="form-group col-md-6 col-12">

                                    <label for="">@lang('Fixed charge')</label>
                                    <input type="text" name="fixed_charge" class="form-control" required>
                                
                                </div>  
                                <div class="form-group col-md-6 col-12">

                                    <label for="">@lang('Percent charge')</label>
                                    <input type="text" name="percent_charge" class="form-control" required>
                                
                                </div>  

                                
                                <div class="form-group col-md-6 col-12">

                                    <label for="">@lang('Minimum Amount')</label>
                                    <input type="text" name="min_amount" class="form-control">
                                
                                </div> 
                                
                                <div class="form-group col-md-6 col-12">

                                    <label for="">@lang('Maximum Amount')</label>
                                    <input type="text" name="max_amount" class="form-control">
                                
                                </div> 
    
                                    <div class="form-group col-md-6 col-12">

                                    <label for="">@lang('Select Currency')</label>
                                    <select name="currency" class="form-control">
                                        <option value="">@lang('Select')</option>
                                        @foreach ($currencies as $item)
                                        <option value="{{ $item->id }}">{{ $item->code }}</option>
                                        @endforeach
                                    </select>
                                
                                </div> 
                                    <div class="form-group col-md-6 col-12">

                                    <label for="">@lang('Status')</label>
                                    <select name="status" class="form-control">
                                        <option value="">@lang('Select')</option>
                                        <option value="0">@lang('Inactive')</option>
                                        <option value="1">@lang('Active')</option>
                                    </select>
                                
                                </div> 
                                
                                <div class="form-group col-md-12">
                                    <label for="">@lang('Instructions for Withdraw')</label>
                                    <textarea name="withdraw_instruction" id="" rows="5" class="form-control summernote">{{old('withdraw_instruction')}}</textarea>
                                </div>

                                <div class="form-group col-md-12 text-right">
                                    {{-- <button type="submit" class="btn btn-primary btn-lg">@lang('Submit')</button> --}}
                                    <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>
                                </div>
                            </div>
                    
                    
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection