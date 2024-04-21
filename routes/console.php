<?php

use App\Console\Commands\CleanOldS3Images;
use Illuminate\Support\Facades\Schedule;


# Clean images older than 1 week (10080 minutes) from s3 bucket. Do this everyday
Schedule::command(CleanOldS3Images::class, ['10080'])->dailyAt('4:00');


