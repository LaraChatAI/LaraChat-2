<?php

namespace App\Http\Controllers;

use App\Jobs\CopyRepositoryToHotJob;
use App\Jobs\InitializeConversationSessionJob;
use App\Jobs\SendClaudeMessageJob;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Webhook-Signature');

        if (!$this->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Webhook signature verification failed');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('Webhook received invalid JSON payload');
            return response()->json(['error' => 'Invalid JSON payload'], 400);
        }

        // Validate required fields
        if (empty($data['message'])) {
            return response()->json(['error' => 'Missing required field: message'], 422);
        }

        try {
            // Get the user for the conversation - either from the webhook data or use a default webhook user
            $user = null;
            if (!empty($data['user_email'])) {
                $user = User::where('email', $data['user_email'])->first();
            }
            
            // If no user found, use or create a default webhook user
            if (!$user) {
                $user = User::firstOrCreate(
                    ['email' => 'webhook@system.local'],
                    [
                        'name' => 'Webhook System',
                        'password' => bcrypt(bin2hex(random_bytes(32))), // Random password
                    ]
                );
            }

            $project_id = uniqid();
            $message = $data['message'];
            $repository = $data['repository'] ?? null;
            
            // Get base project directory from .env
            $baseProjectDirectory = env('PROJECTS_DIRECTORY', 'app/private/repositories');
            $projectDirectory = rtrim($baseProjectDirectory, '/') . '/' . $project_id;

            // Create conversation
            $conversation = Conversation::create([
                'user_id' => $user->id,
                'title' => substr($message, 0, 100) . (strlen($message) > 100 ? '...' : ''),
                'message' => $message,
                'claude_session_id' => null, // Let Claude generate this
                'project_directory' => $projectDirectory,
                'repository' => $repository,
                'filename' => 'claude-sessions/' . date('Y-m-d\TH-i-s') . '-session-' . $project_id . '.json',
                'is_processing' => true, // Mark as processing when created
            ]);

            // Dispatch jobs to process the conversation
            Bus::chain([
                new InitializeConversationSessionJob($conversation, $message),
                new SendClaudeMessageJob($conversation, $message)
            ])->dispatch();

            if ($repository) {
                CopyRepositoryToHotJob::dispatch($repository);
            }

            Log::info('Webhook created new conversation', [
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'repository' => $repository,
            ]);

            return response()->json([
                'status' => 'success',
                'conversation_id' => $conversation->id,
                'message' => 'Conversation created successfully',
            ], 201);

        } catch (\Exception $e) {
            Log::error('Webhook conversation creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Failed to create conversation'], 500);
        }
    }

    private function verifyWebhookSignature($payload, $signature)
    {
        if (empty($signature)) {
            return false;
        }

        $secret = config('services.webhook.secret');
        if (empty($secret)) {
            Log::warning('Webhook secret not configured');
            return false;
        }

        // Support both plain secret comparison and HMAC signatures
        // For HMAC: signature should be in format "sha256=<hash>"
        if (strpos($signature, 'sha256=') === 0) {
            $expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);
            return hash_equals($expected, $signature);
        }

        // For simple secret comparison
        return hash_equals($secret, $signature);
    }
}