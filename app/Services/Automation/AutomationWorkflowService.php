<?php

namespace App\Services\Automation;

use App\Models\Post;
use App\Models\Workflow;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class AutomationWorkflowService
{
    public const WORKFLOW_NAME = 'AI Automation Workflow';
    public const RUN_LOCK_KEY = 'automation-workflow-run-lock';

    public function __construct(
        private readonly AiCaptionService $aiCaptionService,
        private readonly AiImageService $aiImageService,
        private readonly FacebookPostService $facebookPostService,
    ) {
    }

    /**
     * Runs the workflow once, with lock and one-post-per-day enforcement.
     */
    public function run(string $triggeredBy = 'cron'): array
    {
        $lock = Cache::lock(self::RUN_LOCK_KEY, 600);

        if (! $lock->get()) {
            return [
                'ok' => false,
                'status' => 'skipped',
                'message' => 'Workflow is already running.',
            ];
        }

        Cache::put($this->runningFlagKey(), true, now()->addMinutes(10));

        try {
            return DB::transaction(function () use ($triggeredBy) {
                $workflow = Workflow::query()->lockForUpdate()->firstOrCreate(
                    ['name' => self::WORKFLOW_NAME],
                    ['is_enabled' => true, 'last_status' => 'idle']
                );

                if (! $workflow->is_enabled) {
                    $this->logAttempt($workflow->id, 'skipped', 'Workflow is disabled. Triggered by ' . $triggeredBy);
                    $workflow->update(['last_run_at' => now(), 'last_status' => 'disabled']);

                    return ['ok' => false, 'status' => 'disabled', 'message' => 'Workflow is disabled.'];
                }

                $alreadyPostedToday = Post::query()
                    ->where('workflow_id', $workflow->id)
                    ->where('platform', 'facebook')
                    ->where('status', 'success')
                    ->whereDate('posted_at', today())
                    ->exists();

                if ($alreadyPostedToday) {
                    $this->logAttempt($workflow->id, 'skipped', 'Daily post already exists. Triggered by ' . $triggeredBy);
                    $workflow->update(['last_run_at' => now(), 'last_status' => 'skipped']);

                    return ['ok' => false, 'status' => 'skipped', 'message' => 'Already posted today.'];
                }

                try {
                    $caption = $this->aiCaptionService->generateCaption();
                    $imagePath = $this->aiImageService->generateImage($caption);
                    $this->facebookPostService->publish($caption, $imagePath);

                    Post::query()->create([
                        'workflow_id' => $workflow->id,
                        'caption' => $caption,
                        'image_path' => $imagePath,
                        'platform' => 'facebook',
                        'posted_at' => now(),
                        'status' => 'success',
                        'error_message' => null,
                    ]);

                    $workflow->update(['last_run_at' => now(), 'last_status' => 'success']);

                    return ['ok' => true, 'status' => 'success', 'message' => 'Workflow executed successfully.'];
                } catch (Throwable $exception) {
                    Post::query()->create([
                        'workflow_id' => $workflow->id,
                        'caption' => null,
                        'image_path' => null,
                        'platform' => 'facebook',
                        'posted_at' => now(),
                        'status' => 'failed',
                        'error_message' => $exception->getMessage(),
                    ]);

                    $workflow->update(['last_run_at' => now(), 'last_status' => 'failed']);

                    return [
                        'ok' => false,
                        'status' => 'failed',
                        'message' => $exception->getMessage(),
                    ];
                }
            });
        } finally {
            Cache::forget($this->runningFlagKey());
            optional($lock)->release();
        }
    }

    public function setEnabled(bool $enabled): Workflow
    {
        $workflow = Workflow::query()->firstOrCreate(
            ['name' => self::WORKFLOW_NAME],
            ['is_enabled' => true, 'last_status' => 'idle']
        );

        $workflow->update(['is_enabled' => $enabled]);

        return $workflow->fresh();
    }

    public function isRunning(): bool
    {
        return (bool) Cache::get($this->runningFlagKey(), false);
    }

    private function logAttempt(int $workflowId, string $status, ?string $errorMessage = null): void
    {
        Post::query()->create([
            'workflow_id' => $workflowId,
            'caption' => null,
            'image_path' => null,
            'platform' => 'facebook',
            'posted_at' => now(),
            'status' => $status,
            'error_message' => $errorMessage,
        ]);
    }

    private function runningFlagKey(): string
    {
        return self::RUN_LOCK_KEY . ':running';
    }
}
