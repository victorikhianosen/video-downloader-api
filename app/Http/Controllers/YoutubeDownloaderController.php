<?php

namespace App\Http\Controllers;

use App\Http\Requests\YoutubeDownloadRequest;
use App\Http\Requests\YoutubeResolutionRequest;
use App\Services\YoutubeService;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class YoutubeDownloaderController extends Controller
{


    use HttpResponses;

    private $youtubeService;

    public function __construct(YoutubeService $youtubeService)
    {
        $this->youtubeService = $youtubeService;
    }

    public function download(YoutubeDownloadRequest $request)
    {
        $validated = $request->validated();
        return $this->youtubeService->download($validated);
    }



    public function getAvailableQualities(YoutubeDownloadRequest $request)
    {
        // Validate the URL provided in the request
        $validated = $request->validated();

        return $this->youtubeService->availableQualities($validated);
    }

    public function downloadWithResolution(YoutubeResolutionRequest $request)
    {
        $validated = $request->validated();
        return $this->youtubeService->downloadWithResolution($validated);
    }

    // Helper function to map resolution to yt-dlp format
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


    // public function downloadWithResolution(Request $request)
    // {
    //     // Validate the YouTube URL and resolution
    //     $validated = $request->validate([
    //         'url' => 'required|url',
    //         'resolution' => 'required|in:256x144,426x240,640x360,1280x720,1920x1080',
    //     ]);

    //     $url = $validated['url'];
    //     $resolution = $validated['resolution'];

    //     // Construct the yt-dlp format string based on resolution
    //     $format = $this->getYtDlpFormat($resolution);

    //     // Full path to yt-dlp executable
    //     $command = "C:\\yt-dlp\\yt-dlp.exe -f $format --get-url $url";

    //     $command = "C:\\yt-dlp\\yt-dlp.exe -f best --get-url $url";


    //     // Log the yt-dlp command for debugging
    //     Log::info("Running yt-dlp command: " . $command);

    //     // Capture output and error
    //     exec($command, $output, $status);

    //     // Log the output and status for debugging
    //     Log::info("yt-dlp command output: " . implode("\n", $output));
    //     Log::info("yt-dlp command status: " . $status);

    //     // Check if the command was successful
    //     if ($status !== 0) {
    //         return response()->json(['error' => 'Unable to fetch video.'], 500);
    //     }

    //     // Get the download URL
    //     $downloadUrl = trim(implode("\n", $output));

    //     // Log the download URL for debugging
    //     Log::info("Download URL: " . $downloadUrl);

    //     // Download the video content using Laravel's HTTP client
    //     try {
    //         // Get the video content
    //         $videoContent = Http::get($downloadUrl)->body();

    //         // Log the video content size for debugging
    //         Log::info("Video content size: " . strlen($videoContent));

    //         // Generate a filename for the video
    //         $filename = 'video_' . time() . '.mp4';  // Change to the appropriate format if needed

    //         // Store the video in the storage folder (public disk for access via URL)
    //         Storage::disk('public')->put('videos/' . $filename, $videoContent);

    //         // Return the URL of the stored video
    //         $videoUrl = Storage::url('videos/' . $filename);

    //         return response()->json(['download_url' => $videoUrl]);
    //     } catch (\Exception $e) {
    //         // Handle errors while downloading the video
    //         Log::error('Error downloading video: ' . $e->getMessage());
    //         return response()->json(['error' => 'Unable to download the video.'], 500);
    //     }
    // }

    // // Helper function to map resolution to yt-dlp format
    // private function getYtDlpFormat($resolution)
    // {
    //     switch ($resolution) {
    //         case '256x144':
    //             return 'bestaudio[height<=144]+bestvideo[height<=144]/best[height<=144]';
    //         case '426x240':
    //             return 'bestaudio[height<=240]+bestvideo[height<=240]/best[height<=240]';
    //         case '640x360':
    //             return 'bestaudio[height<=360]+bestvideo[height<=360]/best[height<=360]';
    //         case '1280x720':
    //             return 'bestaudio[height<=720]+bestvideo[height<=720]/best[height<=720]';
    //         case '1920x1080':
    //             return 'bestaudio[height<=1080]+bestvideo[height<=1080]/best[height<=1080]';
    //         default:
    //             return 'best';
    //     }
    // }





    // public function getAvailableQualities(Request $request)
    // {
    //     // Validate the YouTube URL
    //     $request->validate([
    //         'url' => 'required|url',
    //     ]);

    //     $url = $request->input('url');

    //     // Full path to yt-dlp executable
    //     $command = "C:\\yt-dlp\\yt-dlp.exe -F $url";

    //     // Capture output and error
    //     exec($command, $output, $status);

    //     // Log output for debugging
    //     Log::info("yt-dlp command output: " . implode("\n", $output));

    //     // Check if the command was successful
    //     if ($status !== 0) {
    //         return response()->json(['error' => 'Unable to fetch video information.'], 500);
    //     }

    //     // Define the valid resolutions
    //     $validResolutions = ['1920x1080', '1280x720', '640x360', '426x240', '256x144'];

    //     // Parse the output to extract available qualities
    //     $qualities = [];
    //     foreach ($output as $line) {
    //         if (preg_match('/(\d+)\s+(\d+x\d+)\s+(\w+)\s+(.+)/', $line, $matches)) {
    //             // Only include qualities with valid resolutions
    //             if (in_array($matches[2], $validResolutions)) {
    //                 $qualities[] = [
    //                     'url' => $url,
    //                     'format_code' => $matches[1],
    //                     'resolution' => $matches[2],
    //                     'extension' => $matches[3],
    //                     'description' => $matches[4],
    //                 ];
    //             }
    //         }
    //     }

    //     // Return the available qualities
    //     return response()->json(['qualities' => $qualities]);
    // }




    // public function getAvailableQualities(Request $request)
    // {
    //     // Validate the YouTube URL
    //     $request->validate([
    //         'url' => 'required|url',
    //     ]);

    //     $url = $request->input('url');

    //     // Full path to yt-dlp executable
    //     $command = "C:\\yt-dlp\\yt-dlp.exe -F $url";

    //     // Capture output and error
    //     exec($command, $output, $status);

    //     // Log output for debugging
    //     Log::info("yt-dlp command output: " . implode("\n", $output));

    //     // Check if the command was successful
    //     if ($status !== 0) {
    //         return response()->json(['error' => 'Unable to fetch video information.'], 500);
    //     }

    //     // Parse the output to extract available qualities
    //     $qualities = [];
    //     foreach ($output as $line) {
    //         if (preg_match('/(\d+)\s+(\d+x\d+)\s+(\w+)\s+(.+)/', $line, $matches)) {
    //             $qualities[] = [
    //                 'url' => $url,
    //                 'format_code' => $matches[1],
    //                 'resolution' => $matches[2],
    //                 'extension' => $matches[3],
    //                 'description' => $matches[4],
    //             ];
    //         }
    //     }

    //     // Return the available qualities
    //     return response()->json(['qualities' => $qualities]);
    // }

}
