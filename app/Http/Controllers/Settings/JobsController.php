<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use Symfony\Component\Process\PhpExecutableFinder;

class JobsController extends Controller
{
    public function index()
    {
        return Inertia::render('settings/Jobs');
    }

    public function status()
    {
        $workers = $this->getActiveWorkers();

        // Also check for queue:work processes running
        $runningProcesses = $this->findQueueWorkerProcesses();

        return response()->json([
            'workers' => $workers,
            'processes' => $runningProcesses,
            'stats' => $this->getQueueStats(),
            'failedJobs' => $this->getFailedJobs(),
        ]);
    }

    public function control(Request $request)
    {
        $action = $request->input('action');

        switch ($action) {
            case 'start':
                return $this->startWorker();
            case 'stop':
                return $this->stopWorker($request->input('workerId'));
            default:
                return back()->withErrors(['message' => 'Invalid action']);
        }
    }

    private function startWorker()
    {
        try {
            // Find PHP executable
            $phpFinder = new PhpExecutableFinder();
            $phpBinary = $phpFinder->find(false);

            if (!$phpBinary) {
                $phpBinary = PHP_BINARY;
            }

            // Start the queue worker in background
            $command = sprintf(
                '%s %s queue:work --timeout=3600 --tries=1 --sleep=3 > %s 2>&1 &',
                escapeshellarg($phpBinary),
                escapeshellarg(base_path('artisan')),
                escapeshellarg(storage_path('logs/queue-worker.log'))
            );

            // Execute command and get PID
            if (PHP_OS_FAMILY === 'Windows') {
                // Windows specific background execution
                $descriptors = [
                    0 => ['pipe', 'r'],
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w']
                ];
                $process = proc_open('start /B ' . $command, $descriptors, $pipes);
                $pid = proc_get_status($process)['pid'];
                proc_close($process);
            } else {
                // Unix/Linux/Mac
                exec($command . ' echo $!', $output);
                $pid = isset($output[0]) ? (int)$output[0] : null;

                // Alternative method to get PID
                if (!$pid) {
                    exec('ps aux | grep "[q]ueue:work" | tail -1 | awk \'{print $2}\'', $pidOutput);
                    $pid = isset($pidOutput[0]) ? (int)$pidOutput[0] : null;
                }
            }

            if ($pid) {
                $workerData = [
                    'id' => uniqid('worker_'),
                    'pid' => $pid,
                    'status' => 'running',
                    'startTime' => now()->toIso8601String(),
                    'processedJobs' => 0,
                    'failedJobs' => 0,
                ];

                $workers = Cache::get('queue_workers', []);
                $workers[] = $workerData;
                Cache::put('queue_workers', $workers, now()->addDay());

                return redirect()->route('settings.jobs')
                    ->with('message', 'Queue worker started successfully (PID: ' . $pid . ')');
            } else {
                // Even if we can't get PID, the worker might have started
                return redirect()->route('settings.jobs')
                    ->with('message', 'Queue worker command executed. Check process list for status.');
            }
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Failed to start queue worker: ' . $e->getMessage()]);
        }
    }

    private function stopWorker($workerId = null)
    {
        try {
            if ($workerId) {
                // Stop specific worker by ID
                $workers = Cache::get('queue_workers', []);
                $workers = array_filter($workers, function ($worker) use ($workerId) {
                    if ($worker['id'] === $workerId) {
                        $this->killProcess($worker['pid']);
                        return false;
                    }
                    return true;
                });

                Cache::put('queue_workers', array_values($workers), now()->addDay());
            } else {
                // Stop all queue workers
                $this->stopAllQueueWorkers();
                Cache::forget('queue_workers');
            }

            return redirect()->route('settings.jobs')
                ->with('message', 'Queue worker(s) stopped successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Failed to stop queue worker: ' . $e->getMessage()]);
        }
    }

    private function stopAllQueueWorkers()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            exec('taskkill /F /IM php.exe /FI "WINDOWTITLE eq queue:work" 2>&1');
        } else {
            // Stop all queue:work processes
            exec('pkill -f "queue:work" 2>&1');

            // Alternative method
            exec('ps aux | grep "[q]ueue:work" | awk \'{print $2}\' | xargs kill -9 2>&1');
        }
    }

    private function killProcess($pid)
    {
        if (PHP_OS_FAMILY === 'Windows') {
            exec("taskkill /F /PID $pid 2>&1");
        } else {
            exec("kill -9 $pid 2>&1");
        }
    }

    private function findQueueWorkerProcesses()
    {
        $processes = [];

        if (PHP_OS_FAMILY === 'Windows') {
            exec('wmic process where "commandline like \'%queue:work%\'" get processid,commandline /format:csv 2>&1', $output);
            foreach ($output as $line) {
                if (strpos($line, 'queue:work') !== false) {
                    $parts = str_getcsv($line);
                    if (count($parts) >= 3) {
                        $processes[] = [
                            'pid' => $parts[2],
                            'command' => $parts[1],
                        ];
                    }
                }
            }
        } else {
            exec('ps aux | grep "[q]ueue:work"', $output);
            foreach ($output as $line) {
                if (strpos($line, 'queue:work') !== false) {
                    $parts = preg_split('/\s+/', $line);
                    if (count($parts) >= 2) {
                        $processes[] = [
                            'pid' => $parts[1],
                            'command' => implode(' ', array_slice($parts, 10)),
                            'memory' => $this->formatMemory($parts[5] ?? 0),
                            'cpu' => $parts[2] ?? '0',
                        ];
                    }
                }
            }
        }

        return $processes;
    }

    private function getActiveWorkers()
    {
        $workers = Cache::get('queue_workers', []);
        $activeWorkers = [];
        $runningProcesses = $this->findQueueWorkerProcesses();
        $runningPids = array_column($runningProcesses, 'pid');

        // Check cached workers
        foreach ($workers as $worker) {
            if (in_array($worker['pid'], $runningPids) || $this->isProcessRunning($worker['pid'])) {
                $processInfo = $this->getProcessInfo($worker['pid']);
                $worker['status'] = 'running';
                $worker['memory'] = $processInfo['memory'] ?? 'N/A';

                // Get job stats from database if available
                $stats = $this->getWorkerStats($worker['startTime']);
                $worker['processedJobs'] = $stats['processed'];
                $worker['failedJobs'] = $stats['failed'];

                $activeWorkers[] = $worker;
            }
        }

        // Add any running processes not in cache
        foreach ($runningProcesses as $process) {
            $found = false;
            foreach ($activeWorkers as $worker) {
                if ($worker['pid'] == $process['pid']) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $activeWorkers[] = [
                    'id' => uniqid('worker_'),
                    'pid' => $process['pid'],
                    'status' => 'running',
                    'startTime' => now()->toIso8601String(),
                    'processedJobs' => 0,
                    'failedJobs' => 0,
                    'memory' => $process['memory'] ?? 'N/A',
                ];
            }
        }

        // Update cache
        Cache::put('queue_workers', $activeWorkers, now()->addDay());

        return $activeWorkers;
    }

    private function isProcessRunning($pid)
    {
        if (!$pid) return false;

        if (PHP_OS_FAMILY === 'Windows') {
            $output = [];
            exec("tasklist /FI \"PID eq $pid\" 2>&1", $output);
            return count($output) > 1 && strpos(implode('', $output), (string)$pid) !== false;
        } else {
            return file_exists("/proc/$pid") || posix_kill($pid, 0);
        }
    }

    private function getProcessInfo($pid)
    {
        $info = [
            'memory' => 'N/A',
            'cpu' => '0',
        ];

        if (PHP_OS_FAMILY !== 'Windows') {
            // Get memory usage
            $output = [];
            exec("ps -o rss= -p $pid 2>&1", $output);
            if (!empty($output[0]) && is_numeric(trim($output[0]))) {
                $memoryKb = (int)trim($output[0]);
                $info['memory'] = $this->formatMemory($memoryKb * 1024);
            }

            // Get CPU usage
            exec("ps -o %cpu= -p $pid 2>&1", $cpuOutput);
            if (!empty($cpuOutput[0])) {
                $info['cpu'] = trim($cpuOutput[0]);
            }
        }

        return $info;
    }

    private function formatMemory($bytes)
    {
        if (!is_numeric($bytes)) return 'N/A';

        $bytes = (int)$bytes;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
        return round($bytes / 1048576, 2) . ' MB';
    }

    private function getWorkerStats($startTime)
    {
        try {
            $start = \Carbon\Carbon::parse($startTime);

            // Count processed jobs (this is an approximation based on jobs table if exists)
            $processed = 0;
            $failed = 0;

            // Check if jobs table exists
            if (\Schema::hasTable('jobs')) {
                $processed = DB::table('jobs')
                    ->where('created_at', '>=', $start)
                    ->whereNull('reserved_at')
                    ->count();
            }

            if (\Schema::hasTable('failed_jobs')) {
                $failed = DB::table('failed_jobs')
                    ->where('failed_at', '>=', $start)
                    ->count();
            }

            return [
                'processed' => $processed,
                'failed' => $failed,
            ];
        } catch (\Exception $e) {
            return [
                'processed' => 0,
                'failed' => 0,
            ];
        }
    }

    private function getQueueStats()
    {
        try {
            $stats = [
                'pending' => 0,
                'running' => 0,
                'failed' => 0,
            ];

            if (\Schema::hasTable('jobs')) {
                // Pending jobs are those not yet reserved
                $stats['pending'] = DB::table('jobs')
                    ->whereNull('reserved_at')
                    ->count();

                // Running jobs are those that have been reserved but not completed
                $stats['running'] = DB::table('jobs')
                    ->whereNotNull('reserved_at')
                    ->count();
            }

            if (\Schema::hasTable('failed_jobs')) {
                $stats['failed'] = DB::table('failed_jobs')->count();
            }

            return $stats;
        } catch (\Exception $e) {
            return [
                'pending' => 0,
                'running' => 0,
                'failed' => 0,
            ];
        }
    }

    private function getFailedJobs($limit = 10)
    {
        try {
            if (!\Schema::hasTable('failed_jobs')) {
                return [];
            }

            $failedJobs = DB::table('failed_jobs')
                ->orderBy('failed_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($job) {
                    $payload = json_decode($job->payload, true);
                    $exception = $job->exception;

                    // Parse exception to get just the error message
                    $errorMessage = 'Unknown error';
                    if ($exception) {
                        // Extract the main error message from the exception
                        $lines = explode("\n", $exception);
                        if (!empty($lines[0])) {
                            $errorMessage = $lines[0];
                        }
                    }

                    return [
                        'id' => $job->id,
                        'uuid' => $job->uuid ?? null,
                        'connection' => $job->connection,
                        'queue' => $job->queue,
                        'payload' => $payload,
                        'exception' => $errorMessage,
                        'fullException' => $exception,
                        'failed_at' => $job->failed_at,
                        'job_name' => $payload['displayName'] ?? 'Unknown Job',
                        'attempts' => $payload['attempts'] ?? 0,
                    ];
                })
                ->toArray();

            return $failedJobs;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function retry($id)
    {
        try {
            if (!\Schema::hasTable('failed_jobs')) {
                return back()->withErrors(['message' => 'Failed jobs table does not exist']);
            }

            $failedJob = DB::table('failed_jobs')->where('id', $id)->first();

            if (!$failedJob) {
                return back()->withErrors(['message' => 'Failed job not found']);
            }

            // Retry the failed job using Laravel's built-in command
            Artisan::call('queue:retry', ['id' => [$failedJob->uuid ?? $id]]);

            return back()->with('message', 'Job has been pushed back to the queue for retry');
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Failed to retry job: ' . $e->getMessage()]);
        }
    }

    public function discard($id)
    {
        try {
            if (!\Schema::hasTable('failed_jobs')) {
                return back()->withErrors(['message' => 'Failed jobs table does not exist']);
            }

            // Delete the failed job from the failed_jobs table
            $deleted = DB::table('failed_jobs')->where('id', $id)->delete();

            if (!$deleted) {
                return back()->withErrors(['message' => 'Failed job not found']);
            }

            return back()->with('message', 'Failed job has been discarded');
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Failed to discard job: ' . $e->getMessage()]);
        }
    }
}
