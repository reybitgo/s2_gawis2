<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Note: Rank advancement is handled synchronously when packages are purchased.
// No scheduled task needed - advancement happens immediately when requirements are met.
