<?php

namespace App\Livewire;

use Exception;
use ReflectionClass;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Livewire\Attributes\On; 
use Livewire\WithFileUploads;
use Livewire\Attributes\Locked;
use App\Jobs\RemoveBackgroundJob;
use Illuminate\Support\Facades\Log;
use App\Models\RemoveBackgroundTask;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


class UploadPhoto extends Component
{
    use WithFileUploads;

    #[Locked]
    public RemoveBackgroundTask $task;


    #[Locked]
    public $isProcessingTask;

    public $image; 

    public function mount() {
        $this->isProcessingTask = false;
    }
    	
    public function updatedImage(Request $request)
    {   
        if (!$this->isProcessingTask) {
            try {
                $this->validate(
                    ['image' => 'required|image|mimes:jpeg,png,jpg,webp,bmp|max:' . Config::get('app.max_image_size')] # 50Mo it is also needed to set this to php.ini during deployment
                );
            } catch(ValidationException $e) {
                $this->dispatch('notify', ['message' => $e->getMessage(), 'title' => 'Image Upload Failure', 'type' => 'error']);
                return;
            }
            try {
                $this->isProcessingTask = true;
                $filename =  $this->image->store('original', 's3');
                $temporaryUrl = Storage::disk('minio-temporaryurls')->temporaryUrl($filename, now()->addMinutes(20));

                $task = RemoveBackgroundTask::create([
                    'sessionId' => $request->session()->getId(),
                    'userIp' => $request->getClientIp(),
                    'uuid' => Str::uuid(),
                    'clientOriginalImageName' => $this->image->getClientOriginalName(),
                    'imageHashName' => $this->image->hashName(),
                    'imageMimeType' => $this->image->getMimeType(),
                    'imageSize' => $this->image->getSize(),
                    'originalImageExtension' => $this->image->extension(),
                    'originalImageFilename' => $filename,
                    'originalImageTemporaryUrl' => $temporaryUrl,
                    'status' => 'uploaded'           
                ]); 
                $this->task = $task;
                $this->dispatch('task:created', $task->uuid);
                RemoveBackgroundJob::dispatch($task);
            } catch (Exception $e) {
                $this->dispatch('notify', ['message' => 'Unexpected file upload error', 'title' => 'Image Upload Failure', 'type' => 'error']);
            }
            
        } else {
            $this->dispatch('notify', ['message' => 'Failed to remove background. There is already another task running', 'title' => 'Concurrent Remove Background Task Request', 'type' => 'error']);
        }
        $this->image->delete();
       }

 
    #[On('removeBackgroundTaskCompleted')] # OR task failed
    public function unlockOtherTask() {
        $this->isProcessingTask = false;
        $this->dispatch('notify', ['message' => 'Background has been removed successfully', 'title' => 'Success', 'type' => 'success']);

    }

    #[On('removeBackgroundTaskFailed')]
    public function failedTask($exceptionMessage, $exceptionTitle) {
        $this->reset('task');
        $this->reset('image');
        $this->isProcessingTask = false;
        $this->dispatch('notify', ['message' => $exceptionMessage, 'title' => $exceptionTitle, 'type' => 'error']);
    }

    #[On('upload:errored')]
    public function uploadErrored() { 
        $this->dispatch('notify', ['message' => 'Make sure it has an appropriate image format (jpeg,png,jpg,webp,bmp) and that it does not exceed 50mo', 'title' => 'Image upload Failure', 'type' => 'error']);
        $this->reset('task');
        $this->reset('image');
        $this->isProcessingTask = false;
    }

    public function download()
    {   
        return Storage::disk('s3')->download($this->task->modifiedImageFilename, $this->task->downloadModifiedImageFilename);
    }

    public function cancelRemoveBackgroundTask() {
        $this->reset('task');
        $this->reset('image');
        $this->isProcessingTask = false;   
    }


    public function render()
    {   
        return view('livewire.upload-photo');
    }
}
