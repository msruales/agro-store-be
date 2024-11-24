<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendRequest extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $comercialName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($order, $comercialName)
    {
        $this->order = $order;
        $this->comercialName = $comercialName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $orderDate = $this->order->date;

        $subject = "Pedidos $this->comercialName, $orderDate";

        return $this->subject($subject)->view('mail.order_to_send');
    }
}
