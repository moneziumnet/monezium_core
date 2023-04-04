@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-fluid">
    <div class="page-header d-print-none">
        @include('user.settingtab')
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Active Your Modules')}}
          </h2>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title text-center">@lang('User Module List')</h5>
                        </div>
                        <div class="card-body">
                            @includeIf('includes.flash')
                            <form action="{{route('user.module.update')}}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group mb-3 mt-3 ms-3 col-md-4">
                                    <h4>{{__('User Modules')}}</h4>
                                </div>
                                <div class="row ms-3">

                                    @foreach(explode(" , ", $user->section) as $section )
                                    @if ($section)
                                    <div class="col-md-4 col-sm-6 mt-3">
                                        <div class="form-group">
                                            <div class="form-check form-switch">
                                                <input type="checkbox" name="section[]" value="{{$section}}" {{ $user->moduleCheck($section) ? 'checked' : '' }} class="form-check-input" id="{{$section}}">
                                                <label class="form-check-label" for="{{$section}}">{{__($section)}}</label>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    @endforeach
                                </div>

                                <div class="form-footer">
                                    <button type="submit" class="btn btn-primary w-100">{{__('Submit')}}</button>
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

<script>
    "use strict";
    function myFunction() {
        var copyText = document.getElementById("referralURL");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        toastr.options =
        {
            "closeButton" : true,
            "progressBar" : true
        }
        toastr.success("Copied.");
    }

    $(document).ready(function() {
    $('#payment_fa_yn').click(function() {
        var checked = $(this).prop('checked');
        $('#check_box').find('input:checkbox').prop('checked', checked);
    });
    })
</script>

<script src="{{asset('assets/user/js/sweetalert2@9.js')}}"></script>

    @if($errors->any())
        @foreach ($errors->all() as $error)
            <script>
                const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                onOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
            })
                Toast.fire({
                icon: 'error',
                title: '{{ $error }}'
                })
            </script>
        @endforeach
    @endif


    @if(Session::has('success'))
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            onOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
    })
    Toast.fire({
        icon: 'success',
        title: '{{Session::get('success')}}'
    })
  </script>
@endif

@endpush
