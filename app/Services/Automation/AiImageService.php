<?php

namespace App\Services\Automation;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class AiImageService
{
    public function generateImage(string $caption): string
    {
        $endpoint = (string) config('automation.ai_image.endpoint');
        $apiKey = (string) config('automation.ai_image.key');
        $size = (string) config('automation.ai_image.size', '1024x1024');

        if ($endpoint === '' || $apiKey === '') {
            throw new RuntimeException('AI image service is not configured.');
        }

        $response = Http::timeout(90)
            ->withToken($apiKey)
            ->post($endpoint, [
                'prompt' => 'Create a professional social media image based on this caption: ' . $caption,
                'size' => $size,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('AI image generation failed: ' . $response->body());
        }

        $imageUrl = (string) data_get($response->json(), 'data.0.url', '');
        if ($imageUrl === '') {
            throw new RuntimeException('AI image response did not return a URL.');
        }

        $imageBinary = Http::timeout(90)->get($imageUrl)->body();
        if ($imageBinary === '') {
            throw new RuntimeException('Generated image could not be downloaded.');
        }

        $path = 'automation/posts/' . now()->format('Y/m/d') . '/' . uniqid('post_', true) . '.png';
        Storage::disk('public')->put($path, $imageBinary);

        return $path;
    }
}
