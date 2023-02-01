<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <!-- CSS files -->
    {{-- <link rel="shortcut icon" href="{{getPhoto($gs->favicon)}}"> --}}
    {{-- <link rel="stylesheet" href="{{public_path('assets/admin/')}}css/font-awsome.min.css"> --}}

    <link href="{{asset('assets/user/')}}/css/tabler.min.css" rel="stylesheet"/>
    <link href="{{asset('assets/user/')}}/css/tabler-flags.min.css" rel="stylesheet"/>
    <link href="{{asset('assets/user/')}}/css/tabler-payments.min.css" rel="stylesheet"/>
    <link href="{{asset('assets/user/')}}/css/tabler-vendors.min.css" rel="stylesheet"/>
    <link href="{{asset('assets/user/')}}/css/custom.css" rel="stylesheet"/>
    <link href="{{asset('assets/user/')}}/css/demo.min.css" rel="stylesheet"/>


    <link rel="stylesheet" type="text/css" href="{{asset('assets/user/')}}/css/bootstrap-4.3.1.css">
    <script type="text/javascript" src="{{asset('assets/user/')}}/js/jquery-1.12.4.min.js"></script>
    <link type="text/css" href="{{asset('assets/user/')}}/css/jquery-ui.css" rel="stylesheet">
    <script type="text/javascript" src="{{asset('assets/user/')}}/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="{{asset('assets/user/')}}/js/jquery.signature.js"></script>
    <link rel="stylesheet" type="text/css" href="{{asset('assets/user/')}}/css/jquery.signature.css">
    <style>

        #document-des {
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        .document-logo {
            height: 8rem;
            width: auto;
        }
    </style>
</head>
<body>
    <div>
        <div class="row text-center">
            <div class="mt-3">
                    <img src="{{asset('assets/images/'.$gs->logo)}}" class="document-logo" style="width: auto;">
            </div>
            <div class="mt-3">
                <h3>
                    {{$gs->disqus}}<br/>
                </h3>
            </div>
            <div class="mt-3">
                <h5 class="ms-1">{{$user->company_name ?? $user->name}}</h2>
            </div>
            <div class="mt-2">
                <h5 class="ms-1">
                    {{$user->company_address ?? $user->address}}<br/>
                    {{$user->company_city ?? $user->city}}, {{$user->company_zipcode ?? $user->zip}}<br/>
                </h5>
            </div>
            <div class="mt-2">
                <h5 class="ms-1">
                    E-mail: {{$user->email}}
                </h5>
            </div>
        </div>
    </div>
    <div class="table-responsive mt-3 mb-3">
        <table class="table card-table table-vcenter text-wrap datatable justify-content-center">
            <thead>
                <tr>
                    <th style="width:15%;font-size:8px;">Date/Transaction No.</th>
                    <th style="width:15%;font-size:8px;">Sender</th>
                    <th style="width:15%;font-size:8px;">Receiver</th>
                    <th style="width:20%;font-size:8px;">Description</th>
                    <th style="width:15%;font-size:8px;">Amount</th>
                    <th style="width:10%;font-size:8px;">Fee</th>
                    <th style="width:10%;font-size:8px;">Currency</th>
                </tr>
            </thead>

            <tbody>
                @php
                    $i = 1;
                @endphp
                @foreach($trans as $tran)
                <tr>

                    <td style="font-size:8px;">{{date('d-m-Y', strtotime($tran->created_at))}} <br/> {{$tran->trnx}}</td>
                    <td style="font-size:8px;">{{__(json_decode($tran->data)->sender ?? "")}}</td>
                    <td style="font-size:8px;">{{__(json_decode($tran->data)->receiver ?? "")}}</td>
                    <td style="text-align: left; font-size:8px;">{{__(json_decode($tran->data)->description ?? "")}}<br/>{{ucwords(str_replace('_',' ',$tran->remark))}}</td>
                    <td style="text-align: left;font-size:8px;">{{$tran->type}} {{amount($tran->amount,$tran->currency->type,2)}}</td>
                    <td style="text-align: left;font-size:8px;">{{'-'}} {{amount($tran->charge,$tran->currency->type,2)}} </td>
                    <td style="text-align: left;font-size:8px;">{{$tran->currency->code}} </td>

                </tr>
                @php
                    $i++;
                @endphp
                @endforeach
            </tbody>
        </table>



    </div>
    <div id="document-des" style="text-align: center; font-size:8px;">
        The document is computer printout and does not require any additional signatures or the Financial Institution's seal.<br/>
    Monezium GE LLC registered in Georgia(Registration number: 4151104933; license number: 398/S/1B-7T/393/2021)cooperating with<br/>
    Monezium Spzoo, registered in Poland(Registration number: 0000728097 ; license number: MIP33/2019)<br/>
    Clear Junction Limited, registered in England with registered number 10266827, Registered address: 4th Floor Imperial House, 15 Kingsway, London, United Kingdom,
    Clear Junction is authorised and regulated by the Financial Conduct Authority under reference number 90068
    </div>
    <script src="{{asset('assets/admin/')}}/js/jquery.min.js"></script>
    <!-- Tabler Core -->
    <script src="{{asset('assets/user/')}}/js/tabler.min.js"></script>
    {{-- <script src="{{public_path('assets/user/')}}js/demo.min.js"></script> --}}
    {{-- @include('notify.alert') --}}
    @stack('script')
</body>
</html>
