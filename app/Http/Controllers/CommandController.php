<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;

/**
 * @group System Commands
 * 
 * APIs for executing system commands
 */
class CommandController extends Controller
{
    /**
     * Execute command
     * 
     * Run a system command and return the output
     * 
     * @authenticated
     * 
     * @bodyParam command string required The command to execute. Example: ls -la
     * 
     * @response 200 scenario="Success" {
     *   "output": "total 48\ndrwxr-xr-x  12 user  staff   384 Jan 15 10:30 .\ndrwxr-xr-x   5 user  staff   160 Jan 10 08:00 ..",
     *   "success": true
     * }
     * 
     * @response 200 scenario="Command Failed" {
     *   "output": "ls: invalid option -- 'z'",
     *   "success": false
     * }
     * 
     * @response 500 scenario="Execution Error" {
     *   "output": "Command execution failed",
     *   "success": false
     * }
     */
    public function run(Request $request)
    {
        $request->validate([
            'command' => 'required|string',
        ]);

        $command = $request->input('command');
        
        try {
            $result = Process::run($command);
            
            return response()->json([
                'output' => trim($result->output()),
                'success' => $result->successful(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'output' => $e->getMessage(),
                'success' => false,
            ], 500);
        }
    }
}