<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CleanOldS3Images extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:clean {min=5} : The number of minutes to keep the images.';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean user uploaded images that are older than the number of minutes specified as the first argument.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $minutes = $this->argument('min');

        $allFiles = Storage::disk('s3')->allFiles();

        foreach ($allFiles as $file) {
            $lastModified = Storage::disk('s3')->lastModified($file);
            $lastModifiedDateTime = Carbon::createFromTimestamp($lastModified);

            if ($lastModifiedDateTime->lessThan(Carbon::now()->subMinutes($minutes))) {
                Storage::disk('s3')->delete($file);
            }
        }
        $this->info('Old files have been deleted.');    
        return 0;        
    }
}
