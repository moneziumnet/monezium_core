<title>{{ config('chatify.name') }}</title>

{{-- Meta tags --}}
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="id" content="{{ $id }}">
<meta name="type" content="{{ $type }}">
<meta name="messenger-color" content="{{ $messengerColor }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="url" content="{{ url('').'/'.config('chatify.routes.prefix') }}" data-user="{{ Auth::user()->id }}">

{{-- scripts --}}
<script
  src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('assets/chatify/js/font.awesome.min.js') }}"></script>
<script src="{{ asset('assets/chatify/js/autosize.js') }}"></script>
<script src="{{ asset('assets/chatify/js/app.js') }}"></script>
<script src='https://unpkg.com/nprogress@0.2.0/nprogress.js'></script>

{{-- styles --}}
<link rel='stylesheet' href='https://unpkg.com/nprogress@0.2.0/nprogress.css'/>
<link href="{{ asset('assets/chatify/css/style.css') }}" rel="stylesheet" />
{{--<link href="{{ asset('assets/chatify/css/'.$dark_mode.'.mode.css') }}" rel="stylesheet" />--}}
<link href="{{ asset('assets/chatify/css/app.css') }}" rel="stylesheet" />

{{-- Messenger Color Style--}}
@include('chatify.layouts.messengerColor')
