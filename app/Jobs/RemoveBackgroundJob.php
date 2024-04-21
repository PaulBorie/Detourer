<?php

namespace App\Jobs;

use Exception;
use Throwable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use App\Models\RemoveBackgroundTask;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\RemoveBackgroundTaskFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\RemoveBackgroundTaskCompleted;
use App\Exceptions\SerializableExceptionWrapper;
use App\Exceptions\RemoveBackgroundJobFailureException;
use App\Exceptions\TooManyRemoveBackgroundJobException;
use Illuminate\Support\Facades\App;




class RemoveBackgroundJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries;

    public $failOnTimeout = true;
    
    public $timeout;

    public $maxRembgJobPerWindow;

    public $rembgJobWindow;

    public $rembgPath;

    public $rembgModelPath;
    
    /**
     * Create a new job instance.
     */
    public function __construct(public RemoveBackgroundTask $removeBackgroundTask)
    {
        $this->removeBackgroundTask = $removeBackgroundTask;
        $this->timeout = Config::get('app.rembg_process_timeout');
        $this->tries = Config::get('app.rembg_job_retries');
        $this->maxRembgJobPerWindow = Config::get('app.max_rembg_job_per_window');
        $this->rembgJobWindow = Config::get('app.rembg_job_window');
        $this->rembgPath = Config::get('app.rembg_path');
        $this->rembgModelPath = Config::get('app.rembg_model_path');
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(?Throwable $exception)
    {
        $this->removeBackgroundTask->update([
            'status' => 'failed',
            'errorClass' => get_class($exception),
            'errorMessage' => $exception?->getMessage() ?? 'No message available',
            'errorTrace' => $exception?->getTraceAsString() ?? 'No trace available',
        ]);
        $serializableThrowable = new SerializableExceptionWrapper($exception);
        RemoveBackgroundTaskFailed::dispatch($this->removeBackgroundTask, $serializableThrowable);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $throttleKey = 'rembg-' . $this->removeBackgroundTask->userIp;
        Redis::throttle($throttleKey)->allow($this->maxRembgJobPerWindow)->every($this->rembgJobWindow)->then(function () {
            $originalImageFilename = $this->removeBackgroundTask->originalImageFilename;
            $modifiedImageFilenameWithoutExtension = pathinfo($originalImageFilename, PATHINFO_FILENAME);
            $modifiedImageFilename = "modified/nobg-$modifiedImageFilenameWithoutExtension.png";
            $downloadModifiedImageFilename = "nobg-" . $this->removeBackgroundTask->clientOriginalImageName;
            if (!Storage::disk('s3')->exists($originalImageFilename)) {
                $this->fail(new RemoveBackgroundJobFailureException("File $originalImageFilename does not exist", 404, null));
            }
        
            $originalImageContent = Storage::disk('s3')->get($originalImageFilename);
            //$process = new  Process(['rembg', 'i', '>', '/home/taz/ai-background-remover/output.png'])
            
            $rembgPath = $this->rembgPath;
            $rembgModelPath = $this->rembgModelPath;

            if($rembgModelPath) {
                $command = "$rembgPath i -m u2net -x '{\"model_path\": \"$rembgModelPath\"}'";
            } else {
                $command = "$rembgPath i";
            }

            if (App::environment('local')) {
                Log::info($this->removeBackgroundTask->originalImageFilename);
                Log::info($this->timeout);
                Log::info($this->rembgPath);
                Log::info($this->tries);
                Log::info($this->rembgJobWindow);
                Log::info($this->maxRembgJobPerWindow);
                Log::info($command);
            }
            
            $process = Process::fromShellCommandline($command);
            $process->setTimeout($this->timeout);
            $process->setInput($originalImageContent);
            $process->run();
        
            if (!$process->isSuccessful()) {
                // This exception will not cause a retry
                $this->fail(new RemoveBackgroundJobFailureException($process->getErrorOutput(), 500, null));
            } else {
                Storage::disk('s3')->put($modifiedImageFilename, $process->getOutput());
                $temporaryUrl = Storage::disk('minio-temporaryurls')->temporaryUrl($modifiedImageFilename, now()->addMinutes(20));
                $this->removeBackgroundTask->update([
                    'modifiedImageExtension' => 'png',
                    'modifiedImageFilename' => $modifiedImageFilename,
                    'modifiedImageTemporaryUrl' => $temporaryUrl,
                    'downloadModifiedImageFilename' => $downloadModifiedImageFilename,
                    'status' => 'completed',
                ]);
                RemoveBackgroundTaskCompleted::dispatch($this->removeBackgroundTask);
            } 

        }, function () {
            // Could not obtain lock...
            return $this->fail(new TooManyRemoveBackgroundJobException('You tried too many remove background tasks. Retry later', 429, null));
        });
        
    }

     /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return int
     */
    public function backoff()
    {
        return pow(2, $this->attempts()); //Maybe using throttleing would be better than an exponential backoff
    }
}
