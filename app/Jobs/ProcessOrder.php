<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderPaid;

class ProcessOrder implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Order $order,
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        sleep(3);
        Log::info('Processing order', $this->order->toArray());
        Mail::to($this->order->buyer->email)->send(new OrderPaid($this->order));
    }
}
