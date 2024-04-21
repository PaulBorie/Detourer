<?php

namespace App\Events;

use App\Exceptions\SerializableExceptionWrapper;
use App\Models\RemoveBackgroundTask;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RemoveBackgroundTaskFailed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public RemoveBackgroundTask $removeBackgroundTask;

    public SerializableExceptionWrapper $exception;
    /**
     * Create a new event instance.
     */
    public function __construct(RemoveBackgroundTask $removeBackgroundTask, SerializableExceptionWrapper $exception)
    {   

        $this->removeBackgroundTask = $removeBackgroundTask;
        $this->exception = $exception;   
    }

    public function broadcastWith(): array {
        
        if ($this->exception->getClass() === "Illuminate\Process\Exceptions\ProcessTimedOutException" || $this->exception->getClass() === "Symfony\Component\Process\Exception\ProcessTimedOutException") {
            return ['exceptionMessage' => 'The remove background task timed out. The server might be processing too many requests. Please try again later.',
                    'exceptionTitle' => 'Remove Background task timeout',    
            ];
        
        } else if ($this->exception->getClass() === "App\Exceptions\RemoveBackgroundJobFailureException") {
            return ['exceptionMessage' => 'The remove background task unexpectedly failed.',
                    'exceptionTitle' => 'Remove Background task failure',    
            ];
        } else if ($this->exception->getClass() === "App\Exceptions\TooManyRemoveBackgroundJobException") {
            return ['exceptionMessage' => $this->exception->getMessage(),
                    'exceptionTitle' => 'Too many remove background tasks',    
            ];
        }
        else {
            return ['exceptionMessage' => 'Unexpected error occured.',
                'exceptionTitle' => 'Remove Background task failure',    
            ];
        }
    }
   

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return[
            new Channel('rembgtask.'.$this->removeBackgroundTask->uuid) 
        ];
    }
}
