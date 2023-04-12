@extends('layouts.user')

@section('contents')
    @include('chatify.layouts.headLinks')

    <div class="container-xl">
        <div class="page-header d-print-none">
            <div class="row align-items-center mt-3">
                <div class="col">
                    <h2 class="page-title">
                        {{__('Chat Room')}}
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <div class="d-flex justify-content-end">
                            <label for="layer_id" class="col-form-label mr-3">{{__("Layer Name")}}</label>
                            <div class="form-group me-3 row">
                                <select  class="form-control me-2 shadow-none" id="layer_id" >
                                    <option value="0">@lang('Default')</option>
                                    @foreach ($layer_list as $value)
                                        <option value="{{$value->id}}" {{$value->id == $layerid ? 'selected' : ''}}>@lang($value->layer_id)</option>
                                    @endforeach
                                </select>
                            </div>

                          <a href="javascript:;" class="btn btn-primary d-sm-inline-block me-2" data-bs-toggle="modal" data-bs-target="#modal-apply">
                            <i class="fas fa-plus mr-3"></i>{{__('Create Layer')}}
                          </a>
                          <a href="javascript:;" class="btn btn-primary d-sm-inline-block me-2" data-bs-toggle="modal" data-bs-target="#modal-login">
                            <i class="fas fa-check mr-3"></i>{{__('Login Layer')}}
                          </a>
                          <a href="javascript:;" class="btn btn-primary d-sm-inline-block me-2 layer-logout" >
                            <i class="fas fa-reply mr-3"></i>{{__('Leave Layer')}}
                          </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="messenger">
                                {{-- ----------------------Users/Groups lists side---------------------- --}}
                                <div class="messenger-listView">
                                    {{-- Header and search bar --}}
                                    <div class="m-header">
                                        <nav>
                                            <a href="#"><i class="fas fa-inbox"></i> <span class="messenger-headTitle">{{__("Messages")}}</span>
                                            </a>
                                            {{-- header buttons --}}
                                            <nav class="m-header-right">
                                                <a href="#" class="listView-x"><i class="fas fa-times"></i></a>
                                            </nav>
                                        </nav>
                                        {{-- Search input --}}
                                        <input type="text" class="messenger-search" placeholder="Search"/>
                                        {{-- Tabs --}}
                                        <div class="messenger-listView-tabs">
                                            <a href="#" @if($type == 'user') class="active-tab"
                                               @endif data-view="users">
                                                <span class="far fa-user"></span> {{__("People")}}</a>
                                            {{-- <a href="#" @if($type == 'group') class="active-tab"
                                               @endif data-view="groups">
                                                <span class="fas fa-users"></span> Groups</a> --}}
                                        </div>
                                    </div>
                                    {{-- tabs and lists --}}
                                    <div class="m-body contacts-container">
                                        {{-- Lists [Users/Group] --}}
                                        {{-- ---------------- [ User Tab ] ---------------- --}}
                                        <div class="@if($type == 'user') show @endif messenger-tab users-tab app-scroll"
                                             data-view="users">

                                            {{-- Favorites --}}
                                            <div class="favorites-section">
                                                <p class="messenger-title">{{__("Favorites")}}</p>
                                                <div class="messenger-favorites app-scroll-thin"></div>
                                            </div>

                                            {{-- Saved Messages --}}
                                            {!! view('chatify.layouts.listItem', ['get' => 'saved']) !!}

                                            {{-- Contact --}}
                                            <div class="listOfContacts"
                                                 style="width: 100%;height: calc(100% - 200px);position: relative;">
                                            </div>

                                        </div>

                                        {{-- ---------------- [ Group Tab ] ---------------- --}}
                                        <div
                                            class="@if($type == 'group') show @endif messenger-tab groups-tab app-scroll"
                                            data-view="groups">
                                            {{-- items --}}
                                            <p style="text-align: center;color:grey;margin-top:30px">
                                            </p>
                                        </div>

                                        {{-- ---------------- [ Search Tab ] ---------------- --}}
                                        <div class="messenger-tab search-tab app-scroll" data-view="search">
                                            {{-- items --}}
                                            <p class="messenger-title">{{__("Search")}}</p>
                                            <div class="search-records">
                                                <p class="message-hint center-el"><span>{{__("Type to search..")}}</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ----------------------Messaging side---------------------- --}}
                                <div class="messenger-messagingView">
                                    {{-- header title [conversation name] amd buttons --}}
                                    <div class="m-header m-header-messaging">
                                        <nav
                                            class="chatify-d-flex chatify-justify-content-between chatify-align-items-center">
                                            {{-- header back button, avatar and user name --}}
                                            <div
                                                class="chatify-d-flex chatify-justify-content-between chatify-align-items-center">
                                                <a href="#" class="show-listView"><i class="fas fa-arrow-left"></i></a>
                                                <div class="avatar av-s header-avatar"
                                                     style="margin: 0px 10px; margin-top: -5px; margin-bottom: -5px;">
                                                </div>
                                                <a href="#" class="user-name"></a>
                                            </div>
                                            {{-- header buttons --}}
                                            <nav class="m-header-right">
                                                <a href="#" class="add-to-favorite"><i class="fas fa-star"></i></a>
                                                <a href="#" class="show-infoSide"><i class="fas fa-info-circle"></i></a>
                                            </nav>
                                        </nav>
                                    </div>

                                    {{-- Messaging area --}}
                                    <div class="m-body messages-container app-scroll" style="max-height: 600px;overflow-y: scroll;">
                                        {{-- Internet connection --}}
                                        <div class="internet-connection">
                                            <span class="ic-connected">{{__("Connected")}}</span>
                                            <span class="ic-connecting">{{__("Connecting...")}}</span>
                                            <span class="ic-noInternet">{{__("No internet access")}}</span>
                                        </div>
                                        <div class="messages">
                                            <p class="message-hint center-el"><span>{{__("Please select a chat to start messaging")}}</span>
                                            </p>
                                        </div>
                                        {{-- Typing indicator --}}
                                        <div class="typing-indicator">
                                            <div class="message-card typing">
                                                <p>
                                                    <span class="typing-dots">
                                                        <span class="dot dot-1"></span>
                                                        <span class="dot dot-2"></span>
                                                        <span class="dot dot-3"></span>
                                                    </span>
                                                </p>
                                            </div>
                                        </div>

                                    </div>
                                    {{-- Send Message Form --}}
                                    @include('chatify.layouts.sendForm')
                                </div>
                                {{-- ---------------------- Info side ---------------------- --}}
                                <div class="messenger-infoView app-scroll" style="display: none">
                                    {{-- nav actions --}}
                                    <nav>
                                        <a href="#"><i class="fas fa-times"></i></a>
                                    </nav>
                                    {!! view('chatify.layouts.info')->render() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-blur fade" id="modal-apply" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-title" style="border-radius: 10px 10px 0 0">
                <div class="ms-3">
                  <p>{{('Create Layer')}}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('user.layer.store') }}" method="post">
                @csrf
                <div class="modal-body">
                  <div class="form-group">
                    <label class="form-label required">{{__('Layer Id')}}</label>
                    <input name="layer_id" id="layer_id" class="form-control" autocomplete="off" placeholder="{{__('12345')}}" type="text" onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))" step="any" value="{{ old('layer_id') }}" minlength="5" maxlength="5" required>
                  </div>

                  <div class="form-group mt-3">
                    <label class="form-label required">{{__('PinCode')}}</label>
                    <input name="pincode" id="pincode" class="form-control" autocomplete="off" placeholder="{{__('12345')}}" type="text" onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))" step="any" value="{{ old('layer_id') }}" minlength="5" maxlength="5" required>
                  </div>

                  <input type="hidden" name="user_id" id="user_id" value="{{auth()->id()}}">
                </div>

                <div class="modal-footer">
                    <button type="submit" id="submit-btn" class="btn btn-primary">{{ __('Submit') }}</button>
                </div>
            </form>
          </div>
        </div>
      </div>


    <div class="modal modal-blur fade" id="modal-login" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-title" style="border-radius: 10px 10px 0 0">
                <div class="ms-3">
                  <p>{{('Login Layer')}}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('user.layer.login') }}" method="post">
                @csrf
                <div class="modal-body">
                  <div class="form-group">
                    <label class="form-label required">{{__('Layer Id')}}</label>
                    <input name="layer_id" id="layer_id" class="form-control" autocomplete="off" placeholder="{{__('12345')}}" type="text" onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))" step="any" value="{{ old('layer_id') }}" minlength="5" maxlength="5" required>
                  </div>

                  <div class="form-group mt-3">
                    <label class="form-label required">{{__('PinCode')}}</label>
                    <input name="pincode" id="pincode" class="form-control" autocomplete="off" placeholder="{{__('12345')}}" type="text" onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))" step="any" value="{{ old('layer_id') }}" minlength="5" maxlength="5" required>
                  </div>

                </div>
                <input type="hidden" name="user_id" id="user_id" value="{{auth()->id()}}">

                <div class="modal-footer">
                    <button type="submit" id="submit-btn" class="btn btn-primary">{{ __('Submit') }}</button>
                </div>
            </form>
          </div>
        </div>
    </div>
    <div class="modal modal-blur fade" id="modal-logout" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-title" style="border-radius: 10px 10px 0 0">
                <div class="ms-3">
                  <p>{{('Logut Layer')}}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                <p class="text-center mt-3">{{ __("Do you want to logout from this layer?") }}</p>
            </div>

            <form action="{{ route('user.layer.logout') }}" method="post">
                @csrf
                <input type="hidden" name="user_id" id="user_id" value="{{auth()->id()}}">
                <input type="hidden" name="layerid" id="layerid" value="{{$layerid}}">

                <div class="modal-footer">
                    <button type="submit" id="submit-btn" class="btn btn-primary">{{ __('Ok') }}</button>
                </div>
            </form>
          </div>
        </div>
    </div>

    @include('chatify.layouts.modals')
    @include('chatify.layouts.footerLinks')
@endsection

@push('js')
<script>
      'use strict';

      $('.layer-logout').on('click',function () {
            $('#layerid').val($('#layer_id').val());
          $('#modal-logout').modal('show')
      })


    </script>
@endpush
