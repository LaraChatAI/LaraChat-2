<?php

namespace App\Http\Controllers;

use App\Jobs\CopyRepositoryToHotJob;
use App\Jobs\DeleteProjectDirectoryJob;
use App\Jobs\InitializeConversationSessionJob;
use App\Jobs\PrepareProjectDirectoryJob;
use App\Jobs\SendClaudeMessageJob;
use App\Models\Conversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Inertia\Inertia;

/**
 * @group Conversations
 * 
 * APIs for managing Claude AI conversations
 */
class ConversationsController extends Controller
{
    /**
     * List conversations
     * 
     * Get a list of all non-archived conversations for the authenticated user
     * 
     * @authenticated
     * 
     * @response 200 scenario="Success" [{
     *   "id": 1,
     *   "user_id": 1,
     *   "title": "How to implement authentication",
     *   "message": "Can you help me implement JWT authentication?",
     *   "claude_session_id": "session_abc123",
     *   "project_directory": "/projects/abc123",
     *   "repository": "myapp",
     *   "filename": "claude-sessions/2024-01-15T10-30-00-session-abc123.json",
     *   "is_processing": false,
     *   "archived": false,
     *   "created_at": "2024-01-15T10:30:00.000000Z",
     *   "updated_at": "2024-01-15T10:35:00.000000Z"
     * }]
     */
    public function index()
    {
        $conversations = Conversation::where('archived', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($conversations);
    }

    /**
     * Create conversation
     * 
     * Start a new conversation with Claude AI
     * 
     * @authenticated
     * 
     * @bodyParam message string required The initial message to send to Claude. Can be base64 encoded. Example: How do I implement authentication?
     * @bodyParam repository string optional The repository to use for this conversation. Example: myapp
     * 
     * @response 302 scenario="Success" {
     *   "redirect": "/claude/1"
     * }
     * 
     * @response 422 scenario="Validation Error" {
     *   "message": "The message field is required.",
     *   "errors": {
     *     "message": ["The message field is required."]
     *   }
     * }
     */
    public function store(Request $request): RedirectResponse
    {
        // Handle base64 encoded message if present
        $message = $request->input('message');
        if ($message && base64_decode($message, true) !== false) {
            $decodedMessage = base64_decode($message);
            // Check if it's actually base64 encoded text
            if (mb_check_encoding($decodedMessage, 'UTF-8')) {
                $message = $decodedMessage;
            }
        }

        // Override the request input for validation
        $request->merge(['message' => $message]);

        $request->validate([
            'message' => 'required|string',
            'repository' => 'nullable|string',
            'mode' => 'nullable|string|in:plan,bypassPermissions',
        ]);

        // Only allow blank repository when in plan mode
        if (empty($request->input('repository')) && $request->input('mode', 'plan') !== 'plan') {
            return back()->withErrors(['repository' => 'A repository is required when not in planning mode.']);
        }

        $project_id = uniqid();
        $msg = $request->input('message');
        
        // For blank repository, use the base directory
        if (empty($request->input('repository'))) {
            $projectDirectory = 'app/private/repositories/base';
        } else {
            // Get base project directory from .env
            $baseProjectDirectory = env('PROJECTS_DIRECTORY', 'app/private/repositories');
            $projectDirectory = rtrim($baseProjectDirectory, '/') . '/' . $project_id;
        }

        $conversation = Conversation::query()->create([
            'user_id' => Auth::id(),
            'title' => substr($msg, 0, 100) . (strlen($msg) > 100 ? '...' : ''),
            'message' => $msg,
            'claude_session_id' => null, // Let Claude generate this
            'project_directory' => $projectDirectory,
            'repository' => $request->input('repository'),
            'filename' => 'claude-sessions/' . date('Y-m-d\TH-i-s') . '-session-' . $project_id . '.json',
            'is_processing' => true, // Mark as processing when created
            'mode' => $request->input('mode', 'plan'), // Default to 'plan' if not specified
        ]);

        Bus::chain([
            new InitializeConversationSessionJob($conversation, $msg),
            new SendClaudeMessageJob($conversation, $msg)
        ])->dispatch();

        CopyRepositoryToHotJob::dispatch($conversation->repository);

        return redirect()->route('claude.conversation', $conversation->id);
    }

    /**
     * Archive conversation
     * 
     * Archive a conversation to hide it from the main list
     * 
     * @authenticated
     * 
     * @urlParam conversation integer required The ID of the conversation. Example: 1
     * 
     * @response 200 scenario="Success" {
     *   "message": "Conversation archived successfully"
     * }
     * 
     * @response 403 scenario="Unauthorized" {
     *   "error": "Unauthorized"
     * }
     */
    public function archive(Conversation $conversation)
    {
        $conversation->archived = true;
        $conversation->save();

        // Only dispatch delete job if conversation has a non-blank repository
        if (!empty($conversation->repository)) {
            DeleteProjectDirectoryJob::dispatch($conversation);
        }

        return response()->json(['message' => 'Conversation archived successfully']);
    }

    /**
     * Unarchive conversation
     * 
     * Restore an archived conversation to the main list
     * 
     * @authenticated
     * 
     * @urlParam conversation integer required The ID of the conversation. Example: 1
     * 
     * @response 200 scenario="Success" {
     *   "message": "Conversation unarchived successfully"
     * }
     * 
     * @response 403 scenario="Unauthorized" {
     *   "error": "Unauthorized"
     * }
     */
    public function unarchive(Conversation $conversation)
    {
        $conversation->archived = false;
        $conversation->save();

        return response()->json(['message' => 'Conversation unarchived successfully']);
    }

    /**
     * Update conversation
     * 
     * Update conversation properties like mode
     * 
     * @authenticated
     * 
     * @urlParam conversation integer required The ID of the conversation. Example: 1
     * @bodyParam mode string required The conversation mode. Example: plan
     * 
     * @response 200 scenario="Success" {
     *   "message": "Conversation updated successfully"
     * }
     * 
     * @response 403 scenario="Unauthorized" {
     *   "error": "Unauthorized"
     * }
     */
    public function update(Request $request, Conversation $conversation)
    {
        // Check if user owns this conversation
        if ($conversation->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'mode' => 'required|string|in:plan,bypassPermissions',
        ]);

        $conversation->mode = $request->input('mode');
        $conversation->save();

        return response()->json(['message' => 'Conversation updated successfully']);
    }

    /**
     * List archived conversations
     * 
     * Get a list of all archived conversations for the authenticated user
     * 
     * @authenticated
     * 
     * @response 200 scenario="Success" [{
     *   "id": 2,
     *   "user_id": 1,
     *   "title": "Old conversation about testing",
     *   "message": "How do I write unit tests?",
     *   "claude_session_id": "session_xyz789",
     *   "project_directory": "/projects/xyz789",
     *   "repository": "testapp",
     *   "filename": "claude-sessions/2024-01-10T14-20-00-session-xyz789.json",
     *   "is_processing": false,
     *   "archived": true,
     *   "created_at": "2024-01-10T14:20:00.000000Z",
     *   "updated_at": "2024-01-10T14:25:00.000000Z"
     * }]
     */
    public function archived()
    {
        $conversations = Conversation::where('user_id', Auth::id())
            ->where('archived', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($conversations);
    }

    /**
     * Show diff page
     * 
     * Display the git diff for a conversation's project
     * 
     * @authenticated
     * 
     * @urlParam conversation integer required The ID of the conversation. Example: 1
     * 
     * @response 200 scenario="Success" Returns Inertia page with diff content
     * 
     * @response 403 scenario="Unauthorized" {
     *   "error": "Unauthorized"
     * }
     */
    public function showDiff(Conversation $conversation)
    {
        // Check if user owns this conversation
        if ($conversation->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Get the project directory path
        if (str_starts_with($conversation->project_directory, '/')) {
            $projectPath = $conversation->project_directory;
        } else {
            $projectPath = storage_path($conversation->project_directory);
        }

        // Check if project.diff exists
        $diffPath = $projectPath . '/.git/project.diff';
        $diffContent = '';
        
        if (file_exists($diffPath)) {
            $diffContent = file_get_contents($diffPath);
        }

        return Inertia::render('ConversationDiff', [
            'conversationId' => $conversation->id,
            'diffContent' => $diffContent,
            'conversationTitle' => $conversation->title,
            'hasContent' => !empty($diffContent)
        ]);
    }

}
