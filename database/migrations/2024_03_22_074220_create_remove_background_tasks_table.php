<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('remove_background_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('sessionId')->nullable(false);
            $table->string('userIp', 15)->nullable(false);
            $table->string('uuid')->nullable(false);
            $table->text('clientOriginalImageName')->nullable(false);
            $table->string('imageHashName')->nullable(false);
            $table->string('imageMimeType')->nullable(true);
            $table->string('imageSize')->nullable(false);
            $table->string('originalImageExtension')->nullabl(false);
            $table->string('originalImageFilename')->nullable(false);
            $table->text('originalImageTemporaryUrl')->nullable(false);
            $table->string('modifiedImageExtension')->nullable();
            $table->string('modifiedImageFilename')->nullable();
            $table->text('modifiedImageTemporaryUrl')->nullable();
            $table->string('downloadModifiedImageFilename')->nullable();
            $table->string('status')->nullable(false);
            $table->string('errorClass')->nullable();
            $table->text('errorMessage')->nullable();
            $table->text('errorTrace')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remove_background_tasks');
    }
};
