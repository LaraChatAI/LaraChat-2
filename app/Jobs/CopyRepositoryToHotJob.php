<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class CopyRepositoryToHotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 120, 300];

    protected string $repository;

    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    public function handle(): void
    {
        // Skip if repository is blank/empty
        if (empty($this->repository)) {
            Log::info('CopyRepositoryToHot: Skipping blank repository');
            return;
        }

        $basePath = storage_path('app/private/repositories/base/' . $this->repository);
        $hotPath = storage_path('app/private/repositories/hot/' . $this->repository);

        if (!is_dir($basePath)) {
            Log::error('CopyRepositoryToHot: Missing repository directory', [
                'repository' => $this->repository,
                'path' => $basePath,
            ]);

            return;
        }

        try {
            // Get the default branch name
            $defaultBranch = $this->getDefaultBranch($basePath);
            
            $this->runGitCommand('checkout ' . $defaultBranch, $basePath);
            
            // Try to fetch and reset, but don't fail if it doesn't work (e.g., in test environments)
            try {
                $this->runGitCommand('fetch', $basePath);
                $this->runGitCommand('reset --hard origin/' . $defaultBranch, $basePath);
            } catch (\Exception $e) {
                Log::info('CopyRepositoryToHot: Could not fetch/reset repository (may be a test environment)', [
                    'repository' => $this->repository,
                    'error' => $e->getMessage(),
                ]);
            }
            
            Log::info('CopyRepositoryToHot: Prepared base repository', [
                'repository' => $this->repository,
            ]);
        } catch (ProcessFailedException $e) {
            Log::warning('CopyRepositoryToHot: Could not update base repository, copying as-is', [
                'repository' => $this->repository,
                'error' => $e->getMessage(),
            ]);
            // Continue with copying even if git operations fail
        }

        File::copyDirectory($basePath, $hotPath);
        
        Log::info('CopyRepositoryToHot: Successfully copied repository to hot', [
            'repository' => $this->repository,
        ]);
    }
    
    protected function runGitCommand(string $command, string $workingDirectory): Process
    {
        $fullCommand = 'git ' . $command;
        
        $process = Process::fromShellCommandline($fullCommand);
        $process->setWorkingDirectory($workingDirectory);
        $process->setTimeout(60);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process;
    }
    
    protected function getDefaultBranch(string $workingDirectory): string
    {
        try {
            // Try to get the default branch from remote HEAD
            $process = $this->runGitCommand('symbolic-ref refs/remotes/origin/HEAD', $workingDirectory);
            $output = trim($process->getOutput());
            
            if ($output && str_contains($output, 'refs/remotes/origin/')) {
                return str_replace('refs/remotes/origin/', '', $output);
            }
        } catch (ProcessFailedException $e) {
            // If that fails, try to get the current branch
            try {
                $process = $this->runGitCommand('rev-parse --abbrev-ref HEAD', $workingDirectory);
                $branch = trim($process->getOutput());
                if ($branch && $branch !== 'HEAD') {
                    return $branch;
                }
            } catch (ProcessFailedException $e2) {
                // Ignore and fall back to default
            }
        }
        
        // Fall back to 'main' as it's the most common default now
        return 'main';
    }
}
