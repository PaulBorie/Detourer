<?php

namespace App\Events;

use App\Models\RemoveBackgroundTask;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RemoveBackgroundTaskCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    /**
     * Create a new event instance.
     */
    public function __construct(public RemoveBackgroundTask $removeBackgroundTask)
    {
    }

    public function broadcastWith(): array {
        return ['modifiedImageFilename' => $this->removeBackgroundTask->modifiedImageFilename];
    }

    public function broadcastOn(): array
    {   
        return[
            new Channel('rembgtask.'.$this->removeBackgroundTask->uuid) 
        ];
    }
}
