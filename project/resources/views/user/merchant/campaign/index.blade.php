@extends('layouts.user')

@push('css')

@endpush



@section('contents')
<div class="container-xl">
  <div class="page-header d-print-none">
    @include('user.merchant.tab')
    <div class="row align-items-center">
      <div class="col">
        <div class="page-pretitle">
          {{__('Overview')}}
        </div>
        <h2 class="page-title">
          {{__('Campaign List')}}
        </h2>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">

            <a class="btn btn-primary d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#modal-category">
              <i class="fas fa-plus me-1"></i> {{__('Create Category')}}
            </a>
          <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-campaign">
            <i class="fas fa-plus me-1"></i>
            {{__('Create Campaign')}}
          </a>
        </div>
      </div>
    </div>
  </div>
</div>


<div class="page-body">
  <div class="container-xl mt-3 mb-3">
      <div class="row row-cards">
          <div class="row justify-content " style="max-height: 1600px;overflow-y: scroll;">
                  @if (count($campaigns) == 0)
                      <h3 class="text-center py-5">{{__('No Campaign Data Found')}}</h3>
                  @else
                  @foreach($campaigns as $key=>$val)
                    <div class="col-lg-3 mb-3">
                        <div class="card">
                            <img class="back-preview-image"
                                src="{{asset('assets/images')}}/{{$val->logo}}"
                            alt="Campaign Logo">
                            <!-- Card body -->
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-8">
                                    <p class="text-sm  mb-2"><a class="btn-icon-clipboard copy" data-clipboard-text="{{route('user.merchant.campaign.link', $val->ref_id)}}" title="Copy">{{__('COPY LINK')}} <i class="fas fa-link text-xs"></i></a></p>
                                    </div>
                                    <div class="col-4 text-end">
                                    <a class="mr-0" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-chevron-circle-down"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-left">
                                        <a class="dropdown-item" href="{{route('user.merchant.campaign.status', $val->id)}}"><i class="fas fa-ban me-2"></i>{{$val->status == 1 ? __('Disable') : __('Enable')}}</a>
                                        <a class="dropdown-item" href="{{route('user.merchant.campaign.edit', $val->id)}}"><i class="fas fa-pencil-alt me-2"></i>{{__('Edit')}}</a>
                                        <a class="dropdown-item" href="{{route('user.merchant.campaign.donation_list', ['id' => $val->id])}}"><i class="fas fa-sync me-2"></i>{{__('Donation List')}}</a>
                                        <a class="dropdown-item send-email" data-url="{{route('user.merchant.campaign.link', $val->ref_id)}}" href="#"><i class="fas fa-paper-plane me-2"></i>{{__('Send Email')}}</a>
                                        <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#delete{{$val->id}}" href="#"><i class="fas fa-trash-alt  me-2"></i>{{__('Delete')}}</a>
                                        <a class="dropdown-item download-qrcode" data-data="{{generateQR(route('user.merchant.campaign.link', $val->ref_id))}}" href="#"><i class="fas fa-qrcode  me-2"></i>{{__('QR code')}}</a>
                                    </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-12">
                                    <h5 class="h4 mb-2 font-weight-bolder">{{__('Campaign Title: ')}}{{$val->title}}</h5>
                                    <h5 class="mb-1">{{__('Category: ')}} {{$val->category->name}}</h5>
                                    <h5 class="mb-1">{{__('Organizer: ')}} {{$val->user->company_name ?? $val->user->name}}</h5>
                                    <h5 class="mb-1">{{__('Goal: ')}} {{$val->currency->symbol}}{{$val->goal}}</h5>
                                    @php
                                        $total = DB::table('campaign_donations')->where('campaign_id', $val->id)->where('status', 1)->sum('amount');
                                    @endphp
                                    <h5 class="mb-1">{{__('FundsRaised: ')}} {{$val->currency->symbol}}{{$total}}</h5>
                                    <h5 class="mb-1">{{__('Deadline: ')}} {{$val->deadline}}</h5>
                                    <h5 class="mb-3">{{__('Created Date:')}} {{$val->created_at}}</h5>
                                    <h6 class="mb-3">{{__('Description:')}} {{$val->description}}</h6>
                                    @if($val->status==1)
                                        <span class="badge badge-pill bg-success"><i class="fas fa-check"></i> {{__('Active')}}</span>
                                    @else
                                        <span class="badge badge-pill bg-danger"><i class="fas fa-ban"></i> {{__('Disabled')}}</span>
                                    @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade modal-blur" id="delete{{$val->id}}" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    <div class="modal-status bg-success"></div>

                                    <div class="modal-body text-center py-4">
                                        <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                                        <h3>{{__('Confirm Delete')}}</h3>
                                        <p class="text-center mt-3">{{ __("Do you want to delete this Campaign?") }}</p>
                                      </div>

                                      <div class="modal-footer">
                                        <a href="javascript:;" class="btn btn-secondary" data-bs-dismiss="modal">{{ __("Cancel") }}</a>
                                        <a href="{{route('user.merchant.campaign.delete', $val->id)}}" class="btn shadow-none btn-primary" >@lang('Proceed')</a>
                                      </div>
                                </div>
                            </div>
                        </div>
                    </div>
                  @endforeach
                  @endif
              </div>
      </div>
  </div>
</div>
<div class="modal fade modal-blur" id="qrcode" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-status bg-success"></div>
            <div class="modal-body text-center py-4">
                <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                <h3>{{__('QR code')}}</h3>
                <form action="{{route('user.merchant.download.qr')}}" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                        <div >
                            <img src="" class="" id="qr_code" alt="">
                        </div>
                        <input type="hidden" id="email" name="email" value="">
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary btn-lg">@lang('Download')</button>
                        </div>
                </form>
              </div>
        </div>
    </div>
</div>
<div class="modal modal-blur fade" id="modal-send-email" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="modal-status bg-primary"></div>
        <div class="modal-body text-center py-4">
            <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
            <h3>{{__('Send E-mail')}}</h3>
            <div class="row text-start">
                <div class="col">
                    <form action="{{ route('user.merchant.campaign.send_email') }}" method="post">
                        @csrf
                        <div class="row">
                            <div class="form-group mt-2">
                                <label class="form-label required">{{__('Email Address')}}</label>
                                <input name="email" id="email" class="form-control shadow-none" placeholder="{{__('test@gmail.com')}}" type="email" required>
                            </div>
                        </div>
                        <input name="link" id="link" type="hidden" required>
                        <div class="row mt-3">
                            <div class="col">
                                <button type="submit" class="btn btn-primary w-100 confirm">
                                {{__('Send')}}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
  </div>

<div class="modal modal-blur fade" id="modal-category" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="modal-status bg-primary"></div>
        <div class="modal-body text-center py-4">
            <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
            <h3>{{__('Add New Category')}}</h3>
            <div class="row text-start">
                <div class="col">
                    <form action="{{route('user.merchant.campaign.category.create')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class = "row">
                            <div class="form-group mt-2">
                                <label class="form-label required">{{__('Category Name')}}</label>
                                <input name="name" id="name" class="form-control shadow-none" placeholder="{{__('Name')}}" type="text" pattern="[^()/><\][;!|]+" value="{{ old('name') }}" required>
                            </div>
                        </div>
                        <input type="hidden" name="user_id" value="{{auth()->id()}}">
                        <div class="row mt-3">
                            <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">
                                {{__('Cancel')}}
                                </a>
                            </div>
                            <div class="col">
                                <button type="submit" class="btn btn-primary w-100 confirm">
                                {{__('Confirm')}}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<div class="modal modal-blur fade" id="modal-campaign" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="modal-status bg-primary"></div>
        <div class="modal-body text-center py-4">
            <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
            <h3>{{__('Add New Campaign')}}</h3>
            <div class="row text-start">
                <div class="col">
                    <form action="{{route('user.merchant.campaign.store')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class = "row">
                            <div class="form-group mt-2 mb-3">
                                <label class="form-label required">{{__('Title')}}</label>
                                <input name="title" id="title" class="form-control shadow-none" placeholder="{{__('Title')}}" type="text" pattern="[^()/><\][;!|]+" value="{{ old('title') }}" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label ">{{__('Select Category')}}</label>
                                <select name="cat_id" id="cat_id" class="form-control" >
                                    <option value="">{{__('Select')}}</option>
                                    @foreach($categories as $category)
                                    <option value="{{$category->id}}">{{$category->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label required">{{__('Select Currency')}}</label>
                                <select name="currency_id" id="currency_id" class="form-control" required>
                                    <option value="">{{__('Select')}}</option>
                                    @foreach($currencies as $currency)
                                    <option value="{{$currency->id}}">{{$currency->code}}</option>
                                    @endforeach
                                </select>

                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label required">{{__('Goal')}}</label>
                                <input name="goal" id="goal" class="form-control shadow-none" placeholder="{{__('Goal')}}" type="number" step="any" value="{{ old('goal') }}" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label required">{{__('Deadline')}}</label>
                                <input name="deadline" id="deadline" class="form-control shadow-none" placeholder="{{__('Deadline')}}" type="date" value="{{ old('deadline') }}" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label required">{{__('Description')}}</label>
                                <input name="description" id="description" class="form-control shadow-none" placeholder="{{__('Description')}}" type="text" value="{{ old('description') }}" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label required">{{__('Choose Media')}}</label>
                                <input name="logo" id="logo" class="form-control" type="file" accept=".gif,.png,.jpg" required>
                            </div>

                        </div>
                        <input type="hidden" name="user_id" value="{{auth()->id()}}">
                        <div class="row mt-3">
                            <div class="col">
                                <button type="submit" class="btn btn-primary w-100 confirm">
                                {{__('Confirm')}}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{asset('assets/user/js/clipboard.min.js')}}"></script>
<script>
        'use strict';
        var clipboard = new ClipboardJS('.copy');
        clipboard.on('success', function(e) {
            toastr.options =
            {
            "closeButton" : true,
            "progressBar" : true
            }
            toastr.success("Link URL Copied.");
        });
        $('.send-email').on('click', function() {
            $('#modal-send-email').modal('show');
            $('#link').val($(this).data('url'));
        });
        $('.download-qrcode').on('click', function() {
            $('#qrcode').modal('show');
            $('#email').val($(this).data('data'));
            $('#qr_code').attr('src' , $(this).data('data'));
        })
    </script>
@endpush

