<?php

namespace App\Http\Controllers;

use App\Models\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;

/**
 * @group Repository Management
 * 
 * APIs for managing Git repositories
 */
class RepositoryController extends Controller
{
    /**
     * List repositories
     * 
     * Get a list of all repositories with their hot folder status
     * 
     * @authenticated
     * 
     * @response 200 scenario="Success" [{
     *   "id": 1,
     *   "name": "my-project",
     *   "url": "https://github.com/user/my-project.git",
     *   "local_path": "repositories/base/my-project",
     *   "branch": "main",
     *   "last_pulled_at": "2024-01-15T10:30:00.000000Z",
     *   "has_hot_folder": true,
     *   "slug": "my-project",
     *   "created_at": "2024-01-10T08:00:00.000000Z",
     *   "updated_at": "2024-01-15T10:30:00.000000Z"
     * }]
     */
    public function index(Request $request)
    {
        $repositories = Repository::orderBy('created_at', 'desc')
            ->get();

        // Check hot folder status for each repository and ensure slug is included
        $repositories->transform(function ($repository) {
            $repoName = $this->extractRepoName($repository->url);
            $hotPattern = storage_path('app/private/repositories/hot/' . $repoName);
            $hotFolders = glob($hotPattern);
            $repository->has_hot_folder = !empty($hotFolders);
            // Ensure slug is included in the response
            $repository->makeVisible('slug');
            return $repository;
        });

        return $repositories;
    }

    /**
     * Clone repository
     * 
     * Clone a new Git repository to the local system
     * 
     * @authenticated
     * 
     * @bodyParam url string required The Git repository URL. Example: https://github.com/user/repo.git
     * @bodyParam branch string optional The branch to clone. If not specified, the default branch will be used. Example: develop
     * 
     * @response 200 scenario="Success" {
     *   "message": "Repository cloned successfully",
     *   "repository": {
     *     "id": 1,
     *     "name": "my-project",
     *     "url": "https://github.com/user/my-project.git",
     *     "local_path": "repositories/base/my-project",
     *     "branch": "main",
     *     "last_pulled_at": "2024-01-15T10:30:00.000000Z"
     *   }
     * }
     * 
     * @response 409 scenario="Repository Exists" {
     *   "message": "Repository already exists",
     *   "repository": {}
     * }
     * 
     * @response 422 scenario="Clone Failed" {
     *   "message": "Failed to clone repository",
     *   "error": "fatal: repository not found"
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'branch' => 'nullable|string',
        ]);

        $url = $request->input('url');
        $branch = $request->input('branch');

        // Check if repository already exists
        $existingRepository = Repository::where('url', $url)
            ->first();

        if ($existingRepository) {
            return response()->json([
                'message' => 'Repository already exists',
                'repository' => $existingRepository
            ], 409);
        }

        // Extract repository name from URL
        $repoName = $this->extractRepoName($url);

        // Generate local path in base directory using repository name
        $localPath = 'repositories/base/' . $repoName;
        $fullPath = storage_path('app/private/' . $localPath);

        // Create directory if it doesn't exist
        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        // Clone the repository
        if ($branch) {
            // If branch is specified, try to clone with that branch
            $result = Process::run("git clone --depth=1 --branch {$branch} {$url} {$fullPath}");

            if (!$result->successful() && str_contains($result->errorOutput(), 'Remote branch')) {
                // If the branch doesn't exist, try cloning without specifying branch
                $result = Process::run("git clone {$url} {$fullPath} --depth=1");
            }
        } else {
            // No branch specified, clone with default branch
            $result = Process::run("git clone {$url} {$fullPath} --depth=1");
        }

        if (!$result->successful()) {
            // Clean up if directory was created
            if (file_exists($fullPath)) {
                Process::run("rm -rf {$fullPath}");
            }

            return response()->json([
                'message' => 'Failed to clone repository',
                'error' => $result->errorOutput()
            ], 422);
        }

        // Get the actual branch that was cloned
        $branchResult = Process::path($fullPath)->run('git branch --show-current');
        $actualBranch = trim($branchResult->output());

        // Create repository record
        $repository = Repository::create([
            'name' => $repoName,
            'url' => $url,
            'local_path' => $localPath,
            'branch' => $actualBranch ?: 'master',
            'last_pulled_at' => now(),
        ]);

        return response()->json([
            'message' => 'Repository cloned successfully',
            'repository' => $repository
        ]);
    }

    /**
     * Delete repository
     * 
     * Remove a repository from the system
     * 
     * @authenticated
     * 
     * @urlParam repository integer required The ID of the repository. Example: 1
     * 
     * @response 200 scenario="Success" {
     *   "message": "Repository deleted successfully"
     * }
     */
    public function destroy(Repository $repository)
    {
        // Delete base repository folder
        $fullPath = storage_path('app/private/' . $repository->local_path);
        if (file_exists($fullPath)) {
            Process::run("rm -rf {$fullPath}");
        }
        
        // Delete hot folders if they exist
        $repoName = $this->extractRepoName($repository->url);
        $hotPattern = storage_path('app/private/repositories/hot/' . $repoName);
        $hotFolders = glob($hotPattern);
        foreach ($hotFolders as $hotFolder) {
            if (file_exists($hotFolder)) {
                Process::run("rm -rf {$hotFolder}");
            }
        }

        // Delete database record
        $repository->delete();

        return response()->json([
            'message' => 'Repository deleted successfully'
        ]);
    }

    /**
     * Pull repository updates
     * 
     * Pull the latest changes from the remote repository
     * 
     * @authenticated
     * 
     * @urlParam repository integer required The ID of the repository. Example: 1
     * 
     * @response 200 scenario="Success" {
     *   "message": "Repository updated successfully",
     *   "repository": {
     *     "id": 1,
     *     "name": "my-project",
     *     "url": "https://github.com/user/my-project.git",
     *     "local_path": "repositories/base/my-project",
     *     "branch": "main",
     *     "last_pulled_at": "2024-01-15T11:00:00.000000Z"
     *   }
     * }
     * 
     * @response 422 scenario="Pull Failed" {
     *   "message": "Failed to pull repository",
     *   "error": "error: Your local changes would be overwritten"
     * }
     */
    public function pull(Repository $repository)
    {
        $fullPath = storage_path('app/private/' . $repository->local_path);

        // Pull latest changes
        $result = Process::path($fullPath)->run('git pull');

        if (!$result->successful()) {
            return response()->json([
                'message' => 'Failed to pull repository',
                'error' => $result->errorOutput()
            ], 422);
        }

        // Update last pulled timestamp
        $repository->update(['last_pulled_at' => now()]);

        return response()->json([
            'message' => 'Repository updated successfully',
            'repository' => $repository
        ]);
    }

    /**
     * Copy repository to hot folder
     * 
     * Check if a hot folder exists for the repository or trigger creation
     * 
     * @authenticated
     * 
     * @urlParam repository integer required The ID of the repository. Example: 1
     * 
     * @response 200 scenario="Hot Folder Exists" {
     *   "message": "Hot folder already exists",
     *   "has_hot_folder": true
     * }
     * 
     * @response 200 scenario="Copy Job Dispatched" {
     *   "message": "Repository copy job dispatched",
     *   "has_hot_folder": false
     * }
     */
    public function copyToHot(Repository $repository)
    {
        // Check if hot folder already exists
        $repoName = $this->extractRepoName($repository->url);
        $hotPattern = storage_path('app/private/repositories/hot/' . $repoName . '/' . $repoName . '_*');
        $hotFolders = glob($hotPattern);
        if (!empty($hotFolders)) {
            return response()->json([
                'message' => 'Hot folder already exists',
                'has_hot_folder' => true
            ]);
        }

        return response()->json([
            'message' => 'Repository copy job dispatched',
            'has_hot_folder' => false
        ]);
    }

    /**
     * Get .env file content
     * 
     * Retrieve the contents of the repository's .env file
     * 
     * @authenticated
     * 
     * @urlParam repository integer required The ID of the repository. Example: 1
     * 
     * @response 200 scenario="Success" {
     *   "content": "APP_NAME=Laravel\nAPP_ENV=local\nAPP_KEY=base64:...\nAPP_DEBUG=true"
     * }
     * 
     * @response 404 scenario=".env File Not Found" {
     *   "message": ".env file not found"
     * }
     */
    public function getEnvFile(Repository $repository)
    {
        $envPath = storage_path('app/private/' . $repository->local_path . '/.env');
        
        if (!file_exists($envPath)) {
            return response()->json([
                'content' => ''
            ]);
        }
        
        $content = file_get_contents($envPath);
        
        return response()->json([
            'content' => $content
        ]);
    }
    
    /**
     * Update .env file content
     * 
     * Update the contents of the repository's .env file
     * 
     * @authenticated
     * 
     * @urlParam repository integer required The ID of the repository. Example: 1
     * @bodyParam content string required The new content for the .env file. Example: APP_NAME=MyApp\nAPP_ENV=production
     * 
     * @response 200 scenario="Success" {
     *   "message": ".env file updated successfully"
     * }
     * 
     * @response 422 scenario="Update Failed" {
     *   "message": "Failed to update .env file"
     * }
     */
    public function updateEnvFile(Request $request, Repository $repository)
    {
        $request->validate([
            'content' => 'required|string'
        ]);
        
        $envPath = storage_path('app/private/' . $repository->local_path . '/.env');
        
        // Create backup of existing .env file if it exists
        if (file_exists($envPath)) {
            $backupPath = storage_path('app/private/' . $repository->local_path . '/.env.backup');
            copy($envPath, $backupPath);
        }
        
        // Write new content
        $result = file_put_contents($envPath, $request->input('content'));
        
        if ($result === false) {
            return response()->json([
                'message' => 'Failed to update .env file'
            ], 422);
        }
        
        return response()->json([
            'message' => '.env file updated successfully'
        ]);
    }

    private function extractRepoName(string $url): string
    {
        // Remove trailing .git if present
        if (str_ends_with($url, '.git')) {
            $url = substr($url, 0, -4);
        }

        // Extract repository name from URL
        $parts = explode('/', $url);
        return end($parts);
    }
}
