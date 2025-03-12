<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PurchaseSuccess extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $orderDetails;

    public function __construct($user, $orderDetails)
    {
        $this->user = $user;
        $this->orderDetails = $orderDetails;
    }

    public function build()
    {
        return $this->view('emails.purchase_success')
                    ->with([
                        'user' => $this->user,
                        'orderDetails' => $this->orderDetails,
                    ]);
    }
}
