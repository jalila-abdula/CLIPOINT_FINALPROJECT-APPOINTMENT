<?php

use App\Models\Appointment;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('appointments:mark-no-shows', function () {
    $markedAppointments = Appointment::markReadyForNoShow();

    $this->info("Marked {$markedAppointments} appointment(s) as no-show.");
})->purpose('Automatically mark missed appointments as no-show.');

Schedule::command('appointments:mark-no-shows')->everyMinute();
