<?php

namespace App\Services\Automation;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class FacebookPostService
{
    public function publish(string $caption, string $imagePath): array
    {
        $pageId = (string) config('automation.facebook.page_id');
        $pageToken = (string) config('automation.facebook.page_access_token');

        if ($pageId === '' || $pageToken === '') {
            throw new RuntimeException('Facebook API credentials are missing.');
        }

        if (! Storage::disk('public')->exists($imagePath)) {
            throw new RuntimeException('Image file not found for Facebook posting.');
        }

        $graphUrl = sprintf('https://graph.facebook.com/v22.0/%s/photos', $pageId);
        $file = fopen(Storage::disk('public')->path($imagePath), 'r');

        $response = Http::timeout(60)
            ->attach('source', $file, basename($imagePath))
            ->post($graphUrl, [
                'caption' => $caption,
                'access_token' => $pageToken,
            ]);

        if (is_resource($file)) {
            fclose($file);
        }

        if (! $response->successful()) {
            throw new RuntimeException('Facebook post failed: ' . $response->body());
        }

        return [
            'id' => (string) data_get($response->json(), 'id', ''),
            'post_id' => (string) data_get($response->json(), 'post_id', ''),
        ];
    }
}
