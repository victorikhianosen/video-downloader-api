<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

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
        Log::info("yt-dlp command output: " . implode("\n", $output));

        // Check if the command was successful
        if ($status !== 0) {
            return response()->json(['error' => 'Unable to fetch video.'], 500);
        }

        // Get the download URL
        $downloadUrl = trim(implode("\n", $output));

        // Download the video content using Laravel's HTTP client
        try {
            // Get the video content
            $videoContent = Http::get($downloadUrl)->body();

            // Generate a filename for the video
            $filename = 'video_' . time() . '.mp4';  // Change to the appropriate format if needed

            // Store the video in the storage folder (public disk for access via URL)
            Storage::disk('public')->put('videos/' . $filename, $videoContent);

            // Return the URL of the stored video
            $videoUrl = Storage::url('videos/' . $filename);

            return response()->json(['download_url' => $videoUrl]);
        } catch (\Exception $e) {
            // Handle errors while downloading the video
            Log::error('Error downloading video: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to download the video.'], 500);
        }
    }


    
}
