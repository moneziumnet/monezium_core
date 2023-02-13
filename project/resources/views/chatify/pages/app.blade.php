@extends('layouts.user')

@section('contents')
    @include('chatify.layouts.headLinks')

    <div class="container-xl">
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        {{__('Chat Room')}}
                    </h2>
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
                                            <a href="#"><i class="fas fa-inbox"></i> <span class="messenger-headTitle">MESSAGES</span>
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
                                                <span class="far fa-user"></span> People</a>
                                            <a href="#" @if($type == 'group') class="active-tab"
                                               @endif data-view="groups">
                                                <span class="fas fa-users"></span> Groups</a>
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
                                                <p class="messenger-title">Favorites</p>
                                                <div class="messenger-favorites app-scroll-thin"></div>
                                            </div>

                                            {{-- Saved Messages --}}
                                            {!! view('chatify.layouts.listItem', ['get' => 'saved']) !!}

                                            {{-- Contact --}}
                                            <div class="listOfContacts"
                                                 style="width: 100%;height: calc(100% - 200px);position: relative;"></div>

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
                                            <p class="messenger-title">Search</p>
                                            <div class="search-records">
                                                <p class="message-hint center-el"><span>Type to search..</span></p>
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
                                    <div class="m-body messages-container app-scroll">
                                        {{-- Internet connection --}}
                                        <div class="internet-connection">
                                            <span class="ic-connected">Connected</span>
                                            <span class="ic-connecting">Connecting...</span>
                                            <span class="ic-noInternet">No internet access</span>
                                        </div>
                                        <div class="messages">
                                            <p class="message-hint center-el"><span>Please select a chat to start messaging</span>
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

    @include('chatify.layouts.modals')
    @include('chatify.layouts.footerLinks')
@endsection