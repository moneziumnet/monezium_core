<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignDonation;
use App\Models\Currency;
use App\Models\CampaignCategory;
use App\Models\Generalsetting;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Charge;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Datatables;

class CampaignController extends Controller
{
    public function datatables()
    {
        $datas = Campaign::orderBy('id','desc')->with('category');

        return Datatables::of($datas)
                        ->editColumn('date', function(Campaign $data) {
                            $date = date('d-m-Y',strtotime($data->created_at));
                            return $date;
                        })
                        ->editColumn('deadline', function(Campaign $data) {
                            $date = date('d-m-Y',strtotime($data->deadline));
                            return $date;
                        })
                        ->editColumn('organizer',function(Campaign $data){
                            return $data->user->company_name ?? $data->user->name;
                        })
                        ->editColumn('goal',function(Campaign $data){
                            return $data->currency->symbol.$data->goal;
                        })
                        ->editColumn('fund', function(Campaign $data) {
                            $total = CampaignDonation::where('campaign_id', $data->id)->where('status', 1)->sum('amount');
                            return $data->currency->symbol.$total;
                        })
                        ->editColumn('status', function(Campaign $data) {
                            $status      = $data->status == '1' ? _('Acitve') : _('Inactive');
                            $status_sign = $data->status == '1' ? 'success'   : 'secondary';

                            return '<div class="btn-group mb-1">
                            <button type="button" class="btn btn-'.$status_sign.' btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              '.$status .'
                            </button>
                            <div class="dropdown-menu" x-placement="bottom-start">
                              <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin.campaign.status',['id1' => $data->id, 'id2' => '1']).'">'.__("Active").'</a>
                              <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin.campaign.status',['id1' => $data->id, 'id2' => '0']).'">'.__("Inactive").'</a>
                            </div>
                          </div>';
                        })
                        ->editColumn('action', function(Campaign $data) {
                            $total = CampaignDonation::where('campaign_id', $data->id)->where('status', 1)->sum('amount');
                            $delete = '<a href="javascript:;" data-href="' . route('admin.campaign.delete',$data->id) . '" data-toggle="modal" data-target="#deleteModal" class="dropdown-item">'.__("Delete").'</a>';
                            return '<div class="btn-group mb-1">
                                <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    '.'Actions' .'
                                </button>
                                <div class="dropdown-menu" x-placement="bottom-start">
                                <a href="javascript:;"  data-data= \''.json_encode($data).'\' data-total="'.$total.'"  onclick = "getdetails(event)"class=" dropdown-item detailsBtn" >
                                ' . __("Details") . '</a>'.$delete.'

                                </div>
                                </div>';
                        })
                        ->rawColumns(['date','deadline','organizer','fund','status', 'action'])
                        ->toJson();
    }

    public function index(){
        return view('admin.campaign.index');
    }

    public function status($id1,$id2){
        $data = Campaign::findOrFail($id1);
        $data->status = $id2;
        $data->update();
        $msg = 'Data Updated Successfully.';
        $title = $ids == '1' ? "campaign_enable" : 'campaign_disable';
        $user = User::findOrFail($data->user_id);
        mailSend($title,['campaign_title'=>$data->title], $user);
        send_notification($user->id, 'Campaign status is updated for '.($user->company_name ?? $user->name)."\n Campaign Title : ".$data->title, route('admin.campaign.index'));


        return response()->json($msg);
      }
    public function destroy($id)
    {
        $data = Campaign::findOrFail($id);
        $donations = CampaignDonation::where('campaign_id', $data->campaign_id)->get();
        foreach ($donations as $key => $value) {
            $value->delete();
        }
        $data->delete();
        $msg = __('Data Deleted Successfully.');
        return response()->json($msg);
    }

    public function donation_index(){
        return view('admin.campaign.donation');
    }

    public function donation_datatables()
    {
        $datas = CampaignDonation::orderBy('id','desc');

        return Datatables::of($datas)
                        ->editColumn('date', function(CampaignDonation $data) {
                            $date = date('d-m-Y',strtotime($data->created_at));
                            return $date;
                        })
                        ->editColumn('organizer',function(CampaignDonation $data){
                            $user = User::where('id', $data->campaign->user_id)->first();
                            return $user->company_name ?? $user->name;
                        })
                        ->editColumn('title',function(CampaignDonation $data){
                            return $data->campaign->title;
                        })
                        ->editColumn('donator',function(CampaignDonation $data){
                            return $data->user_name;
                        })
                        ->editColumn('amount', function(CampaignDonation $data) {
                            return $data->currency->symbol.$data->amount;
                        })
                        ->editColumn('status', function(CampaignDonation $data) {
                            $status      = $data->status == '1' ? _('Approved') : _('Pending');
                            $status_sign = $data->status == '1' ? 'success'   : 'secondary';

                            return '<div class="btn-group mb-1">
                            <button type="button" class="btn btn-'.$status_sign.' btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              '.$status .'
                            </button>
                            <div class="dropdown-menu" x-placement="bottom-start">
                              <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin.donation.status',['id1' => $data->id, 'id2' => '1']).'">'.__("Approved").'</a>
                            </div>
                          </div>';
                        })
                        ->editColumn('action', function(CampaignDonation $data) {
                            $total = CampaignDonation::where('campaign_id', $data->id)->where('status', 1)->sum('amount');
                            $organizer = User::findOrFail($data->campaign->user_id)->company_name ?? User::findOrFail($data->campaign->user_id)->name;
                            $delete = '<a href="javascript:;" data-href="' . route('admin.donation.delete',$data->id) . '" data-toggle="modal" data-target="#deleteModal" class="dropdown-item">'.__("Delete").'</a>';
                            return '<div class="btn-group mb-1">
                                <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    '.'Actions' .'
                                </button>
                                <div class="dropdown-menu" x-placement="bottom-start">
                                <a href="javascript:;"   data-data= \''.json_encode($data).'\' data-organizer="'.$organizer.'" onclick = "getdetails(event)" class=" dropdown-item detailsBtn" >
                                ' . __("Details") . '</a>'.$delete.'

                                </div>
                                </div>';
                        })
                        ->rawColumns(['date','deadline','organizer','fund','status', 'action'])
                        ->toJson();
    }

    public function donation_status($id1,$id2){
        $donation = CampaignDonation::findOrFail($id1);
        $data = Campaign::findOrFail($donation->campaign_id);
        $gs = Generalsetting::first();
        if($donation->status == 1) {
            $msg = 'Donation already completed';
            // return back()->with(array('warning' => $msg));
            return response()->json($msg);

        }
        if(explode("-",$donation->payment)[0] == 'bank_pay') {
            return response()->json(array('errors' => [ 0 => __('You can not approve this donation via Bank, This will be approved automatically after incoming via Bank.') ]));
        }
        else if($donation->payment == 'gateway') {
            return response()->json(array('errors' => [ 0 => __('You can not approve this donation via Payment Gateway, This will be approved automatically after incoming via Payment Gateway.') ]));
        }
        else if($donation->payment == 'crypto') {
            $wallet = get_wallet($data->user_id, $donation->currency_id, 8);

            $rcvTrnx              = new Transaction();
            $rcvTrnx->trnx        = str_rand();
            $rcvTrnx->user_id     = $data->user_id;
            $rcvTrnx->user_type   = 1;
            $rcvTrnx->currency_id = $donation->currency_id;
            $rcvTrnx->wallet_id   = $wallet->id;
            $rcvTrnx->amount      = $donation->amount;
            $rcvTrnx->charge      = 0;
            $rcvTrnx->remark      = 'campaign_payment';
            $rcvTrnx->type        = '+';
            $rcvTrnx->details     = trans('Receive Campaign Payment : '). $data->ref_id;

            $rcvTrnx->data        = '{"sender":"'.$donation->user_name.'", "receiver":"'.(User::findOrFail($data->user_id)->company_name ?? User::findOrFail($data->user_id)->name).'", "description":"'.$donation->description.'"}';
            $rcvTrnx->save();
            $donation->amount = $donation->amount * getRate($wallet->currency);
            $donation->currency_id = $data->currency_id;
        }
        $totalamount = CampaignDonation::where('campaign_id', $donation->campaign_id)->whereStatus(1)->sum('amount');
        if($totalamount >= $donation->campaign->goal) {
            $data->status = 0;
            $data->update();
        }
        $donation->status = $id2;
        $donation->update();
        $msg = 'Data Updated Successfully.';
        return response()->json($msg);
      }
    public function donation_destroy($id)
    {
        $data = CampaignDonation::findOrFail($id);
        $data->delete();
        $msg = __('Data Deleted Successfully.');
        return response()->json($msg);
    }
}

