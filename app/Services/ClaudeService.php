<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Process;

class ClaudeService
{
    private static $runningProcesses = [];

    public static function stream(string $prompt, string $options = '--permission-mode bypassPermissions', ?string $sessionId = null, ?string $sessionFilename = null, ?string $repositoryPath = null)
    {
        // Generate a unique process ID for this request
        $processId = uniqid('claude_', true);

        return new StreamedResponse(function () use ($prompt, $options, $sessionId, $sessionFilename, $repositoryPath, $processId) {
            ob_implicit_flush(true);
            if (ob_get_level() > 0) {
                ob_end_flush();
            }

            // Extract project ID from repository path
            $projectId = null;
            if ($repositoryPath) {
                // Try new format: /Users/customer/www/subdomains/{project_id}
                if (preg_match('/\/subdomains\/([^\/]+)/', $repositoryPath, $matches)) {
                    $projectId = $matches[1];
                }
                // Fallback to old format: repositories/projects/{project_id}
                elseif (preg_match('/repositories\/projects\/([^\/]+)/', $repositoryPath, $matches)) {
                    $projectId = $matches[1];
                }
            }

            $wrapperPath = base_path('claude-wrapper.sh');
            $command = [$wrapperPath];
            
            // Add project ID as first argument if available
            if ($projectId) {
                $command[] = $projectId;
            } else {
                // Default project ID if none specified
                $command[] = 'default';
            }
            
            // Add Claude CLI arguments
            $command = array_merge($command, ['--print', '--verbose', '--output-format', 'stream-json']);

            // Use --resume for continuing an existing session with a valid UUID
            if ($sessionId && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $sessionId)) {
                $command[] = '--resume';
                $command[] = $sessionId;
            }

            if ($options) {
                $optionsParts = explode(' ', $options);
                $command = array_merge($command, $optionsParts);
            }

            $command[] = $prompt;

            // With the wrapper handling directory changes, we don't need to set working directory here
            // The wrapper will cd to the correct project directory based on the project ID
            $process = new Process($command);
            $process->setTimeout(null);
            $process->setIdleTimeout(null);

            $process->start();

            // Store the process for potential termination
            self::$runningProcesses[$processId] = $process;

            // Send process ID to frontend
            echo json_encode(['type' => 'process_started', 'processId' => $processId]) . "\n";
            flush();

            // Initialize session data
            $rawJsonResponses = [];
            $extractedSessionId = $sessionId;
            $filename = $sessionFilename;

            // Generate filename if not provided
            if (!$filename) {
                $timestamp = date('Y-m-d_H-i-s');
                $filename = "{$timestamp}-claude-chat.json";
            }

            \Log::info('Starting Claude stream', [
                'prompt' => $prompt,
                'sessionId' => $sessionId,
                'filename' => $filename,
                'repositoryPath' => $repositoryPath
            ]);

            // Buffer for incomplete JSON lines
            $buffer = '';

            foreach ($process as $type => $data) {
                if ($process::OUT === $type) {
                    // Echo the original data to the client
                    echo $data;
                    flush();

                    // Process the data for saving
                    $buffer .= $data;
                    $lines = explode("\n", $buffer);
                    $buffer = array_pop($lines); // Keep incomplete line in buffer

                    foreach ($lines as $line) {
                        if (trim($line)) {
                            try {
                                $jsonData = json_decode($line, true);
                                if ($jsonData) {
                                    $rawJsonResponses[] = $jsonData;

                                    \Log::info('Parsed JSON response', [
                                        'type' => $jsonData['type'] ?? 'unknown',
                                        'has_content' => isset($jsonData['content']),
                                        'response_sample' => substr(json_encode($jsonData), 0, 200)
                                    ]);

                                    // Extract session ID if not provided
                                    if (!$extractedSessionId) {
                                        $extractedSessionId = self::extractSessionId($jsonData);
                                        if ($extractedSessionId) {
                                            \Log::info('Extracted session ID', ['sessionId' => $extractedSessionId]);
                                        }
                                    }

                                    // Save after each response
                                    if ($filename) {
                                        self::saveResponse($prompt, $filename, $sessionId, $extractedSessionId, $rawJsonResponses, false, $repositoryPath);
                                    }
                                }
                            } catch (\Exception $e) {
                                \Log::error('JSON parsing error', [
                                    'error' => $e->getMessage(),
                                    'line' => $line
                                ]);
                            }
                        }
                    }
                } else {
                    // Send error as JSON
                    $errorJson = json_encode(['error' => $data]) . "\n";
                    echo $errorJson;
                    flush();

                    $rawJsonResponses[] = ['error' => $data];
                }
            }

            // Process any remaining buffer
            if (trim($buffer)) {
                try {
                    $jsonData = json_decode($buffer, true);
                    if ($jsonData) {
                        $rawJsonResponses[] = $jsonData;
                    }
                } catch (\Exception $e) {
                    // Ignore JSON parsing errors
                }
            }

            // Final save with complete flag
            if ($filename) {
                self::saveResponse($prompt, $filename, $sessionId, $extractedSessionId, $rawJsonResponses, true, $repositoryPath);
            }

            // Clean up process from tracking
            unset(self::$runningProcesses[$processId]);

            // Send process ended signal
            echo json_encode(['type' => 'process_ended', 'processId' => $processId]) . "\n";
            flush();

            if (!$process->isSuccessful()) {
                echo json_encode(['error' => "Process exited with code: " . $process->getExitCode()]) . "\n";
                flush();
            }
        }, 200, [
            'Content-Type' => 'application/x-ndjson; charset=utf-8',
            'X-Accel-Buffering' => 'no',
            'Cache-Control' => 'no-cache',
        ]);
    }

    private static function extractSessionId($jsonData): ?string
    {
        // Check for session ID in system init response
        if ($jsonData['type'] === 'system' && $jsonData['subtype'] === 'init' && isset($jsonData['session_id'])) {
            return $jsonData['session_id'];
        }

        // Try different possible fields for session ID
        if (isset($jsonData['sessionId'])) {
            return $jsonData['sessionId'];
        } elseif (isset($jsonData['session_id'])) {
            return $jsonData['session_id'];
        } elseif (isset($jsonData['id'])) {
            return $jsonData['id'];
        } elseif (isset($jsonData['conversationId'])) {
            return $jsonData['conversationId'];
        }

        return null;
    }

    /**
     * Extract all text content from Claude responses
     */
    private static function extractAllTextContent(array $rawResponses): string
    {
        $content = '';
        
        foreach ($rawResponses as $response) {
            // Skip system messages
            if (isset($response['type']) && $response['type'] === 'system') {
                continue;
            }
            
            // Handle content blocks (streaming format from Claude CLI)
            if (isset($response['type']) && $response['type'] === 'content' && isset($response['content'])) {
                if (is_array($response['content']) && isset($response['content']['type']) && $response['content']['type'] === 'text' && isset($response['content']['text'])) {
                    $content .= $response['content']['text'];
                } elseif (is_string($response['content'])) {
                    $content .= $response['content'];
                }
            }
            
            // Handle Claude Code CLI assistant response format
            if (isset($response['type']) && $response['type'] === 'assistant' && isset($response['message'])) {
                $message = $response['message'];
                if (isset($message['content']) && is_array($message['content'])) {
                    foreach ($message['content'] as $item) {
                        if (isset($item['type']) && $item['type'] === 'text' && isset($item['text'])) {
                            $content .= $item['text'];
                        }
                    }
                }
            }
        }
        
        return $content;
    }

    /**
     * Process Claude message in background (for queue jobs)
     */
    public static function processInBackground(string $prompt, string $options = '--permission-mode bypassPermissions', ?string $sessionId = null, ?string $sessionFilename = null, ?string $repositoryPath = null, ?callable $progressCallback = null): array
    {
        // Extract project ID from repository path
        $projectId = null;
        if ($repositoryPath) {
            // Try new format: /Users/customer/www/subdomains/{project_id}
            if (preg_match('/\/subdomains\/([^\/]+)/', $repositoryPath, $matches)) {
                $projectId = $matches[1];
            }
            // Fallback to old format: repositories/projects/{project_id}
            elseif (preg_match('/repositories\/projects\/([^\/]+)/', $repositoryPath, $matches)) {
                $projectId = $matches[1];
            }
        }

        $wrapperPath = base_path('claude-wrapper.sh');
        $command = [$wrapperPath];
        
        // Add project ID as first argument if available
        if ($projectId) {
            $command[] = $projectId;
        } else {
            // Default project ID if none specified
            $command[] = 'default';
        }
        
        // Add Claude CLI arguments
        $command = array_merge($command, ['--print', '--verbose', '--output-format', 'stream-json']);

        // Use --resume for continuing an existing session with a valid UUID
        if ($sessionId && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $sessionId)) {
            $command[] = '--resume';
            $command[] = $sessionId;
        }

        if ($options) {
            $optionsParts = explode(' ', $options);
            $command = array_merge($command, $optionsParts);
        }

        $command[] = $prompt;

        // With the wrapper handling directory changes, we don't need to set working directory here
        // The wrapper will cd to the correct project directory based on the project ID
        $process = new Process($command);
        $process->setTimeout(null);
        $process->setIdleTimeout(null);

        // Initialize session data
        $rawJsonResponses = [];
        $extractedSessionId = $sessionId;
        $filename = $sessionFilename;

        // Generate filename if not provided
        if (!$filename) {
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "{$timestamp}-claude-chat.json";
        }

        \Log::info('Processing Claude in background', [
            'prompt' => $prompt,
            'sessionId' => $sessionId,
            'filename' => $filename,
            'repositoryPath' => $repositoryPath
        ]);

        // Run the process with real-time output processing
        $process->run(function ($type, $buffer) use (&$rawJsonResponses, &$extractedSessionId, $prompt, $filename, $sessionId, $repositoryPath, $progressCallback) {
            $lines = explode("\n", $buffer);
            
            foreach ($lines as $line) {
                if (trim($line)) {
                    try {
                        $jsonData = json_decode($line, true);
                        if ($jsonData) {
                            $rawJsonResponses[] = $jsonData;

                            // Extract session ID if not provided
                            if (!$extractedSessionId) {
                                $extractedSessionId = self::extractSessionId($jsonData);
                                
                                // Notify about session ID extraction
                                if ($progressCallback && $extractedSessionId) {
                                    $progressCallback('sessionId', $extractedSessionId);
                                }
                            }

                            // Save response incrementally after each message
                            if ($filename) {
                                self::saveResponse($prompt, $filename, $sessionId, $extractedSessionId, $rawJsonResponses, false, $repositoryPath);
                                
                                // Notify about progress with accumulated content
                                if ($progressCallback) {
                                    // Extract all text content from responses
                                    $allContent = self::extractAllTextContent($rawJsonResponses);
                                    
                                    $progressCallback('response', [
                                        'filename' => $filename,
                                        'responseCount' => count($rawJsonResponses),
                                        'content' => $allContent
                                    ]);
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::error('JSON parsing error in background job', [
                            'error' => $e->getMessage(),
                            'line' => $line
                        ]);
                    }
                }
            }
        });

        // Final save with complete flag ONLY if we got responses
        if ($filename && count($rawJsonResponses) > 0) {
            self::saveResponse($prompt, $filename, $sessionId, $extractedSessionId, $rawJsonResponses, true, $repositoryPath);
        } elseif ($filename && count($rawJsonResponses) === 0) {
            // If no responses, keep it incomplete so it can be retried
            \Log::warning('No responses from Claude process', [
                'filename' => $filename,
                'process_successful' => $process->isSuccessful()
            ]);
            self::saveResponse($prompt, $filename, $sessionId, $extractedSessionId, $rawJsonResponses, false, $repositoryPath);
        }

        return [
            'success' => $process->isSuccessful(),
            'sessionId' => $extractedSessionId,
            'filename' => $filename,
            'responses' => $rawJsonResponses
        ];
    }

    /**
     * Save initial user message to session file immediately
     */
    public static function saveUserMessage(string $userMessage, string $filename, ?string $sessionId = null, ?string $repositoryPath = null): void
    {
        self::saveResponse($userMessage, $filename, $sessionId, null, [], false, $repositoryPath);
    }

    private static function saveResponse(string $userMessage, string $filename, ?string $sessionId, ?string $extractedSessionId, array $rawJsonResponses, bool $isComplete, ?string $repositoryPath = null): void
    {
        // If filename already includes claude-sessions/, use it as-is
        // Otherwise, prepend claude-sessions/
        if (strpos($filename, 'claude-sessions/') === 0) {
            $path = $filename;
            $directory = 'claude-sessions';
        } else {
            $directory = 'claude-sessions';
            $path = $directory . '/' . $filename;
        }

        \Log::info('Saving response', [
            'filename' => $filename,
            'path' => $path,
            'sessionId' => $sessionId,
            'response_count' => count($rawJsonResponses),
            'isComplete' => $isComplete
        ]);

        // Create directory if it doesn't exist
        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);
            \Log::info('Created claude-sessions directory');
        }
        $lockKey = 'file_lock_' . md5($path);

        // Use cache lock to prevent concurrent writes
        $lock = Cache::lock($lockKey, 10);

        try {
            if ($lock->get()) {
                // Read existing data or create new array
                $data = [];
                if (Storage::exists($path)) {
                    $existingContent = Storage::get($path);
                    $data = json_decode($existingContent, true) ?? [];
                }

                $messageData = [
                    'sessionId' => $sessionId ?? $extractedSessionId ?? \Illuminate\Support\Str::uuid()->toString(),
                    'role' => 'user',
                    'userMessage' => $userMessage,
                    'timestamp' => now()->toIso8601String(),
                    'isComplete' => $isComplete,
                    'rawJsonResponses' => $rawJsonResponses,
                    'repositoryPath' => $repositoryPath
                ];

                // Check if this is a new conversation or an update to the current one
                $isNewConversation = true;

                // Only update if it's the last conversation and it's not complete
                if (!empty($data)) {
                    $lastIndex = count($data) - 1;
                    $lastConversation = &$data[$lastIndex];

                    \Log::info('Checking update condition', [
                        'lastIsComplete' => $lastConversation['isComplete'],
                        'lastUserMessage' => $lastConversation['userMessage'],
                        'currentUserMessage' => $userMessage,
                        'match' => (!$lastConversation['isComplete'] && $lastConversation['userMessage'] === $userMessage)
                    ]);
                    
                    if (!$lastConversation['isComplete'] &&
                        $lastConversation['userMessage'] === $userMessage) {
                        // Update the existing conversation with new responses
                        // Preserve the role field if it exists
                        if (isset($lastConversation['role'])) {
                            $messageData['role'] = $lastConversation['role'];
                        }
                        // Update with new data while preserving important fields
                        $data[$lastIndex] = array_merge($lastConversation, [
                            'rawJsonResponses' => $rawJsonResponses,
                            'isComplete' => $isComplete,
                            'timestamp' => $messageData['timestamp'],
                            'sessionId' => $messageData['sessionId'] ?? $lastConversation['sessionId'],
                            'repositoryPath' => $messageData['repositoryPath'] ?? $lastConversation['repositoryPath'],
                            'role' => $lastConversation['role'] ?? $messageData['role'] ?? null
                        ]);
                        $isNewConversation = false;
                    }
                }

                // If it's a new conversation, append it
                if ($isNewConversation) {
                    $data[] = $messageData;
                }

                // Save the updated data
                Storage::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            }
        } finally {
            optional($lock)->release();
        }
    }

    public static function stopProcess(string $processId): bool
    {
        if (isset(self::$runningProcesses[$processId])) {
            $process = self::$runningProcesses[$processId];

            try {
                // Stop the process
                $process->stop(3.0); // 3 second timeout

                // Remove from tracking
                unset(self::$runningProcesses[$processId]);

                \Log::info('Claude process stopped', ['processId' => $processId]);

                return true;
            } catch (\Exception $e) {
                \Log::error('Error stopping Claude process', [
                    'processId' => $processId,
                    'error' => $e->getMessage()
                ]);
                return false;
            }
        }

        return false;
    }

    public static function moveDirectory(string $source, string $destination): void
    {
        if (!File::exists($source)) {
            throw new \Exception("Source directory does not exist: {$source}");
        }

        File::ensureDirectoryExists(dirname($destination));

        if (File::exists($destination)) {
            File::deleteDirectory($destination);
        }

        $command = sprintf(
            'mv %s %s 2>&1',
            escapeshellarg($source),
            escapeshellarg($destination)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            $error = implode("\n", $output);
            Log::error('PrepareProjectDirectoryJob: Failed to move directory', [
                'source' => $source,
                'destination' => $destination,
                'error' => $error,
            ]);
            throw new \Exception("Failed to move directory: {$error}");
        }
    }
    private static function copyDirectory(string $source, string $destination): void
    {
        if (!File::exists($source)) {
            throw new \Exception("Source directory does not exist: {$source}");
        }

        File::ensureDirectoryExists($destination);

        $command = sprintf(
            'cp -r %s %s 2>&1',
            escapeshellarg($source . '/.'),
            escapeshellarg($destination)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            $error = implode("\n", $output);
            Log::error('PrepareProjectDirectoryJob: Failed to copy directory', [
                'source' => $source,
                'destination' => $destination,
                'error' => $error,
            ]);
            throw new \Exception("Failed to copy directory: {$error}");
        }
    }
}
