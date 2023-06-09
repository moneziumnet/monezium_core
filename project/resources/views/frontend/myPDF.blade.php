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

    {{-- <link href="{{URL::asset('assets/user/')}}/css/tabler.min.css" rel="stylesheet"/>
    <link href="{{URL::asset('assets/user/')}}/css/tabler-flags.min.css" rel="stylesheet"/>
    <link href="{{URL::asset('assets/user/')}}/css/tabler-payments.min.css" rel="stylesheet"/>
    <link href="{{URL::asset('assets/user/')}}/css/tabler-vendors.min.css" rel="stylesheet"/>
    <link href="{{URL::asset('assets/user/')}}/css/custom.css" rel="stylesheet"/>
    <link href="{{URL::asset('assets/user/')}}/css/demo.min.css" rel="stylesheet"/>


    <link rel="stylesheet" type="text/css" href="{{URL::asset('assets/user/')}}/css/bootstrap-4.3.1.css">
    <script type="text/javascript" src="{{URL::asset('assets/user/')}}/js/jquery-1.12.4.min.js"></script>
    <link type="text/css" href="{{URL::asset('assets/user/')}}/css/jquery-ui.css" rel="stylesheet">
    <script type="text/javascript" src="{{URL::asset('assets/user/')}}/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="{{URL::asset('assets/user/')}}/js/jquery.signature.js"></script>
    <link rel="stylesheet" type="text/css" href="{{URL::asset('assets/user/')}}/css/jquery.signature.css"> --}}
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>

        #document-des {
            position: absolute;
            bottom: 0;
            width: 100%;
        }
        .document-logo {
            height: 5rem;
            width: 5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div>
        <div class="text-center row text-wrap text-center">
            <div class="mt-3">
                    <img src="{{'data:image/jpeg;base64,'.$image}}" class="document-logo">
            </div>
            <div class="mt-3">
                <h3 class="font-weight-bold">
                    {{$gs->disqus}}<br/>
                </h3>

            </div>
            <div class="mt-3" style="font-size:8px;">
                    <b class="font-weight-bold" >{{__('First, Last name / Company name: ')}}</b>{{$user->company_name ?? $user->name}}

            </div>
            <div class="mt-3" style="font-size:8px;">
                <b class="font-weight-bold" >{{__('Personal Code / Company Registration No: ')}}</b>{{$user->company_reg_no ?? $user->personal_code}}
            </div>
            <div class="mt-2" style="font-size:8px;">
                <b class="font-weight-bold" >{{__('Address: ')}}</b>{{$user->company_address ?? $user->address}}<br/>
                {{$user->company_city ?? $user->city}}, {{$user->company_zipcode ?? $user->zip}}<br/>
            </div>
            <div class="mt-2" style="font-size:8px;">
                <b class="font-weight-bold" >{{__('Email: ')}}</b>{{$user->email}}
            </div>
            @if (isset($wallet) && $wallet != '')
            <div class="mt-2 mb-3" style="font-size:8px;">
                <b class="font-weight-bold" >{{__('Account No: ')}}</b>{{$wallet->wallet_no}}
            </div>
            @endif
        </div>
    </div>
    <div class="text-center row text-wrap text-center">

        <div class="mt-3">
            <h6 class="font-weight-bold">
                {{__('Transaction History')}}<br/>
            </h6>

        </div>
        @if (isset($start_time) && isset($end_time))
        <div>
            <h6 class="font-weight-bold">
                {{$start_time ? __('FROM ') : ''}}{{$start_time ?? '' }}{{__(' TILL ')}}{{$end_time}}<br/>
            </h6>
        </div>
    </div>
    @endif
    <div class="table-responsive mb-3">
        <table class="table card-table table-vcenter text-wrap datatable justify-content-center">
            @if (isset($s_bal))
                <tr style="align-content: flex-end">
                    <td style="width:15%;font-size:8px;"></td>
                    <td style="width:15%;font-size:8px;"></td>
                    <td style="width:15%;font-size:8px;"></td>
                    <td style="width:20%;font-size:8px;"></td>
                    <td style="width:15%;font-size:8px;"></td>
                    <td style="width:15%;font-size:8px;">{{__("Start Balance.")}}</td>
                    <td style="width:15%;font-size:8px;">{{$s_bal.$def_code}}</td>
                </tr>
            @endif
            <thead>
                <tr>
                    <th style="width:15%;font-size:8px;">{{__("Date / Transaction ID")}}</th>
                    <th style="width:15%;font-size:8px;">{{__("Sender")}}</th>
                    <th style="width:15%;font-size:8px;">{{__("Receiver")}}</th>
                    <th style="width:20%;font-size:8px;">{{__("Description")}}</th>
                    <th style="width:15%;font-size:8px;">{{__("Amount")}}</th>
                    <th style="width:10%;font-size:8px;">{{__("Fee")}}</th>
                    <th style="width:10%;font-size:8px;">{{__("Currency")}}</th>
                </tr>
            </thead>

            <tbody>

                @php
                    $i = 1;
                @endphp
                @foreach($trans as $tran)
                <tr>

                    <td style="font-size:8px;">{{date('d-M-Y', strtotime($tran->created_at))}} <br/> {{$tran->trnx}}</td>
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
                @if (isset($e_bal))
                    <tr style="align-content: flex-end">
                        <td style="width:15%;font-size:8px;"></td>
                        <td style="width:15%;font-size:8px;"></td>
                        <td style="width:15%;font-size:8px;"></td>
                        <td style="width:20%;font-size:8px;"></td>
                        <td style="width:15%;font-size:8px;"></td>
                        <td style="width:15%;font-size:8px;">{{__("End Balance.")}}</td>
                        <td style="width:15%;font-size:8px;">{{$e_bal.$def_code}}</td>
                    </tr>
                @endif
            </tbody>
        </table>



    </div>
    <div id="document-des" class="mt-5" style="text-align: center; font-size:8px;">
        The document is computer printout and does not require any additional signatures or the Financial Institution's seal.<br/>
    Monezium GE LLC registered in Georgia(Registration number: 4151104933; license number: 398/S/1B-7T/393/2021)cooperating with<br/>
    Monezium Spzoo, registered in Poland(Registration number: 0000728097 ; license number: MIP33/2019)<br/>
    Clear Junction Limited, registered in England with registered number 10266827, Registered address: 4th Floor Imperial House, 15 Kingsway, London, United Kingdom,
    Clear Junction is authorised and regulated by the Financial Conduct Authority under reference number 90068
    </div>
    <script src="{{URL::asset('assets/admin/')}}/js/jquery.min.js"></script>
    <!-- Tabler Core -->
    <script src="{{URL::asset('assets/user/')}}/js/tabler.min.js"></script>
    {{-- <script src="{{public_path('assets/user/')}}js/demo.min.js"></script> --}}
    {{-- @include('notify.alert') --}}
    @stack('script')
</body>
</html>
