<?php
/**
 * Created by PhpStorm.
 * User: ShaOn
 * Date: 11/29/2018
 * Time: 12:49 AM
 */

namespace App\Classes;

use App\Models\EmailTemplate;
use App\Models\Generalsetting;
use App\Models\Pagesetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PDF;

class MoneziumMailer
{
    public function __construct()
    {
        $this->email = DB::table('generalsettings')->first();
        Config::set('mail.port', $this->email->smtp_port);
        Config::set('mail.host', $this->email->smtp_host);
        Config::set('mail.username', $this->email->smtp_user);
        Config::set('mail.password', $this->email->smtp_pass);
        Config::set('mail.encryption', $this->email->smtp_encryption);
    }
    public function sendCustomMail(array $mailData)
    {
        return true;
        $setup = Generalsetting::first();
        $pageSetting = Pagesetting::first();
        $data = [
            'email_body' => $mailData['body'],
            'logo' => $setup->logo,
            'title' => $setup->title,
            'street' => $pageSetting->street,
            'copyright' => $setup->copyright
        ];
        $objDemo = new \stdClass();
        $objDemo->to = $mailData['to'];
        $objDemo->from = $setup->from_email;
        $objDemo->title = $setup->from_name;
        $objDemo->subject = $mailData['subject'];
        $objDemo->attach = $mailData['attach'] ?? null;
        try {
            Mail::send('admin.email.mailbody', $data, function ($message) use ($objDemo) {
                $message->from($objDemo->from, $objDemo->title);
                $message->to($objDemo->to);
                // $message->subject($objDemo->subject);
                if ($objDemo->attach) {
                    // $pdf = PDF::loadView('frontend.myPDF', $objDemo->attach);
                    $message->subject($objDemo->subject)
                        ->attach($objDemo->attach);
                } else {
                    $message->getHeaders()
                        ->addTextHeader('Content-Type', 'text/html; charset=utf-8\r\n');
                    $message->subject($objDemo->subject);
                }
            });
        } catch (\Exception $e) {
            die($e);
        }
        return true;
    }

}