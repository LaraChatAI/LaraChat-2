<?php

namespace App\Http\Controllers;

use App\Jobs\SendClaudeMessageJob;
use App\Models\Conversation;
use App\Services\ClaudeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @group Claude AI Integration
 * 
 * APIs for interacting with Claude AI assistant
 */
class ClaudeController extends Controller
{
    /**
     * Stop Claude process
     * 
     * Stop a running Claude AI process
     * 
     * @authenticated
     * 
     * @bodyParam processId string required The ID of the process to stop. Example: proc_123456
     * 
     * @response 200 scenario="Success" {
     *   "success": true,
     *   "message": "Process stopped successfully"
     * }
     * 
     * @response 200 scenario="Process Not Found" {
     *   "success": false,
     *   "message": "Process not found or already stopped"
     * }
     */
    public function stop(Request $request)
    {
        $request->validate([
            'processId' => 'required|string',
        ]);

        $processId = $request->input('processId');
        $success = ClaudeService::stopProcess($processId);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Process stopped successfully' : 'Process not found or already stopped'
        ]);
    }

    /**
     * Send message to Claude
     * 
     * Send a message to Claude AI and receive a response
     * 
     * @authenticated
     * 
     * @bodyParam prompt string required The message to send to Claude. Example: Can you help me debug this code?
     * @bodyParam sessionId string optional The Claude session ID. Example: session_abc123
     * @bodyParam sessionFilename string optional The session filename. Example: claude-sessions/2024-01-15T10-30-00-session-abc123.json
     * @bodyParam conversationId integer optional The conversation ID. Example: 1
     * @bodyParam repositoryPath string optional The repository path for context. Example: /projects/my-app
     * 
     * @response 200 scenario="Success" {
     *   "success": true,
     *   "message": "Message queued for processing",
     *   "conversationId": 1,
     *   "sessionFilename": "claude-sessions/2024-01-15T10-30-00-session-abc123.json"
     * }
     * 
     * @response 403 scenario="Unauthorized" {
     *   "message": "Forbidden"
     * }
     * 
     * @response 422 scenario="Validation Error" {
     *   "message": "The prompt field is required.",
     *   "errors": {
     *     "prompt": ["The prompt field is required."]
     *   }
     * }
     * 
     * @throws Exception
     */
    public function store(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string',
            'sessionId' => 'nullable|string',
            'sessionFilename' => 'nullable|string',
            'conversationId' => 'nullable|integer|exists:conversations,id',
            'repositoryPath' => 'nullable|string',
            'mode' => 'nullable|string|in:plan,bypassPermissions',
        ]);

        $conversationId = $request->input('conversationId');
        
        if ($conversationId) {
            /** @var Conversation $conversation */
            $conversation = Conversation::findOrFail($conversationId);
            
            // Check if user owns this conversation
            if ($conversation->user_id !== auth()->id()) {
                abort(403);
            }
        } else {
            // Create a new conversation if none exists
            $timestamp = now()->format('Y-m-d\TH-i-s');
            $tempId = substr(uniqid(), -12);
            $filename = "claude-sessions/{$timestamp}-session-{$tempId}.json";
            
            $conversation = Conversation::create([
                'user_id' => auth()->id(),
                'message' => $request->input('prompt'),
                'filename' => $filename,
                'is_processing' => true,
                'claude_session_id' => $request->input('sessionId'),
                'project_directory' => $request->input('repositoryPath'),
                'mode' => $request->input('mode', 'plan'),
            ]);
            
            $conversationId = $conversation->id;
        }
        
        // Generate filename if not exists
        if (!$conversation->filename) {
            $timestamp = now()->format('Y-m-d\TH-i-s');
            $tempId = substr(uniqid(), -12);
            $filename = "claude-sessions/{$timestamp}-session-{$tempId}.json";
            $conversation->filename = $filename;
        }
        
        // Update conversation with new message and mark as processing
        $conversation->update([
            'message' => $request->input('prompt'),
            'is_processing' => true,
            'filename' => $conversation->filename
        ]);
        
        // Save user message immediately (synchronously) before queuing
        // Convert relative path to absolute if needed
        $projectDir = $conversation->project_directory;
        if ($projectDir && !str_starts_with($projectDir, '/')) {
            $projectDir = storage_path($projectDir);
        }
        
        ClaudeService::saveUserMessage(
            $request->input('prompt'),
            $conversation->filename,
            $conversation->claude_session_id,
            $projectDir
        );
        
        // Dispatch job to send message to Claude
        SendClaudeMessageJob::dispatch($conversation, $request->input('prompt'));
        
        return response()->json([
            'success' => true,
            'message' => 'Message queued for processing',
            'conversationId' => $conversationId,
            'sessionFilename' => $conversation->filename,
        ]);
    }

    /**
     * Get session messages
     * 
     * Retrieve all messages from a Claude AI session
     * 
     * @authenticated
     * 
     * @urlParam filename string required The session filename. Example: claude-sessions/2024-01-15T10-30-00-session-abc123.json
     * 
     * @response 200 scenario="Success" [{
     *   "prompt": "Can you help me debug this code?",
     *   "rawJsonResponses": ["Looking at your code..."],
     *   "timestamp": "2024-01-15T10:30:00.000000Z",
     *   "sessionId": "session_abc123"
     * }]
     * 
     * @response 404 scenario="Session Not Found" {
     *   "error": "Session file not found"
     * }
     * 
     * @response 422 scenario="Invalid Session File" {
     *   "error": "Invalid session file"
     * }
     */
    public function getSessionMessages($filename)
    {
        $path = $filename;

        if (!Storage::exists($path)) {
            return response()->json(['error' => 'Session file not found'], 404);
        }

        $content = Storage::get($path);
        $messages = json_decode($content, true);

        if (!$messages) {
            return response()->json(['error' => 'Invalid session file'], 422);
        }

        // Log the structure for debugging
        \Log::info('Session data structure:', [
            'filename' => $filename,
            'message_count' => count($messages),
            'first_message_keys' => !empty($messages) ? array_keys($messages[0]) : [],
            'first_response_sample' => !empty($messages) && !empty($messages[0]['rawJsonResponses'])
                ? array_slice($messages[0]['rawJsonResponses'], 0, 2)
                : []
        ]);

        return response()->json($messages);
    }
}
