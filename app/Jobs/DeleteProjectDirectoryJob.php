<?php

namespace App\Jobs;

use App\Models\Conversation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class DeleteProjectDirectoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $conversation;

    public function __construct(Conversation $conversation)
    {
        $this->conversation = $conversation;
    }

    public function handle()
    {
        $projectDirectory = $this->conversation->project_directory;
        
        if (empty($projectDirectory)) {
            Log::warning('DeleteProjectDirectoryJob: Project directory is empty for conversation ' . $this->conversation->id);
            return;
        }

        if (!str_starts_with($projectDirectory, '/')) {
            $projectDirectory = storage_path($projectDirectory);
        }

        if (File::exists($projectDirectory)) {
            try {
                File::deleteDirectory($projectDirectory);
                Log::info('DeleteProjectDirectoryJob: Successfully deleted project directory: ' . $projectDirectory);
            } catch (\Exception $e) {
                Log::error('DeleteProjectDirectoryJob: Failed to delete project directory: ' . $projectDirectory . '. Error: ' . $e->getMessage());
                throw $e;
            }
        } else {
            Log::info('DeleteProjectDirectoryJob: Project directory does not exist: ' . $projectDirectory);
        }
    }
}