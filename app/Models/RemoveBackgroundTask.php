<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemoveBackgroundTask extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */         
    protected $fillable = [
        'sessionId',
        'userIp',
        'uuid',
        'clientOriginalImageName',
        'imageHashName',
        'imageMimeType',
        'imageSize',
        'originalImageExtension',
        'originalImageFilename',
        'originalImageTemporaryUrl',
        'modifiedImageExtension',
        'modifiedImageFilename',
        'modifiedImageTemporaryUrl',
        'downloadModifiedImageFilename',
        'status',
        'errorClass',
        'errorMessage',
        'errorTrace',
    ];

}
