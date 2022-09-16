<?php

namespace App\Http\Controllers\Admin;

use App\Classes\GeniusMailer;
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
                            return $data->user->name;
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
                            $name = User::where('id', $data->campaign->user_id)->first()->name;
                            return $name;
                        })
                        ->editColumn('title',function(CampaignDonation $data){
                            return $data->campaign->title;
                        })
                        ->editColumn('donator',function(CampaignDonation $data){
                            return $data->user->name;
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
                              <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'. route('admin.donation.status',['id1' => $data->id, 'id2' => '0']).'">'.__("Pending").'</a>
                            </div>
                          </div>';
                        })
                        ->editColumn('action', function(CampaignDonation $data) {
                            $total = CampaignDonation::where('campaign_id', $data->id)->where('status', 1)->sum('amount');
                            $organizer = User::findOrFail($data->campaign->user_id)->name;
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
        if($donation->payment == 'wallet' && $id2 == 1){
            $wallet = Wallet::where('user_id',$donation->user_id)->where('user_type',1)->where('currency_id',$donation->currency_id)->where('wallet_type', 1)->first();

            if(!$wallet){
                $wallet =  Wallet::create([
                    'user_id'     => $donation->user_id,
                    'user_type'   => 1,
                    'currency_id' => $donation->currency_id,
                    'balance'     => 0,
                    'wallet_type' => 1,
                    'wallet_no' => $gs->wallet_no_prefix. date('ydis') . random_int(100000, 999999)
                ]);

                $user = User::findOrFail($donation->user_id);

                $chargefee = Charge::where('slug', 'account-open')->where('plan_id', $user->bank_plan_id)->first();

                $trans = new Transaction();
                $trans->trnx = str_rand();
                $trans->user_id     = $user->id;
                $trans->user_type   = 1;
                $trans->currency_id = 1;
                $trans->amount      = $chargefee->data->fixed_charge;
                $trans->charge      = 0;
                $trans->type        = '-';
                $trans->remark      = 'wallet_create';
                $trans->details     = trans('Wallet Create');
                $trans->data        = '{"sender":"'.$user->name.'", "receiver":"System Account"}';
                $trans->save();

                user_wallet_decrement($user->id, 1, $chargefee->data->fixed_charge, 1);
                user_wallet_increment(0, 1, $chargefee->data->fixed_charge, 9);
            }

            if($wallet->balance < $donation->amount) {
                return response()->json('Insufficient balance to your wallet');
            }

            $wallet->balance -= $donation->amount;
            $wallet->update();

            $trnx              = new Transaction();
            $trnx->trnx        = str_rand();
            $trnx->user_id     = $donation->user_id;
            $trnx->user_type   = 1;
            $trnx->currency_id = $donation->currency_id;
            $trnx->wallet_id   = $wallet->id;
            $trnx->amount      = $donation->amount;
            $trnx->charge      = 0;
            $trnx->remark      = 'donation_payment';
            $trnx->type        = '-';
            $trnx->details     = trans('Payemnt for Donation : '). $donation->campaign->ref_id;
            $trnx->data        = '{"sender":"'.User::findOrFail($donation->user_id)->name.'", "receiver":"'.User::findOrFail($donation->campaign->user_id)->name.'"}';
            $trnx->save();

            $rcvWallet = Wallet::where('user_id',$donation->campaign->user_id)->where('user_type',1)->where('currency_id',$donation->currency_id)->where('wallet_type', 1)->first();

            $rcvWallet->balance += $donation->amount;
            $rcvWallet->update();

            $rcvTrnx              = new Transaction();
            $rcvTrnx->trnx        = $trnx->trnx;
            $rcvTrnx->user_id     = $donation->campaign->user_id;
            $rcvTrnx->user_type   = 1;
            $rcvTrnx->currency_id = $donation->currency_id;
            $rcvTrnx->wallet_id   = $rcvWallet->id;
            $rcvTrnx->amount      = $donation->amount;
            $rcvTrnx->charge      = 0;
            $rcvTrnx->remark      = 'donation_receive_payment';
            $rcvTrnx->type        = '+';
            $rcvTrnx->details     = trans('Receive Payemnt for Donation : '). $donation->campaign->ref_id;
            $rcvTrnx->data        = '{"sender":"'.User::findOrFail($donation->user_id)->name.'", "receiver":"'.User::findOrFail($donation->campaign->user_id)->name.'"}';
            $rcvTrnx->save();
            $donation->status = $id2;
            $donation->update();
            $totalamount = CampaignDonation::where('campaign_id', $donation->campaign_id)->whereStatus(1)->sum('amount');
            if($totalamount >= $donation->campaign->goal) {
                $data->status = 0;
                $data->update();
            }


            $to = $data->user->email;
            $subject = "Received Campaign Donation payments";
            $msg_body = "You received money ".amount($data->amount, $data->currency->type,2)." \n The customers donate your campaign." ;
            $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
            $headers .= "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            @mail($to,$subject,$msg_body,$headers);

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

