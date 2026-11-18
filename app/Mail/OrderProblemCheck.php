<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderProblemCheck extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $orders;

    /**
     * Create a new message instance.
     *
     * @param  array<int, array<string, mixed>>  $orders
     */
    public function __construct(array $orders = [])
    {
        $this->orders = $orders;
    }

    /**
     * Build the message.
     */
    public function build(): static
    {
        return $this->view('emails.order-problem-check')->subject('Commandes entre Woocommerce et TCPOS : Problème de synchronisation détecté');
    }
}
