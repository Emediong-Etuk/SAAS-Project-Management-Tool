<?php


use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\ResetSubscriptionPlan;

Schedule::command('sanctum:prune-expired --hours=24')->daily();
Schedule::command(ResetSubscriptionPlan::class)->daily();