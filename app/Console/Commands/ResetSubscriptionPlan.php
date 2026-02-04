<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Notifications\SubscriptionReminder;
use App\Support\Repositories\UserRepository;

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
        //

        $usersToRemind=DB::table('users')
        ->where('reminder_date','=', now()->toDateString())
        ->where('subscription_plan','!=','free')
        ->get();

        foreach($usersToRemind as $user){
            $userModel=App(UserRepository::class)->find($user->id);
            $userModel->notify(new SubscriptionReminder($userModel));
        }

        DB::table('users')
            ->where('expiry_date', '=', now()->toDateString())
            ->where('subscription_plan', '!=', 'free')
            ->update([
                'subscription_plan' => 'free',
                'expiry_date' => null,
                'reminder_date'=>null,
            ]);

        $this->info("Reset expired subscriptions");

        return Command::SUCCESS;
    }
}
