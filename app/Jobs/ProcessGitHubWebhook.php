<?php

namespace App\Jobs;

use App\Models\GitHubWebhookLog;
use App\Models\Repository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessGitHubWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1 min, 5 min, 15 min

    public function __construct(
        protected GitHubWebhookLog $webhookLog,
        protected string $event,
        protected array $data
    ) {}

    public function handle()
    {
        try {
            switch ($this->event) {
                case 'push':
                    $this->handlePushEvent();
                    break;
                case 'pull_request':
                    $this->handlePullRequestEvent();
                    break;
                case 'issues':
                    $this->handleIssuesEvent();
                    break;
                case 'release':
                    $this->handleReleaseEvent();
                    break;
                default:
                    Log::info('Unhandled GitHub webhook event', ['event' => $this->event]);
            }
            
            $this->webhookLog->update(['status' => 'success']);
        } catch (\Exception $e) {
            $this->webhookLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
    
    private function handlePushEvent()
    {
        $repositoryName = $this->data['repository']['full_name'] ?? null;
        
        if ($repositoryName) {
            $repository = Repository::where('name', $repositoryName)
                ->orWhere('path', 'LIKE', '%' . $repositoryName)
                ->first();
                
            if ($repository && $this->data['ref'] === 'refs/heads/' . ($repository->default_branch ?? 'main')) {
                // TODO: Uncomment when PullRepository job is implemented
                // dispatch(new \App\Jobs\PullRepository($repository));
                
                Log::info('Auto-pull would be triggered for repository (not yet implemented)', [
                    'repository' => $repositoryName,
                    'ref' => $this->data['ref'],
                ]);
            }
        }
        
        Log::info('Push event processed', [
            'repository' => $repositoryName,
            'pusher' => $this->data['pusher']['name'] ?? 'unknown',
            'commits' => count($this->data['commits'] ?? []),
        ]);
    }
    
    private function handlePullRequestEvent()
    {
        $action = $this->data['action'] ?? 'unknown';
        $repository = $this->data['repository']['full_name'] ?? 'unknown';
        $number = $this->data['pull_request']['number'] ?? null;
        
        Log::info('Pull request event processed', [
            'action' => $action,
            'repository' => $repository,
            'number' => $number,
            'title' => $this->data['pull_request']['title'] ?? null,
        ]);
        
        // You can add custom logic here, such as:
        // - Notify users about PR updates
        // - Run automated checks
        // - Update PR status in your database
    }
    
    private function handleIssuesEvent()
    {
        $action = $this->data['action'] ?? 'unknown';
        $repository = $this->data['repository']['full_name'] ?? 'unknown';
        $number = $this->data['issue']['number'] ?? null;
        
        Log::info('Issues event processed', [
            'action' => $action,
            'repository' => $repository,
            'number' => $number,
            'title' => $this->data['issue']['title'] ?? null,
        ]);
        
        // You can add custom logic here, such as:
        // - Track issues in your system
        // - Send notifications
        // - Auto-assign issues
    }
    
    private function handleReleaseEvent()
    {
        $action = $this->data['action'] ?? 'unknown';
        $repository = $this->data['repository']['full_name'] ?? 'unknown';
        $tagName = $this->data['release']['tag_name'] ?? null;
        
        Log::info('Release event processed', [
            'action' => $action,
            'repository' => $repository,
            'tag_name' => $tagName,
            'name' => $this->data['release']['name'] ?? null,
        ]);
        
        // You can add custom logic here, such as:
        // - Deploy releases
        // - Send release notifications
        // - Update changelogs
    }
    
    public function failed(\Throwable $exception)
    {
        Log::error('GitHub webhook job failed', [
            'webhook_log_id' => $this->webhookLog->id,
            'event' => $this->event,
            'error' => $exception->getMessage(),
        ]);
        
        $this->webhookLog->update([
            'status' => 'failed',
            'error_message' => 'Job failed after ' . $this->attempts() . ' attempts: ' . $exception->getMessage(),
        ]);
    }
}