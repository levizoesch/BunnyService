<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * @author Levi Zoesch
 * @package Application\BunnyCDN\Service
 */
class BunnyService
{
    private string $bunnyKey;
    private string $storageZoneName;
    private string $hostname;

    public function __construct()
    {
        $this->bunnyKey = 'your-api-key-here';
        $this->storageZoneName = 'your-zone';
        $region = 'ny';
        $baseHostname = 'storage.bunnycdn.com';
        $this->hostname = empty($region) ? $baseHostname : "{$region}.{$baseHostname}";
    }

    /**
     * Upload a file to BunnyCDN
     *
     * @param string $filePath Local path to the file to be uploaded
     * @param string $remotePath Path where the file should be stored on BunnyCDN
     * @return bool|string Success or error message
     */
    public function uploadFile(string $filePath, string $remotePath): bool|string
    {
        try {
            $url = "https://{$this->hostname}/{$this->storageZoneName}/{$remotePath}";
            $response = Http::withHeaders([
                'AccessKey' => $this->bunnyKey,
                'Content-Type' => 'application/octet-stream',
            ])->attach('file', fopen($filePath, 'r'), basename($filePath))
                ->put($url);

            if ($response->successful()) {
                return "File uploaded successfully!";
            } else {
                Log::error("BunnyCDN Upload Error: " . $response->body());
                return "Upload failed: " . $response->body();
            }
        } catch (\Exception $e) {
            Log::error("BunnyCDN Upload Exception: " . $e->getMessage());
            return "Upload exception: " . $e->getMessage();
        }
    }

    /**
     * Delete a file from BunnyCDN
     *
     * @param string $remotePath Path of the file to delete on BunnyCDN
     * @return bool|string Success or error message
     */
    public function deleteFile(string $remotePath): bool|string
    {
        try {
            $url = "https://{$this->hostname}/{$this->storageZoneName}/{$remotePath}";
            $response = Http::withHeaders([
                'AccessKey' => $this->bunnyKey,
            ])->delete($url);

            if ($response->successful()) {
                return "File deleted successfully!";
            } else {
                Log::error("BunnyCDN Delete Error: " . $response->body());
                return "Delete failed: " . $response->body();
            }
        } catch (\Exception $e) {
            Log::error("BunnyCDN Delete Exception: " . $e->getMessage());
            return "Delete exception: " . $e->getMessage();
        }
    }

    /**
     * List all files in a directory on BunnyCDN
     *
     * @param string $directoryPath Directory path to list files
     * @return array|string List of files or error message
     */
    public function listFiles(string $directoryPath = '/'): array|string
    {
        try {
            $url = "https://{$this->hostname}/{$this->storageZoneName}/" . ltrim($directoryPath, '/');
            $response = Http::withHeaders([
                'AccessKey' => $this->bunnyKey,
                'Accept' => 'application/json',
            ])->get($url);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error("BunnyCDN List Files Error: " . $response->body());
                return "List files failed: " . $response->body();
            }
        } catch (\Exception $e) {
            Log::error("BunnyCDN List Files Exception: " . $e->getMessage());
            return "List files exception: " . $e->getMessage();
        }
    }

    /**
     * Download a file from BunnyCDN
     *
     * @param string $remotePath Path of the file to download on BunnyCDN
     * @return Response|string Response or error message
     */
    public function downloadFile(string $remotePath): string|Response
    {
        try {
            $url = "https://{$this->hostname}/{$this->storageZoneName}/{$remotePath}";
            $response = Http::withHeaders([
                'AccessKey' => $this->bunnyKey,
            ])->get($url);

            if ($response->successful()) {
                return $response;
            } else {
                Log::error("BunnyCDN Download Error: " . $response->body());
                return "Download failed: " . $response->body();
            }
        } catch (\Exception $e) {
            Log::error("BunnyCDN Download Exception: " . $e->getMessage());
            return "Download exception: " . $e->getMessage();
        }
    }
}
