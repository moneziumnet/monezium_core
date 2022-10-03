<table>
    <thead>
        <tr>
            <th colspan="7" style="text-align: center">
                {{$user->name}}
            </th>
        </tr>
        <tr>
            <th colspan="7" style="text-align: center">
                {{$user->address}}<br/>
                {{$user->city}}, {{$user->zip}}<br/>
                {{-- {{$user->account_number}} --}}
            </th>
        </tr>
        {{-- <tr>
            <th colspan="7" style="text-align: center">
                {{$user->city}}, {{$user->zip}}
            </th>
        </tr> --}}
        <tr>
            <th colspan="7" style="text-align: center">
                E-mail: {{$user->email}}
            </th>
        </tr>
        <tr>
            <th style="width:25px;">No</th>
            <th style="width:80px;">Date</th>
            <th style="width:125px;">Transaction ID</th>
            <th style="width:100px;">Sender</th>
            <th style="width:100px;">Receiver</th>
            <th style="width:100px;">Remark</th>
            <th>Amount</th>
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
            <td style="width:125px;">{{$tran->trnx}}</td>
            <td>{{__(json_decode($tran->data)->sender ?? "")}}</td>
            <td>{{__(json_decode($tran->data)->receiver ?? "")}}</td>
            <td>{{ucwords(str_replace('_',' ',$tran->remark))}}</td>
            <td style="text-align: right;">{{$tran->type}} {{amount($tran->amount,$tran->currency->type,2)}} {{$tran->currency->code}}</td>
        </tr>
        @php
            $i++;
        @endphp
        @endforeach
    </tbody>
</table>
