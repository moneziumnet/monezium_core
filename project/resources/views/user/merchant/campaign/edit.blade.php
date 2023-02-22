@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      @include('user.merchant.tab')
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Edit Campaign')}}
          </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">

              <a href="{{ route('user.merchant.campaign.index') }}" class="btn btn-primary d-sm-inline-block">
                  <i class="fas fa-backward me-1"></i> {{__('Campaign List')}}
              </a>
            </div>
          </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card p-5">
                    <form action="{{ route('user.merchant.campaign.update', $data->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group mt-2 mb-3">
                            <label class="form-label required">{{__('Campaign Title')}}</label>
                            <input name="title" id="title" class="form-control shadow-none" placeholder="{{__('Title')}}" type="text" pattern="[^()/><\][\\;&$@!|]+" value="{{ $data->title }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label ">{{__('Select Category')}}</label>
                            <select name="cat_id" id="cat_id" class="form-control" >
                                <option value="">{{__('Select')}}</option>
                                @foreach($categories as $category)
                                <option value="{{$category->id}}"  {{$category->id == $data->cat_id ? "selected" : ""}}>{{$category->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label required">{{__('Select Currency')}}</label>
                            <select name="currency_id" id="currency_id" class="form-control" required>
                                <option value="">Select</option>
                                @foreach($currencies as $currency)
                                <option value="{{$currency->id}}"  {{$currency->id == $data->currency_id ? "selected" : ""}}>{{$currency->code}}</option>
                                @endforeach
                            </select>

                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label required">{{__('Goal')}}</label>
                            <input name="goal" id="goal" class="form-control shadow-none" placeholder="{{__('Goal')}}" type="number" step="any" value="{{ $data->goal}}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label required">{{__('Deadline')}}</label>
                            <input name="deadline" id="deadline" class="form-control shadow-none" placeholder="{{__('Deadline')}}" type="date" value="{{ $data->deadline}}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">{{__('Description')}}</label>
                            <input name="description" id="description" class="form-control shadow-none" placeholder="{{__('Description')}}" type="text" value="{{ $data->description }}">
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label required">{{__('Choose Media')}}</label>
                            <input name="logo" id="logo" class="form-control" type="file" accept=".gif,.png,.jpg" >
                        </div>

                        <input name="user_id" type="hidden" class="form-control" value="{{auth()->id()}}">


                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary submit-btn w-100" >{{__('Update')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

