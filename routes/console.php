<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;


Schedule::command('queue:work --once --timeout=300 --tries=3')->name('inspire')->withoutOverlapping()->everySecond();
