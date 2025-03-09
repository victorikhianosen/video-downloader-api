<?php


namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class LogController extends Controller
{
    public function fetchLogs(): JsonResponse
    {
        // Path to the log file
        $logPath = storage_path('logs/laravel.log');

        // Check if the file exists
        if (!file_exists($logPath)) {
            return response()->json(['error' => 'Log file not found'], 404);
        }

        // Get the contents of the log file
        $logContents = file_get_contents($logPath);

        if ($logContents === false) {
            return response()->json(['error' => 'Unable to read log file'], 500);
        }

        // Ensure the log contents are UTF-8 encoded
        $logContents = mb_convert_encoding($logContents, 'UTF-8', 'UTF-8');

        // Split log content into lines for better readability
        $logLines = preg_split('/\r\n|\r|\n/', $logContents);

        // Return the log content as a JSON response
        return response()->json(['logs' => $logLines]);
    }
}
