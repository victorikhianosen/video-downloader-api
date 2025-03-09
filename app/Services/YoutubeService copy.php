<?php

namespace App\Services;

use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class YoutubeService
{
    // Implement YouTube related logic here

    use HttpResponses;

    public function download($validated)
    {
        // $url = $validated->input('url');
        $url = $validated['url'];

        // Full path to yt-dlp executable
        $command = "C:\\yt-dlp\\yt-dlp.exe -f best --get-url $url";

        // Capture output and error
        exec($command, $output, $status);

        // Log output for debugging
        Log::info("yt-dlp command output: " . implode("\n", $output));

        // Check if the command was successful
        if ($status !== 0) {
            return $this->error('Unable to fetch video', 500);
            // return response()->json(['error' => 'Unable to fetch video.'], 500);
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

            return $this->success('Video download successfully', ['video' => $videoUrl]);

            // return response()->json(['download_url' => $videoUrl]);
        } catch (\Exception $e) {
            // Handle errors while downloading the video
            Log::error('Error downloading video: ' . $e->getMessage());
            return $this->error('Unable to download the video.', 500);
        }
    }


    public function availableQualities($validated)
    {
        $url = $validated['url'];

        // Full path to yt-dlp executable
        $ytDlpPath = "C:\\yt-dlp\\yt-dlp.exe";

        // Step 1: Get the video title using yt-dlp
        $titleCommand = "$ytDlpPath --get-title \"$url\"";
        // Log::info("=========================");
        // Log::info("Check Available Qualities API");

        // Log::info("Executing yt-dlp title command: $titleCommand");

        exec($titleCommand, $titleOutput, $titleStatus);

        // Check if the title command was successful
        if ($titleStatus !== 0 || empty($titleOutput)) {
            // Log::error("yt-dlp title command failed for URL: $url");
            return response()->json(['error' => 'Unable to fetch video title'], 500);
        }

        // Extract the title from the output
        $videoTitle = trim(implode("\n", $titleOutput));
        // Log::info("Video title fetched: $videoTitle");

        // Step 2: Get available formats
        $formatCommand = "$ytDlpPath -F \"$url\"";
        // Log::info("Executing yt-dlp format command: $formatCommand");

        exec($formatCommand, $formatOutput, $formatStatus);

        // Log::info("yt-dlp format command output: " . implode("\n", $formatOutput));
        // Log::info("yt-dlp format command status: $formatStatus");

        // Check if the format command was successful
        if ($formatStatus !== 0) {
            // Log::error("yt-dlp format command failed for URL: $url");
            return response()->json(['error' => 'Unable to fetch video information'], 500);
        }

        // Step 3: Parse the output to extract available qualities
        $qualities = [];
        foreach ($formatOutput as $line) {
            if (preg_match('/(\d+)\s+(\d+x\d+|\d+)\s+(\w+)\s+(.+)/', $line, $matches)) {
                if (strpos($matches[4], 'video') !== false) {
                    $qualities[] = [
                        'title' => $videoTitle, // Add the title here
                        'format_code' => $matches[1],
                        'resolution' => $matches[2],
                        'extension' => $matches[3],
                        'description' => $matches[4],
                    ];
                }
            }
        }

        // If no valid qualities were found, return an error response
        if (empty($qualities)) {
            return $this->error('No video qualities found for the video.', 400);
        }

        return $this->success('Video qualities fetched successfully.', $qualities);
    }



    public function downloadWithResolution($validated)
    {
        $url = $validated['url'];
        $resolution = $validated['resolution'];

        // Construct the yt-dlp format string based on resolution
        $format = $this->getYtDlpFormat($resolution);

        // Log the format being used for debugging
        // Log::info("=========================");
        // Log::info("DOwnload With Resolution API");

        // Log::info("Using yt-dlp format: " . $format);

        // Step 1: Get the video title using yt-dlp
        $titleCommand = "C:\\yt-dlp\\yt-dlp.exe --get-title \"$url\"";
        exec($titleCommand, $titleOutput, $titleStatus);

        // Check if the title retrieval was successful


        // Step 2: Get the download URL with the specified format
        $command = "C:\\yt-dlp\\yt-dlp.exe -f \"$format\" --get-url \"$url\"";

        // Log the yt-dlp command for debugging
        // Log::info("Running yt-dlp command: " . $command);

        // Capture output and error
        exec(
            $command,
            $output,
            $status
        );

        // Log the output and status for debugging
        // Log::info("yt-dlp command output: " . implode("\n", $output));
        // Log::info("yt-dlp command status: " . $status);

        // Check if the command was successful
        if ($status !== 0) {
            return response()->json(['error' => 'Unable to fetch video.'], 500);
        }

        // Get the download URL
        $downloadUrl = trim(implode("\n", $output));

        // Check if the download URL is empty
        if (empty($downloadUrl)) {
            // Log::error("Download URL is empty. Please verify the resolution and video availability.");
            return response()->json(['error' => 'Unable to fetch valid video URL.'], 500);
        }

        // Log the download URL for debugging
        // Log::info("Download URL: " . $downloadUrl);

        // Step 3: Download the video content
        try {
            // Get the video content
            $videoContent = Http::get($downloadUrl)->body();


            // Check and Login the video title
            if ($titleStatus === 0 && !empty($titleOutput)) {
                $videoTitle = trim(implode("\n", $titleOutput));
                // Log::info("Video Title: " . $videoTitle); // Log the video title
            } else {
                Log::warning("Failed to fetch video title for URL: " . $url);
                $videoTitle = 'Unknown Title'; // Default title if retrieval fails
            }
            // Log the video content size for debugging
            // Log::info("Video content size: " . strlen($videoContent));

            // Generate a filename for the video
            $filename = 'video_' . time() . '.mp4'; // Change to the appropriate format if needed

            // Store the video in the storage folder (public disk for access via URL)
            Storage::disk('public')->put('videos/' . $filename, $videoContent);

            // Return the URL of the stored video
            $videoUrl = Storage::url('videos/' . $filename);

            // Include the video title in the response
            return $this->success('Video downloaded successfully', [
                'video' => $videoUrl,
                'title' => $videoTitle
            ]);
        } catch (\Exception $e) {
            // Handle errors while downloading the video
            // Log::error('Error downloading video: ' . $e->getMessage());
            return $this->error('Unable to download the video.', 500);
        }
    }



    private function getYtDlpFormat($resolution)
    {
        switch ($resolution) {
            case '256x144':
                return 'bestaudio[height<=144]+bestvideo[height<=144]/best[height<=144]';
            case '426x240':
                return 'bestaudio[height<=240]+bestvideo[height<=240]/best[height<=240]';
            case '640x360':
                return 'bestaudio[height<=360]+bestvideo[height<=360]/best[height<=360]';
            case '1280x720':
                return 'bestaudio[height<=720]+bestvideo[height<=720]/best[height<=720]';
            case '1920x1080':
                return 'bestaudio[height<=1080]+bestvideo[height<=1080]/best[height<=1080]';
            default:
                return 'best';
        }
    }
}
