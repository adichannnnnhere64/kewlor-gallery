<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;


Schedule::command('queue:work  --queue=media --stop-when-empty --timeout=300')->name('workit')
    ->runInBackground()
    ->everyFiveSeconds();

Schedule::command('queue:retry  --queue=media')->name('retry')
    ->runInBackground()
    ->everyMinute();
