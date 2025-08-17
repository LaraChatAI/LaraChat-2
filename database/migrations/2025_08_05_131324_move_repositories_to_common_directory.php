<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Process;
use App\Models\Repository;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create common repository directory
        $commonDir = storage_path('app/private/repositories/common');
        if (!file_exists($commonDir)) {
            mkdir($commonDir, 0755, true);
        }

        // Move existing repositories to common directory
        $repositories = Repository::all();
        
        foreach ($repositories as $repository) {
            $oldPath = storage_path('app/private/' . $repository->local_path);
            
            if (file_exists($oldPath)) {
                // Extract the repository folder name from the old path
                $pathParts = explode('/', $repository->local_path);
                $repoFolderName = end($pathParts);
                
                // New path in common directory
                $newLocalPath = 'repositories/common/' . $repoFolderName;
                $newFullPath = storage_path('app/private/' . $newLocalPath);
                
                // Move the repository directory
                if (rename($oldPath, $newFullPath)) {
                    // Update the database record
                    $repository->update(['local_path' => $newLocalPath]);
                }
            }
        }
        
        // Clean up old user-specific directories if empty
        $reposDir = storage_path('app/private/repositories');
        if (file_exists($reposDir)) {
            $userDirs = glob($reposDir . '/*', GLOB_ONLYDIR);
            foreach ($userDirs as $userDir) {
                if (basename($userDir) !== 'common' && count(glob($userDir . '/*')) === 0) {
                    rmdir($userDir);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not reversible as it would require knowing
        // which user each repository belonged to originally
        throw new \RuntimeException('This migration cannot be reversed.');
    }
};
