<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <div class="table-responsive">
        <table class="table card-table table-vcenter text-nowrap datatable">
          <thead>
            <tr>
				<th style="width:50px;font-size:8px;">@lang('Trnx')/@lang('Date')</th>
				<th style="width:110px;font-size:8px;">Sender</th>
				<th style="width:120px;font-size:8px;">Receiver</th>
				<th style="width:150px;font-size:8px;">Description</th>      
				<th style="width:75px;font-size:8px;">@lang('Amount')</th>
				<th style="width:50px;font-size:8px;">@lang('Charge')</th>     
            </tr>
          </thead>
          <tbody>
            @forelse (auth()->user()->balance_transfers as $key=>$data)
              <tr>
				<td style="width:110px;font-size:8px;" data-label="@lang('Trnx')">
                  <div >
                    {{ $data->trnx }}</br>{{date('d M Y',strtotime($data->created_at))}}
                  </div>
                </td>
				<td style="width:110px;font-size:8px;" data-label="@lang('Sender')">
                  <div>
                    <p>{{$data->sender }}</p>
                  </div>
                </td>
				<td style="width:110px;font-size:8px;"data-label="@lang('Sender')">
                  <div>
                    <p>{{$data->receiver }}</p>
                  </div>
                </td>
                <td style="width:110px;font-size:8px;" data-label="@lang('Amount')">
                  <div>
                    <p class="text-{{ $data->amount == '+' ? 'success' : 'danger'}}">{{ showprice($data->amount,$currency) }}</p>
                  </div>
                </td>
				<td style="width:110px;font-size:8px;" data-label="@lang('Fee')">
                  <div>
                    <p class="text-{{ $data->amount == '+' ? 'danger' : 'danger'}}">'-'{{ showprice($data->charge,$currency) }}</p>
                  </div>
                </td>
                
              </tr>
            @empty
              <p>@lang('NO DATA FOUND')</p>
            @endforelse

          </tbody>
        </table>
    </div>
</body>
</html>
