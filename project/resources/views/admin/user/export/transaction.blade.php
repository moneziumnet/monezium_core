<table>
    <thead>
        <tr>
            <th colspan="6" style="text-align: center; font-size:18px;">
               {{$gs->disqus}}<br/><br/><br/>
            </th>
        </tr>


	   <tr>
            <th colspan="6" style="text-align: center">
                {{$user->company_name ?? $user->name}}
            </th>
        </tr>

        <tr>
            <th colspan="6" style="text-align: center">
                {{$user->company_address ?? $user->address}}<br/>
                {{$user->company_city ?? $user->city}}, {{$user->company_zipcode ?? $user->zip}}<br/>

            </th>
        </tr>
        <tr>
            <th colspan="6" style="text-align: center">
                E-mail: {{$user->email}}
            </th>
        </tr>
        <tr>
            <th style="width:100px;font-size:8px;">Date / Transaction ID</th>
            <th style="width:110px;font-size:8px;">Sender</th>
            <th style="width:120px;font-size:8px;">Receiver</th>
			<th style="width: 220px;font-size:8px;">Description</th>
            <th style="width:100px;font-size:8px;">Amount</th>
			<th style="width:75px;font-size:8px;">Fee</th>
			<th style="width:75px;font-size:8px;">Currency</th>
        </tr>
    </thead>

    <tbody>
        @php
            $i = 1;
        @endphp
        @foreach($trans as $tran)
        <tr>
            <td style="font-size:8px;">{{date('d-M-Y', strtotime($tran->created_at))}} {{$tran->trnx}}</td>
            <td style="font-size:8px;">{{__(json_decode($tran->data)->sender ?? "")}}</td>
            <td style="font-size:8px;">{{__(json_decode($tran->data)->receiver ?? "")}}</td>
			<td style="text-align: left; font-size:8px;">{{__(json_decode($tran->data)->description ?? "")}}<br/>{{ucwords(str_replace('_',' ',$tran->remark))}}</td>
            <td style="text-align: right;font-size:8px;">{{$tran->type}}{{amount($tran->amount,$tran->currency->type,2)}}</td>
			<td style="text-align: right;font-size:8px;">-{{amount($tran->charge,$tran->currency->type,2)}}</td>
			<td style="text-align: right;font-size:8px;">{{$tran->currency->code}} </td>
        </tr>
        @php
            $i++;
        @endphp
        @endforeach
    </tbody>
</table>
