@extends('layouts.admin')
@section('styles')
<link
rel="stylesheet"
href="https://cdn01.boxcdn.net/platform/elements/16.0.0/en-US/picker.css"
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
    <div class="card mt-1 tab-card">
      @include('admin.user.profiletab')

      <div class="tab-content" id="myTabContent">
        @php
        $currency = defaultCurr();
        @endphp
        @include('includes.admin.form-success')
        <div class="tab-pane fade show p-1 active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
          <div class="card-body">
            <form id="request-form" action="{{ route('admin-user.createfile',$data->id) }}" method="POST" enctype="multipart/form-data">
              @include('includes.admin.form-flash')
              {{ csrf_field() }}
              <div class="form-group">
                <label for="inp-name">{{ __('Name') }}</label>
                <input name="document_name" class="form-control" autocomplete="off" placeholder="{{__('Name')}}" type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" required>
              </div>
              <div class="document-select-options">
                <div class="mb-2">
                    <label class="form-selectgroup-item">
                        <input type="radio" name="docu_type" value="type_file" id="type_file"
                            class="form-selectgroup-input select_method" checked>
                        <span class="form-selectgroup-label">
                            <i class="fas fa-file me-2"></i>
                            @lang('Select File')</span>
                    </label>
                </div>
                <div class="mb-2">
                    <label class="form-selectgroup-item">
                        <input type="radio" name="docu_type" value="type_folder" id="type_folder"
                            class="form-selectgroup-input select_method">
                        <span class="form-selectgroup-label">
                            <i class="fas fa-folder me-2"></i>
                            @lang('Select Folder')</span>
                    </label>
                </div>
              </div>
              <div class="form-group">
                <label for="full-name">{{ __('Choose') }}</label>
                <input type="text" class="form-control" id="document_file" placeholder="{{__('Please choose file or folder in Box.')}}" name="document_file" required>
              </div>
              <input type="hidden" id="file_id" name="file_id" >

              <div class="container" style="height:600px; display: none" id="box_container">
              </div>
              <div class="form-group">
                <button type="submit" class="btn btn-primary w-100">{{__('Submit')}}</button>
              </div>
            </form>
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
  <script src="https://cdn01.boxcdn.net/platform/elements/16.0.0/en-US/picker.js"></script>
  <script src="https://cdn.polyfill.io/v2/polyfill.min.js?features=es6,Intl"></script>
  <script type="text/javascript">
      "use strict";

    $('#document_file').on('click', function() {
        document.getElementById('box_container').style.display = "flex";

    })
$('.select_method').on('click', function () {
    if ($(this).attr('id') == 'type_file') {
        var folderId = "0";
        var accessToken = "{{$access_token}}";
        var filePicker = new Box.FilePicker();



        // Attach event listener for when the choose button is pressed
        filePicker.addListener('choose', function(items) {
            // do something with the items array
            console.log(JSON.stringify(items, null, 2));
            $('#document_file').val(items[0]['name'])
            $('#file_id').val(items[0]['id'])

        });

        // Attach event listener for when the cancel button is pressed
        filePicker.addListener('cancel', function() {
            // do something
        });

        filePicker.show(folderId, accessToken, {
            container: ".container",
            chooseButtonLabel: 'Select',
            logoUrl:"{{ asset('assets/images/'.$gs->logo) }}",
            maxSelectable:1,
            canSetShareAccess:false
        });

    }
    else if($(this).attr('id') == 'type_folder') {
        var folderId = "0";
      var accessToken = "{{$access_token}}";
      var filePicker = new Box.FolderPicker();



      // Attach event listener for when the choose button is pressed
      filePicker.addListener('choose', function(items) {
          // do something with the items array
          console.log(JSON.stringify(items, null, 2));
          $('#document_file').val(items[0]['name'])
          $('#file_id').val(items[0]['id'])

      });

      // Attach event listener for when the cancel button is pressed
      filePicker.addListener('cancel', function() {
          // do something
      });

      filePicker.show(folderId, accessToken, {
          container: ".container",
          chooseButtonLabel: 'Select',
          logoUrl:"{{ asset('assets/images/'.$gs->logo) }}",
          maxSelectable:1,
          canSetShareAccess:false
      });

    }
})
      var folderId = "0";
      var accessToken = "{{$access_token}}";
      var filePicker = new Box.FilePicker();



      // Attach event listener for when the choose button is pressed
      filePicker.addListener('choose', function(items) {
          // do something with the items array
          console.log(JSON.stringify(items, null, 2));
          $('#document_file').val(items[0]['name'])
          $('#file_id').val(items[0]['id'])

      });

      // Attach event listener for when the cancel button is pressed
      filePicker.addListener('cancel', function() {
          // do something
      });

      filePicker.show(folderId, accessToken, {
          container: ".container",
          chooseButtonLabel: 'Select',
          logoUrl:"{{ asset('assets/images/'.$gs->logo) }}",
          maxSelectable:1,
          canSetShareAccess:false
      });


  </script>
@endsection
