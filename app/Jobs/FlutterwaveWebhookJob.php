<?php

namespace App\Jobs;

use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;
use Spatie\WebhookClient\Models\WebhookCall;

class FlutterwaveWebhookJob extends ProcessWebhookJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
        parent::__construct(WebhookCall::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $webhookData=$this->webhookCall->payload;
    }
}
