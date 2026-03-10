<?php

use App\Console\Commands\ResetSubscriptionPlan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('sanctum:prune-expired --hours=24')->daily();
Schedule::command(ResetSubscriptionPlan::class)->daily();
