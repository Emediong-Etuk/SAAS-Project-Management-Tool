<?php

namespace App\Jobs;

use App\TransactionStatus;
use App\Traits\HasResponse;
use App\Enum\FlutterwaveWebhookEvent;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;
use App\Support\Repositories\TransactionRepository;
use App\Contracts\Interface\SubscriptionPaymentInterface;

class FlutterwaveWebhookJob extends ProcessWebhookJob implements ShouldQueue
{
    use Queueable, HasResponse;


    private TransactionRepository $transactionRepository;
    private SubscriptionPaymentInterface $subscriptionPaymentService;
    /**
     * Create a new job instance.
     */
    public function __construct(WebhookCall $webhookCall)
    {
        //
        parent::__construct($webhookCall);
        $this->transactionRepository = app(TransactionRepository::class);
        $this->subscriptionPaymentService = app(SubscriptionPaymentInterface::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $webhookData = $this->webhookCall->payload;

        if ($webhookData['event'] !== FlutterwaveWebhookEvent::value()) {
            return;
        }

        $transaction = $this->transactionRepository->findByRef($webhookData['data']['tx_ref']);


        if (!$transaction) {
            return;
        }


        $verifyTransactionStatus = $this->subscriptionPaymentService->verifyTransaction($transaction->transaction_id);

        if ($verifyTransactionStatus !== TransactionStatus::SUCCESS->value) {
            return;
        }

        $this->transactionRepository->update($transaction->id, [
            'status' => TransactionStatus::from($verifyTransactionStatus),
        ]);
    }
}
