<?php

namespace App\Services\Automation;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AiCaptionService
{
    public function generateCaption(): string
    {
        $endpoint = (string) config('automation.ai_caption.endpoint');
        $apiKey = (string) config('automation.ai_caption.key');
        $model = (string) config('automation.ai_caption.model', 'gpt-4o-mini');

        if ($endpoint === '' || $apiKey === '') {
            throw new RuntimeException('AI caption service is not configured.');
        }

        $response = Http::timeout(45)
            ->withToken($apiKey)
            ->post($endpoint, [
                'model' => $model,
                'temperature' => 0.8,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You write concise, engaging Facebook captions for a business page.',
                    ],
                    [
                        'role' => 'user',
                        'content' => 'Write one short Facebook caption for today. Add 3-5 relevant hashtags. No emojis.',
                    ],
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('AI caption generation failed: ' . $response->body());
        }

        $caption = trim((string) data_get($response->json(), 'choices.0.message.content', ''));

        if ($caption === '') {
            throw new RuntimeException('AI caption response was empty.');
        }

        return $caption;
    }
}
