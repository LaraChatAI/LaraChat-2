<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class SystemUpdateController extends Controller
{
    public function update(Request $request)
    {
        try {
            // Run the system-update.sh script
            $scriptPath = base_path('scripts/system-update.sh');
            
            // Make sure the script is executable
            if (!file_exists($scriptPath)) {
                throw new \Exception('Update script not found at: ' . $scriptPath);
            }
            
            // Run the system-update.sh script
            $process = $this->runCommand('bash ' . escapeshellarg($scriptPath));
            $output = $process->getOutput();
            
            Log::info('System update completed successfully', ['output' => $output]);

            return back()->with('flash', [
                'message' => 'System update completed successfully!',
                'output' => $output
            ]);

        } catch (ProcessFailedException $e) {
            Log::error('System update failed', [
                'error' => $e->getMessage(),
                'output' => $e->getProcess()->getErrorOutput()
            ]);

            return back()->withErrors([
                'message' => 'System update failed: ' . $e->getMessage(),
                'output' => $e->getProcess()->getErrorOutput()
            ]);
        } catch (\Exception $e) {
            Log::error('System update error', ['error' => $e->getMessage()]);

            return back()->withErrors([
                'message' => 'An error occurred during the update: ' . $e->getMessage()
            ]);
        }
    }

    public function runCommand(string $command): Process
    {
        // Set up PATH with all necessary binaries - include multiple possible locations
        $paths = [
            '/Users/customer/Library/Application Support/Herd/bin',
            '/Users/customer/Library/Application Support/Herd/config/nvm/versions/node/v22.17.1/bin',
            '/opt/homebrew/bin', // Homebrew on Apple Silicon
            '/usr/local/bin',    // Homebrew on Intel / standard Unix
            '/usr/bin',
            '/bin',
            '/usr/sbin',
            '/sbin',
        ];
        
        // Add user's home directories for various Node.js version managers
        $home = $_SERVER['HOME'] ?? '/Users/customer';
        
        // NVM paths
        if (is_dir($home . '/.nvm/versions/node')) {
            // Try to find the latest version
            $nvmVersions = glob($home . '/.nvm/versions/node/*/bin');
            if (!empty($nvmVersions)) {
                $paths[] = end($nvmVersions); // Use the latest version
            }
        }
        $paths[] = $home . '/.nvm/versions/node/default/bin';
        
        // Other Node.js version managers
        $paths[] = $home . '/.volta/bin';
        $paths[] = $home . '/.fnm/aliases/default/bin';
        $paths[] = $home . '/.asdf/shims';
        
        // Remove duplicates and non-existent paths
        $paths = array_unique(array_filter($paths, 'is_dir'));
        
        // Combine all paths
        $fullPath = implode(':', $paths);
        
        // Source NVM if available and then run command
        $nvmSource = '';
        if (file_exists($home . '/.nvm/nvm.sh')) {
            $nvmSource = 'source ' . escapeshellarg($home . '/.nvm/nvm.sh') . ' 2>/dev/null && ';
        } elseif (file_exists('/Users/customer/Library/Application Support/Herd/config/nvm/nvm.sh')) {
            $nvmSource = 'source ' . escapeshellarg('/Users/customer/Library/Application Support/Herd/config/nvm/nvm.sh') . ' 2>/dev/null && ';
        }
        
        // Create the shell command with the proper PATH
        $shellCommand = 'export PATH="' . $fullPath . ':$PATH" && ' . $nvmSource . $command;
        
        // Use bash to execute the command with the environment
        $process = Process::fromShellCommandline($shellCommand);
        $process->setWorkingDirectory(base_path());
        $process->setTimeout(300); // 5 minutes timeout
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process;
    }
}