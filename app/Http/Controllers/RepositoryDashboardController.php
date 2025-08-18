<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Repository;
use Inertia\Inertia;

class RepositoryDashboardController extends Controller
{
    public function show()
    {
        $repositoryName = request()->query('repository');
        
        if (!$repositoryName) {
            abort(404, 'Repository parameter is required');
        }
        
        $repository = Repository::where('name', $repositoryName)->firstOrFail();
        
        // Get repository stats
        $stats = $this->getRepositoryStats($repository);

        // Get recent conversations for this repository
        $recentConversations = Conversation::where('repository', $repository->name)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get(['id', 'title', 'created_at']);

        return Inertia::render('RepositoryDashboard', [
            'repository' => [
                'id' => $repository->id,
                'name' => $repository->name,
                'slug' => $repository->slug,
                'url' => $repository->url,
                'branch' => $repository->branch,
                'path' => $repository->path,
                'has_hot_folder' => $repository->has_hot_folder,
                'created_at' => $repository->created_at,
                'updated_at' => $repository->updated_at,
            ],
            'stats' => $stats,
            'recent_conversations' => $recentConversations,
        ]);
    }

    private function getRepositoryStats($repository)
    {
        $path = $repository->path;

        if (!is_dir($path)) {
            return null;
        }

        $filesCount = 0;
        $directoriesCount = 0;
        $totalSize = 0;

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                // Skip .git directory
                if (strpos($file->getPathname(), '/.git/') !== false) {
                    continue;
                }

                if ($file->isDir()) {
                    $directoriesCount++;
                } else {
                    $filesCount++;
                    $totalSize += $file->getSize();
                }
            }
        } catch (\Exception $e) {
            // Handle permission errors or other issues
            return null;
        }

        return [
            'files_count' => $filesCount,
            'directories_count' => $directoriesCount,
            'total_size' => $this->formatBytes($totalSize),
        ];
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
