<?php

namespace App\Jobs;

use App\Models\Conversation;
use App\Services\ClaudeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendClaudeMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Conversation $conversation;
    protected string $message;

    public function __construct(Conversation $conversation, string $message)
    {
        $this->conversation = $conversation;
        $this->message = $message;
    }

    public function handle(): void
    {
        try {
            // Validate conversation exists and is valid
            if (!$this->conversation->exists) {
                Log::warning('Conversation no longer exists', [
                    'conversation_id' => $this->conversation->id
                ]);
                return;
            }

            // Check if project directory exists (if specified)
            if ($this->conversation->project_directory) {
                // If project_directory starts with absolute path, use it directly
                // Otherwise treat it as relative to storage_path
                if (str_starts_with($this->conversation->project_directory, '/')) {
                    $projectPath = $this->conversation->project_directory;
                } else {
                    $projectPath = storage_path($this->conversation->project_directory);
                }
                
                if (!is_dir($projectPath)) {
                    Log::error('Project directory does not exist', [
                        'conversation_id' => $this->conversation->id,
                        'project_directory' => $this->conversation->project_directory,
                        'full_path' => $projectPath
                    ]);
                    
                    $this->conversation->update([
                        'is_processing' => false,
                        'error_message' => 'Project directory not found: ' . $this->conversation->project_directory
                    ]);
                    return;
                }
            }

            // Filename should already be set by the controller
            $filename = $this->conversation->filename;
            
            // If for some reason it's not set, generate it
            if (!$filename) {
                $timestamp = now()->format('Y-m-d\TH-i-s');
                $tempId = substr(uniqid(), -12);
                $filename = "claude-sessions/{$timestamp}-session-{$tempId}.json";
                $this->conversation->update(['filename' => $filename]);
                
                // Also save the user message if we had to generate filename
                ClaudeService::saveUserMessage(
                    $this->message,
                    $filename,
                    $this->conversation->claude_session_id,
                    $this->conversation->project_directory
                );
            }
            // Note: User message is already saved by the controller synchronously

            // Create a progress callback to update the conversation in real-time
            $progressCallback = function ($type, $data) {
                if ($type === 'sessionId' && !$this->conversation->claude_session_id) {
                    $this->conversation->update(['claude_session_id' => $data]);
                    Log::info('Updated conversation with session ID', [
                        'conversation_id' => $this->conversation->id,
                        'sessionId' => $data
                    ]);
                } elseif ($type === 'response') {
                    // Update the conversation's updated_at timestamp to signal new content
                    $this->conversation->touch();
                    
                    Log::debug('Progress update', [
                        'conversation_id' => $this->conversation->id,
                        'filename' => $data['filename'],
                        'responseCount' => $data['responseCount']
                    ]);
                }
            };

            // Use conversation mode to determine permission mode
            // If mode is 'plan', use 'plan' permission mode; otherwise use 'bypassPermissions'
            $permissionMode = $this->conversation->mode === 'plan' ? 'plan' : 'bypassPermissions';
            
            $result = ClaudeService::processInBackground(
                $this->message,
                '--permission-mode ' . $permissionMode,
                $this->conversation->claude_session_id,
                $filename,
                $this->conversation->project_directory,
                $progressCallback
            );

            // Update conversation with the session ID if extracted (in case callback didn't catch it)
            if ($result['sessionId'] && !$this->conversation->fresh()->claude_session_id) {
                $this->conversation->update(['claude_session_id' => $result['sessionId']]);
            }

            // Filename is already set at the beginning, no need to update it again
            
            // Add a small delay to ensure the file has been completely written
            // This prevents the frontend from seeing is_processing = false before the response is ready
            sleep(1);
            
            // Capture git diff and save to .git folder
            if ($this->conversation->project_directory) {
                $this->captureGitDiff();
            }
            
            // Mark conversation as no longer processing
            $this->conversation->update(['is_processing' => false]);

            Log::info('Background Claude processing completed', [
                'conversation_id' => $this->conversation->id,
                'success' => $result['success'],
                'sessionId' => $result['sessionId']
            ]);
        } catch (\Exception $e) {
            // In case of error, mark as not processing
            $this->conversation->update(['is_processing' => false]);

            Log::error('Error in SendClaudeMessageJob', [
                'conversation_id' => $this->conversation->id,
                'error' => $e->getMessage()
            ]);

            throw $e; // Re-throw to let the queue handle retry logic
        }
    }

    protected function captureGitDiff(): void
    {
        try {
            // Determine project path
            if (str_starts_with($this->conversation->project_directory, '/')) {
                $projectPath = $this->conversation->project_directory;
            } else {
                $projectPath = storage_path($this->conversation->project_directory);
            }
            
            // Check if it's a git repository
            if (!is_dir($projectPath . '/.git')) {
                Log::info('Project directory is not a git repository, skipping diff capture', [
                    'conversation_id' => $this->conversation->id,
                    'project_path' => $projectPath
                ]);
                return;
            }
            
            // Get the default branch from origin
            $defaultBranch = $this->getDefaultBranch($projectPath);
            
            // Use fixed filename for the diff
            $diffFilename = "project.diff";
            $diffPath = $projectPath . '/.git/' . $diffFilename;
            
            // Execute git diff command comparing current branch to origin's default branch
            $command = sprintf(
                'cd %s && git diff --no-ext-diff --no-color origin/%s...HEAD > %s 2>&1',
                escapeshellarg($projectPath),
                escapeshellarg($defaultBranch),
                escapeshellarg($diffPath)
            );
            
            $output = [];
            $returnCode = null;
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0) {
                // Check if diff file has content
                if (filesize($diffPath) > 0) {
                    Log::info('Git diff captured and saved', [
                        'conversation_id' => $this->conversation->id,
                        'diff_path' => $diffPath,
                        'size' => filesize($diffPath),
                        'default_branch' => $defaultBranch
                    ]);
                } else {
                    // Remove empty diff file
                    @unlink($diffPath);
                    Log::info('No changes detected in git diff', [
                        'conversation_id' => $this->conversation->id,
                        'default_branch' => $defaultBranch
                    ]);
                }
            } else {
                Log::warning('Failed to capture git diff', [
                    'conversation_id' => $this->conversation->id,
                    'return_code' => $returnCode,
                    'output' => implode("\n", $output),
                    'default_branch' => $defaultBranch
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error capturing git diff', [
                'conversation_id' => $this->conversation->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    protected function getDefaultBranch(string $workingDirectory): string
    {
        try {
            // Try to get the default branch from remote HEAD
            $command = sprintf('cd %s && git symbolic-ref refs/remotes/origin/HEAD 2>/dev/null', escapeshellarg($workingDirectory));
            $output = trim(shell_exec($command) ?? '');
            
            if ($output && str_contains($output, 'refs/remotes/origin/')) {
                return str_replace('refs/remotes/origin/', '', $output);
            }
        } catch (\Exception $e) {
            // If that fails, try to get the current branch
            try {
                $command = sprintf('cd %s && git rev-parse --abbrev-ref HEAD 2>/dev/null', escapeshellarg($workingDirectory));
                $branch = trim(shell_exec($command) ?? '');
                if ($branch && $branch !== 'HEAD') {
                    return $branch;
                }
            } catch (\Exception $e2) {
                // Ignore and fall back to default
            }
        }
        
        // Fall back to 'main' as it's the most common default now
        return 'main';
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendClaudeMessageJob failed permanently', [
            'conversation_id' => $this->conversation->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        $this->conversation->update([
            'is_processing' => false,
            'error_message' => 'Failed to send message to Claude: ' . $exception->getMessage()
        ]);
    }
}
