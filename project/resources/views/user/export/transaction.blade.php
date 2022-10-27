<table>
    <thead>
        <tr>
            <th colspan="8" style="text-align: center">
                {{$user->company_name ?? $user->name}}
            </th>
        </tr>
        <tr>
            <th colspan="8" style="text-align: center">
                {{$user->company_address ?? $user->address}}<br/>
                {{$user->company_city ?? $user->city}}, {{$user->company_zipcode ?? $user->zip}}<br/>
            </th>
        </tr>
        <tr>
            <th colspan="8" style="text-align: center">
                E-mail: {{$user->email}}
            </th>
        </tr>
        <tr>
            <th style="width:25px;">No</th>
            <th style="width:40px;">Date</th>
            <th>Transaction ID</th>
            <th style="width:100px;">Sender</th>
            <th style="width:100px;">Receiver</th>
            <th style="width:100px;">Remark</th>
            <th>Amount</th>
            <th style="width: 100px;">Description</th>
        </tr>
    </thead>

    <tbody>
        @php
            $i = 1;
        @endphp
        @foreach($trans as $tran)
        <tr>
            <td>{{$i}}</td>
            <td>{{date('d-m-Y', strtotime($tran->created_at))}}</td>
            <td>{{$tran->trnx}}</td>
            <td>{{__(json_decode($tran->data)->sender ?? "")}}</td>
            <td>{{__(json_decode($tran->data)->receiver ?? "")}}</td>
            <td>{{ucwords(str_replace('_',' ',$tran->remark))}}</td>
            <td style="text-align: right;">{{$tran->type}} {{amount($tran->amount,$tran->currency->type,2)}} {{$tran->currency->code}}</td>
            <td style="text-align: left;">{{__(json_decode($tran->data)->description ?? "")}}</td>
        </tr>
        @php
            $i++;
        @endphp
        @endforeach
    </tbody>
</table>
