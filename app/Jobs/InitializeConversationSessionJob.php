<?php

namespace App\Jobs;

use App\Models\Conversation;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class InitializeConversationSessionJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected Conversation $conversation,
        protected string $message
    ) {}

    public function handle(): void
    {
        // Initialize session with the user's message (but no Claude response)
        $sessionData = [
            [
                "sessionId" => null,
                'role' => 'user',
                'userMessage' => $this->message,
                'timestamp' => now()->toIso8601String(),
                "isComplete" => false,
                "repositoryPath" => null,
            ]
        ];

        Storage::put($this->conversation->filename, json_encode($sessionData, JSON_PRETTY_PRINT));

        $from = storage_path('app/private/repositories/hot/' . $this->conversation->repository);

        if (!File::exists($from)) {
            CopyRepositoryToHotJob::dispatchSync($this->conversation->repository);
            
            // Check again after the copy job
            if (!File::exists($from)) {
                Log::error('InitializeConversationSessionJob: Repository not found after copy attempt', [
                    'repository' => $this->conversation->repository,
                    'from' => $from,
                ]);
                
                // Mark conversation as failed
                $this->conversation->update(['is_processing' => false]);
                return;
            }
        }

        // If project_directory starts with absolute path from PROJECT_DIRECTORY env, use it directly
        // Otherwise treat it as relative to storage_path
        $projectDir = $this->conversation->project_directory;
        if (str_starts_with($projectDir, '/')) {
            $to = $projectDir;
        } else {
            $to = storage_path($projectDir);
        }

        ray($from, $to);
        
        // Ensure the parent directory exists
        $parentDir = dirname($to);
        if (!File::exists($parentDir)) {
            File::makeDirectory($parentDir, 0755, true);
        }
        
        File::moveDirectory($from, $to, true);

        // Only run git commands if the directory exists after move
        if (!File::exists($to)) {
            Log::error('InitializeConversationSessionJob: Failed to move repository directory', [
                'from' => $from,
                'to' => $to,
                'repository' => $this->conversation->repository,
            ]);
            return;
        }

        // Update the Git repository in the moved directory using system-update script
        try {
            $scriptPath = base_path('scripts/system-update.sh');
            
            // Make sure the script is executable
            if (File::exists($scriptPath)) {
                chmod($scriptPath, 0755);
            }
            
            // Run the system-update script in the project directory
            $result = Process::path($to)
                ->timeout(120) // 2 minutes timeout
                ->run('bash ' . escapeshellarg($scriptPath));
            
            if (!$result->successful()) {
                throw new \RuntimeException("system-update script failed. Output: " . $result->errorOutput());
            }
            
            Log::info('InitializeConversationSessionJob: Updated project repository using system-update script', [
                'repository' => $this->conversation->repository,
                'project_directory' => $this->conversation->project_directory,
            ]);
        } catch (\Exception $e) {
            Log::error('InitializeConversationSessionJob: Failed to update project repository using system-update script', [
                'repository' => $this->conversation->repository,
                'project_directory' => $this->conversation->project_directory,
                'error' => $e->getMessage(),
            ]);
            
            // Mark conversation as failed
            $this->conversation->update(['is_processing' => false]);
            
            // Re-throw the exception to fail the job
            throw $e;
        }
    }

}