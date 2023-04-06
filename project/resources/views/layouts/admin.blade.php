<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="{{ $gs->title }}">
  <meta name="author" content="{{url('/')}}">
  <link href="{{ asset('assets/images/'.$gs->favicon) }}" rel="icon">
  <title>{{ $gs->title }}</title>
  <link href="{{ asset('assets/admin/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
  <link href="{{ asset('assets/admin/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
  <link href="{{ asset('assets/admin/css/toastr.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/admin/css/select2.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/admin/css/tagify.css') }}" rel="stylesheet">
  <link rel="stylesheet" href="{{asset('assets/admin/css/summernote.css')}}">
  <link href="{{ asset('assets/admin/css/bootstrap-colorpicker.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/admin/css/bootstrap-iconpicker.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/admin/css/color-picker.css') }}" rel="stylesheet">
  <link rel="stylesheet" href="{{asset('assets/front/css/toastr.min.css')}}">
  <link href="{{asset('assets/admin/css/plugin.css')}}" rel="stylesheet" />
  <link href="{{ asset('assets/admin/css/ruang-admin.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/admin/css/custom.css') }}" rel="stylesheet">
    <style>
        .notification-ui a:after {
    display: none;
}

.notification-ui_icon {
    position: relative;
}

.notification-ui_icon .unread-notification {
    display: inline-block;
    height: 7px;
    width: 7px;
    border-radius: 7px;
    background-color: #66BB6A;
    position: absolute;
    top: 24px;
    left: 12px;
}

@media (min-width: 900px) {
    .notification-ui_icon .unread-notification {
        left: 20px;
    }
}

.notification-ui_dd {
    padding: 0;
    border-radius: 10px;
    -webkit-box-shadow: 0 5px 20px -3px rgba(0, 0, 0, 0.16);
    box-shadow: 0 5px 20px -3px rgba(0, 0, 0, 0.16);
    border: 0;
    max-width: 400px;
}

@media (min-width: 900px) {
    .notification-ui_dd {
        min-width: 400px;
        position: absolute;
        left: -192px;
        top: 70px;
    }
}

.notification-ui_dd:after {
    content: "";
    position: absolute;
    top: -30px;
    left: calc(50% - 7px);
    border-top: 15px solid transparent;
    border-right: 15px solid transparent;
    border-bottom: 15px solid #fff;
    border-left: 15px solid transparent;
}

.notification-ui_dd .notification-ui_dd-header {
    border-bottom: 1px solid #ddd;
    padding: 15px;
}

.notification-ui_dd .notification-ui_dd-header h3 {
    margin-bottom: 0;
}

.notification-ui_dd .notification-ui_dd-content {
    max-height: 500px;
    overflow: auto;
}

.notification-list {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-pack: justify;
    -ms-flex-pack: justify;
    justify-content: space-between;
    padding: 20px 0;
    margin: 0 25px;
    border-bottom: 1px solid #ddd;
}

.notification-list--unread {
    position: relative;
}

.notification-list--unread:before {
    content: "";
    position: absolute;
    top: 0;
    left: -25px;
    height: calc(100% + 1px);
    border-left: 2px solid #29B6F6;
}

.notification-list .notification-list_img img {
    height: 48px;
    width: 48px;
    border-radius: 50px;
    margin-right: 20px;
}

.notification-list .notification-list_detail p {
    margin-bottom: 5px;
    line-height: 1.2;
}

.notification-list .notification-list_feature-img img {
    height: 48px;
    width: 48px;
    border-radius: 5px;
    margin-left: 20px;
}
    </style>
  @yield('styles')

</head>

<body id="page-top">
    @if ($gs->is_admin_loader==1)
        <div class="Loader" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center #FFF;"></div>
    @endif
  <div id="wrapper">
    <!-- Sidebar -->
    <ul class="navbar-nav sidebar sidebar-light accordion" id="accordionSidebar">
      <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('admin.dashboard') }}">
        <div class="sidebar-brand-icon">
          <img src="{{ asset('assets/images/'.$gs->logo) }}">
        </div>
      </a>

      <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.dashboard') }}">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>{{ __('Dashboard') }}</span></a>
      </li>

      @if(Auth::guard('admin')->user()->IsSuper())
        @include('includes.admin.roles.super')
      @elseif(Auth::guard('admin')->user()->role === 'admin')
        @include('includes.admin.roles.normal')
      @elseif(Auth::guard('admin')->user()->role === 'staff')
        @include('includes.admin.roles.staff')
      @endif

    </ul>
    <!-- Sidebar -->
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <!-- TopBar -->
        <nav class="navbar navbar-expand navbar-light bg-navbar topbar mb-4 static-top">
          <button id="sidebarToggleTop" class="btn btn-link rounded-circle mr-3">
            <i class="fa fa-bars"></i>
          </button>
          <ul class="navbar-nav ml-auto">
            @if(Auth::guard('admin')->user()->IsSuper() == false)
                @php
                    $notificationlist = App\Models\ActionNotification::where('status', 0)->orderBy('status','asc')->orderBy('created_at','desc')->limit(3)->get();
                @endphp
                <li class="nav-item dropdown notification-ui">
                    <a class="nav-link dropdown-toggle notification-ui_icon" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        <i class="fa fa-bell"></i>
                        @if(count($notificationlist) > 0)
                            <span class="unread-notification"></span>
                        @endif
                    </a>
                    <div class="dropdown-menu notification-ui_dd" aria-labelledby="navbarDropdown">
                        <div class="notification-ui_dd-header">
                            <h3 class="text-center">Notification</h3>
                        </div>
                        <div class="notification-ui_dd-content">
                            @if(count($notificationlist) == 0)
                                <div class="notification-list_detail text-center mt-3">
                                    <p>No Notification</p>
                                </div>
                            @endif
                            @foreach ($notificationlist as $item)
                                <div class="notification-list notification-list--unread">
                                    <div class="notification-list_detail">
                                        <p><b>{{$item->user->company_name ?? $item->user->name}}, </b> {{$item->description}}</p>
                                        <p><small>{{time_elapsed_string($item->created_at)}}</small></p>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                        <div class="notification-ui_dd-footer p-3">
                            <a href={{route('admin.actionnotification.index')}} class="btn btn-success btn-block">View All</a>
                        </div>
                    </div>
                </li>
            @endif
            <li class="nav-item dropdown no-arrow mx-1">
              <a class="nav-link pr-0" target="_blank" href="{{url('/')}}">
                  <i class="fas fa-globe fa-fw"></i>
              </a>
            </li>


            <div class="topbar-divider d-none d-sm-block"></div>
            <li class="nav-item dropdown no-arrow">
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <img class="img-profile rounded-circle" src="{{ Auth::guard('admin')->user()->photo ? asset('assets/images/'.Auth::guard('admin')->user()->photo ):asset('assets/images/noimage.png') }}" style="max-width: 60px">
                <span class="ml-2 d-none d-lg-inline text-white small">{{ Auth::guard('admin')->user()->name }}</span>
              </a>
              <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">

                <a class="dropdown-item" href="{{ route('admin.profile') }}">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    {{ __('Profile') }}
                  </a>
                <a class="dropdown-item" href="{{ route('admin.password') }}">
                  <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                  {{ __('Change Password') }}
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('admin.logout') }}">
                  <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                  {{ __('Logout') }}
                </a>
              </div>
            </li>
          </ul>
        </nav>
        <!-- Topbar -->

        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">

            @yield('content')

        </div>
        <!---Container Fluid-->
      </div>

    </div>
  </div>

  <!-- Scroll to top -->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>


  <script type="text/javascript">
    'use strict';
    var form_error   = "{{ __('Please fill all the required fields') }}";
    var mainurl = "{{ url('/') }}";
    var admin_loader = {{ $gs->is_admin_loader }};

  </script>

  <script src="{{ asset('assets/admin/vendor/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/admin/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/admin/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
  <script src="{{asset('assets/admin/js/plugin.js')}}"></script>
  <script src="{{ asset('assets/admin/js/chart.min.js') }}"></script>
  <script src="{{ asset('assets/admin/js/toastr.js') }}"></script>
  <script src="{{ asset('assets/admin/js/bootstrap-colorpicker.js') }}"></script>
  <script src="{{ asset('assets/admin/js/bootstrap-iconpicker.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/admin/js/colorpicker.js') }}"></script>
  <script src="{{ asset('assets/admin/js/select2.min.js') }}"></script>
  <script src="{{ asset('assets/admin/js/tagify.js') }}"></script>
  <script src="{{asset('assets/admin/js/summernote.js')}}"></script>
  <script src="{{ asset('assets/admin/js/sortable.js') }}"></script>
  <script src="{{ asset('assets/admin/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/admin/js/bootstrap-datepicker.min.js') }}"></script>
  <script src="{{asset('assets/front/js/toastr.min.js')}}"></script>
  <script src="{{ asset('assets/admin/js/ruang-admin.js') }}"></script>
  <script src="{{asset('assets/admin/js/apexcharts.min.js')}}"></script>
  <script>
    'use strict';
    var submit_flag = false;
      $(document).on('submit', '#iban-submit', function(e){
            var ibanapi = '{{ $gs->ibanapi ?? '' }}';
            var url   = 'https://api.ibanapi.com/v1/validate/' +  $('.iban-input').val() + '?api_key=' + ibanapi;
            if(!submit_flag) {
                if(document.getElementById('iban-submit').checkValidity()) {
                    e.preventDefault();
                    $.ajax({
                        url: url,
                        dataType: 'jsonp',
                        success: function (data) {
                            if(data.result != 200) {
                                $('.iban-validation').removeClass('text-success')
                                $('.iban-validation').addClass('text-danger').text( data.message);
                            }
                            else if (data.result == 200) {
                                $('.iban-validation').removeClass('text-danger')
                                $('.iban-validation').addClass('text-success').text(data.message);
                                submit_flag=true;
                                if((data.data).hasOwnProperty('bank')) {
                                    $('#bank_name').val(data.data.bank.bank_name)
                                    $('#bank_address').val(data.data.bank.address)
                                    $('#swift').val(data.data.bank.bic)
                                }
                                $('#iban-submit').submit();
                            }
                        },
                        error: function(httpObj, textStatus) {
                            $('.iban-validation').removeClass('text-success')
                            $('.iban-validation').addClass('text-danger').text('Iban Validation Api is expired or Api is not correct, Please contact Support Team');
                        }
                    });
                }
            }
        })
        $('.iban-input').on('keypress', function(e){
            var key = e.keyCode;
            if (key === 32) {
            e.preventDefault();
            }
        })
    function showDiv(select) {
            var company_input_list = $('.company-input');
            var private_input_list = $('.private-input');
            if (select.value == 1) {
                document.getElementById('corporate_div').style.display = "block";
                document.getElementById('private_div').style.display = "none";
                for (let i = 0; i < company_input_list.length; i++) {
                    const item = company_input_list[i];
                    item.required = true;
                }
                for (let i = 0; i < private_input_list.length; i++) {
                    const item = private_input_list[i];
                    item.required = false;
                }
            } else {
                document.getElementById('corporate_div').style.display = "none";
                document.getElementById('private_div').style.display = "block";
                for (let i = 0; i < company_input_list.length; i++) {
                    const item = company_input_list[i];
                    item.required = false;
                }
                for (let i = 0; i < private_input_list.length; i++) {
                    const item = private_input_list[i];
                    item.required = true;
                }
            }
        }

    @if(Session::has('message'))
      toastr.options =
      {
        "closeButton" : true,
        "progressBar" : true
      }
      toastr.success("{{ session('message') }}");
    @endif

    @if(Session::has('error'))
      toastr.options =
      {
        "closeButton" : true,
        "progressBar" : true
      }
      toastr.error("{{ session('error') }}");
    @endif

    @if(Session::has('info'))
      toastr.options =
      {
        "closeButton" : true,
        "progressBar" : true
      }
      toastr.info("{{ session('info') }}");
    @endif

    @if(Session::has('warning'))
      toastr.options =
      {
        "closeButton" : true,
        "progressBar" : true
      }
      toastr.warning("{{ session('warning') }}");
    @endif
  </script>

  @yield('scripts')

</body>

</html>
