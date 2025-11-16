<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendMail extends Mailable
{
    use Queueable, SerializesModels;
    public $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
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
        $subject = '';
        if($this->data['type'] == 'request'){
            $subject .= "[OneClick] ".$this->data['ticket']." - New Request - ";
        }
        if($this->data['type'] == 'request_user'){
            $subject .= "[OneClick] ".$this->data['ticket']." - New Request - ";
        }
        if($this->data['type'] == 'reject'){
            $subject .= "[OneClick] ".$this->data['ticket']." - Rejected - ";
        }
        if($this->data['type'] == 'return'){
            $subject .= "[OneClick] ".$this->data['ticket']." - Returned - ";
        }
        if($this->data['type'] == 'confirm_to_nextflow'){
            $subject .= "[OneClick] ".$this->data['ticket']." - Confirm To Next Workflow - ";
        }
        if($this->data['type'] == 'confirm_to_nextflow_pic'){
            $subject .= "[OneClick] ".$this->data['ticket']." - New Request To Confirm - ";
        }
        if($this->data['type'] == 'assign_pic'){
            $subject .= "[OneClick] ".$this->data['ticket']." - Confirmed - ";
        }
        if($this->data['type'] == 'completed'){
            $subject .= "[OneClick] ".$this->data['ticket']." - Completed - ";
        }
        return $this->subject($subject)->view('mail-template')->with('data', $this->data);
    }
}
