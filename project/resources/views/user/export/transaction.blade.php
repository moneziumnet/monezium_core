<table>
    <thead>
        <tr>
            <th colspan="5" style="text-align: center">
                {{$user->name}}
       
            </th>
        </tr>
        <tr>
            <th colspan="5" style="text-align: center">
                {{$user->address}}<br/>
                {{$user->city}}, {{$user->zip}}<br/>
                {{$user->account_number}}
            </th>
        </tr>
        <tr>
            <th colspan="5" style="text-align: center">
                {{$user->city}}, {{$user->zip}}
            </th>
        </tr>
        <tr>
            <th colspan="5" style="text-align: center">
                Account Number: {{$user->account_number}}
            </th>
        </tr>
        <tr>
            <th>##</th>
            <th>Date</th>
            <th>Transaction ID</th>
            <th>Remark</th>
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
            <td>{{$tran->txnid}}</td>
            <td>{{ucwords(str_replace('_',' ',$tran->remark))}}</td>
            <td>{{$tran->type}} {{amount($tran->amount,$tran->currency->type,2)}} {{$tran->currency->code}}</td>
        </tr>
        @php
            $i++;
        @endphp
        @endforeach
    </tbody>
</table>