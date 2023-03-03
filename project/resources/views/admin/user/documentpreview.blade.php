@extends('layouts.admin')
@section('styles')
<link
rel="stylesheet"
href="https://cdn01.boxcdn.net/platform/elements/16.0.0/en-US/explorer.css"
/>
@endsection
@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ $data->name }}</h5>
    <ol class="breadcrumb py-0 m-0">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.user.index') }}">{{ __('User List') }}</a></li>
    </ol>
  </div>
</div>


<div class="row mt-3">
    <div class="col-lg-12">
        @include('includes.admin.form-success')
        <div class="card tab-card mb-4">
            @include('admin.user.profiletab')
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
                    <div class='p-3'>
                        <p class="h4">{{__('Folder Preview')}}</p>
                    </div>

                    <div class="card-body">
                        <div class="card mb-4">
                            <div class="container" style="height:800px; display: flex"  id="box_container">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--Row-->


@endsection
@section('scripts')
<script src="https://cdn01.boxcdn.net/platform/elements/16.0.0/en-US/explorer.js"></script>
<script src="https://cdn.polyfill.io/v2/polyfill.min.js?features=es6,Intl"></script>
<script type="text/javascript">
  "use strict";
    var contentExplorer = new Box.ContentExplorer();
    var folderId = "{{$folder_id}}";
    var accessToken = "{{$access_token}}";
    // Show the content explorer
    contentExplorer.show(folderId, accessToken, {
        container: ".container",
        logoUrl:"{{ asset('assets/images/'.$gs->logo) }}"
    });





</script>
@endsection
