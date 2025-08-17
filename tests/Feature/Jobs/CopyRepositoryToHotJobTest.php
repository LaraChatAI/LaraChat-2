<?php

namespace Tests\Feature\Jobs;

use App\Jobs\CopyRepositoryToHotJob;
use App\Models\Repository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CopyRepositoryToHotJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_can_be_instantiated()
    {
        $repository = Repository::factory()->create();
        $job = new CopyRepositoryToHotJob($repository);
        
        $this->assertInstanceOf(CopyRepositoryToHotJob::class, $job);
    }

    public function test_job_deletes_repository_when_base_directory_missing()
    {
        // Create a repository in the database
        $repository = Repository::factory()->create([
            'name' => 'test-repo',
            'local_path' => 'repositories/base/test-repo',
        ]);

        // Ensure the base directory doesn't exist
        $basePath = storage_path('app/private/repositories/base/test-repo');
        if (is_dir($basePath)) {
            File::deleteDirectory($basePath);
        }

        // Run the job
        $job = new CopyRepositoryToHotJob('test-repo');

        // Expect an exception to be thrown
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Base repository directory does not exist');

        try {
            $job->handle();
        } catch (\Exception $e) {
            // Check that the repository was deleted from the database
            $this->assertDatabaseMissing('repositories', [
                'id' => $repository->id,
            ]);
            
            throw $e;
        }
    }

    public function test_job_skips_blank_repository()
    {
        $job = new CopyRepositoryToHotJob('');
        
        // This should not throw an exception
        $job->handle();
        
        $this->assertTrue(true); // Just to have an assertion
    }
}