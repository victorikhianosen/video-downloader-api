<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class YoutubeDownloader extends Controller
{
    public function download(Request $request)
    {
        // Validate the YouTube URL
        $request->validate([
            'url' => 'required|url',
        ]);

        $url = $request->input('url');

        // Full path to yt-dlp executable
        $command = "C:\\yt-dlp\\yt-dlp.exe -f best --get-url $url";

        // Capture output and error
        exec($command, $output, $status);

        // Log output for debugging
        \Log::info("yt-dlp command output: " . implode("\n", $output));

        // Check if the command was successful
        if ($status !== 0) {
            return response()->json(['error' => 'Unable to fetch video.'], 500);
        }

        // Return the download URL
        return response()->json(['download_url' => trim(implode("\n", $output))]);
    }

}
