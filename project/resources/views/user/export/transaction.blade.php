<table>
    <thead>
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