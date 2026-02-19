<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('automation:run-workflow --trigger=cron')
    ->dailyAt('09:00')
    ->withoutOverlapping();
