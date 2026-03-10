<?php

namespace App\Jobs;

use App\Contracts\Interface\SubscriptionPaymentInterface;
use App\Enum\FlutterwaveWebhookEvent;
use App\Enum\TransactionStatus;
use App\Support\Repositories\TransactionRepository;
use App\Support\Repositories\UserRepository;
use App\Traits\HasResponse;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;
use Spatie\WebhookClient\Models\WebhookCall;

class FlutterwaveWebhookJob extends ProcessWebhookJob implements ShouldQueue
{
    use HasResponse, Queueable;

    private TransactionRepository $transactionRepository;

    private SubscriptionPaymentInterface $subscriptionPaymentService;

    private UserRepository $userRepository;

    /**
     * Create a new job instance.
     */
    public function __construct(WebhookCall $webhookCall)
    {
        //
        parent::__construct($webhookCall);
        $this->transactionRepository = app(TransactionRepository::class);
        $this->subscriptionPaymentService = app(SubscriptionPaymentInterface::class);
        $this->userRepository = app(UserRepository::class);
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

        if (! $transaction) {
            return;
        }

        $verifyTransaction = $this->subscriptionPaymentService->verifyTransaction($transaction->transaction_id);

        if ($verifyTransaction->status !== TransactionStatus::SUCCESS->value) {
            return;
        }

        $this->transactionRepository->update($transaction->id, [
            'status' => TransactionStatus::from($verifyTransaction->status),
        ]);

        $this->userRepository->update($transaction->user_id, [
            'card_token' => $verifyTransaction->token,
        ]);
    }
}
