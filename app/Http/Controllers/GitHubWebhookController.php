<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessGitHubWebhook;
use App\Models\GitHubWebhookLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GitHubWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Hub-Signature-256');

        if (!$this->verifyWebhookSignature($payload, $signature)) {
            Log::warning('GitHub webhook signature verification failed');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $event = $request->header('X-GitHub-Event');
        $data = json_decode($payload, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('GitHub webhook invalid JSON', [
                'error' => json_last_error_msg(),
                'payload' => substr($payload, 0, 100),
            ]);
            return response()->json(['error' => 'Invalid JSON payload'], 400);
        }

        $webhookLog = GitHubWebhookLog::create([
            'event_type' => $event,
            'delivery_id' => $request->header('X-GitHub-Delivery'),
            'repository' => $data['repository']['full_name'] ?? null,
            'payload' => $data,
            'status' => 'processing',
        ]);

        Log::info('GitHub webhook received', [
            'event' => $event,
            'delivery_id' => $request->header('X-GitHub-Delivery'),
        ]);

        try {
            // Dispatch job for async processing
            ProcessGitHubWebhook::dispatch($webhookLog, $event, $data);

            return response()->json(['status' => 'queued'], 200);
        } catch (\Exception $e) {
            Log::error('GitHub webhook processing failed', [
                'event' => $event,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $webhookLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    private function verifyWebhookSignature($payload, $signature)
    {
        if (empty($signature)) {
            return false;
        }

        $secret = config('services.github.webhook_secret');
        if (empty($secret)) {
            Log::warning('GitHub webhook secret not configured');
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }

    private function processWebhookEvent($event, $data)
    {
        switch ($event) {
            case 'push':
                $this->handlePushEvent($data);
                break;
            case 'pull_request':
                $this->handlePullRequestEvent($data);
                break;
            case 'issues':
                $this->handleIssuesEvent($data);
                break;
            case 'release':
                $this->handleReleaseEvent($data);
                break;
            case 'create':
                $this->handleCreateEvent($data);
                break;
            case 'delete':
                $this->handleDeleteEvent($data);
                break;
            case 'star':
                $this->handleStarEvent($data);
                break;
            case 'fork':
                $this->handleForkEvent($data);
                break;
            default:
                Log::info('Unhandled GitHub webhook event', ['event' => $event]);
        }
    }

    private function handlePushEvent($data)
    {
        Log::info('Push event received', [
            'repository' => $data['repository']['full_name'] ?? 'unknown',
            'pusher' => $data['pusher']['name'] ?? 'unknown',
            'commits' => count($data['commits'] ?? []),
            'ref' => $data['ref'] ?? null,
        ]);
    }

    private function handlePullRequestEvent($data)
    {
        Log::info('Pull request event received', [
            'action' => $data['action'] ?? 'unknown',
            'repository' => $data['repository']['full_name'] ?? 'unknown',
            'number' => $data['pull_request']['number'] ?? null,
            'title' => $data['pull_request']['title'] ?? null,
            'user' => $data['pull_request']['user']['login'] ?? 'unknown',
        ]);
    }

    private function handleIssuesEvent($data)
    {
        Log::info('Issues event received', [
            'action' => $data['action'] ?? 'unknown',
            'repository' => $data['repository']['full_name'] ?? 'unknown',
            'number' => $data['issue']['number'] ?? null,
            'title' => $data['issue']['title'] ?? null,
            'user' => $data['issue']['user']['login'] ?? 'unknown',
        ]);
    }

    private function handleReleaseEvent($data)
    {
        Log::info('Release event received', [
            'action' => $data['action'] ?? 'unknown',
            'repository' => $data['repository']['full_name'] ?? 'unknown',
            'tag_name' => $data['release']['tag_name'] ?? null,
            'name' => $data['release']['name'] ?? null,
            'author' => $data['release']['author']['login'] ?? 'unknown',
        ]);
    }

    private function handleCreateEvent($data)
    {
        Log::info('Create event received', [
            'ref_type' => $data['ref_type'] ?? 'unknown',
            'ref' => $data['ref'] ?? null,
            'repository' => $data['repository']['full_name'] ?? 'unknown',
            'sender' => $data['sender']['login'] ?? 'unknown',
        ]);
    }

    private function handleDeleteEvent($data)
    {
        Log::info('Delete event received', [
            'ref_type' => $data['ref_type'] ?? 'unknown',
            'ref' => $data['ref'] ?? null,
            'repository' => $data['repository']['full_name'] ?? 'unknown',
            'sender' => $data['sender']['login'] ?? 'unknown',
        ]);
    }

    private function handleStarEvent($data)
    {
        Log::info('Star event received', [
            'action' => $data['action'] ?? 'unknown',
            'repository' => $data['repository']['full_name'] ?? 'unknown',
            'starred_at' => $data['starred_at'] ?? null,
            'sender' => $data['sender']['login'] ?? 'unknown',
        ]);
    }

    private function handleForkEvent($data)
    {
        Log::info('Fork event received', [
            'repository' => $data['repository']['full_name'] ?? 'unknown',
            'forkee' => $data['forkee']['full_name'] ?? 'unknown',
            'owner' => $data['forkee']['owner']['login'] ?? 'unknown',
        ]);
    }
}
