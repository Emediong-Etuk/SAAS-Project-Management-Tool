<?php

namespace App\Console\Commands;

use App\Contracts\Interface\SubscriptionPaymentInterface;
use App\Enum\PlansEnum;
use App\SubscriptionStatus;
use App\Support\Repositories\UserRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetSubscriptionPlan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-subscription-plan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset User subscription plan from pro to free if expired today';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $users = DB::table('users')
            ->where('expiry_date', '=', now()->toDateString())
            ->where('subscription_plan', '=', PlansEnum::Pro->value)->get();

        foreach ($users as $user) {
            $subscriptionStatus = App(SubscriptionPaymentInterface::class)->getSubscriptionStatus($user->id);
            $userRepository = App(UserRepository::class);

            if ($subscriptionStatus->status === SubscriptionStatus::Active->value) {
                $userRepository->update($user->id, [
                    'reminder_date' => now()->addWeeks(3)->toDateString(),
                    'expiry_date' => now()->addMonth()->toDateString(),
                    'subscription_plan' => PlansEnum::Pro->value,
                ]);
            } else {
                $userRepository->update($user->id, [
                    'subscription_plan' => PlansEnum::Free->value,
                    'expiry_date' => null,
                    'reminder_date' => null,
                ]);
            }
        }

        $this->info('Reset expired subscriptions');

        return Command::SUCCESS;
    }
}
