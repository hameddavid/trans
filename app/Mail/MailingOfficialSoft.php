<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailingOfficialSoft extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $data;
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->from('transcript@run.edu.ng')->view('emails.mailing_official_soft')->subject($this->data["sub"])->with('data', $this->data);
        if(!empty($this->data["docs"])){
            foreach($this->data["docs"] as $k => $v){
                $mail = $mail->attach($v["path"],[
                "as" => $v['as'],
                'mime' => $v['mime'],
                ]);
            }
            }
            return $mail;
    }
}
