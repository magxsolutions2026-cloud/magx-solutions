<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Workflow;
use App\Services\Automation\AutomationWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AutomationWorkflowController extends Controller
{
    public function __construct(private readonly AutomationWorkflowService $workflowService)
    {
    }

    public function index(): View
    {
        $workflow = Workflow::query()->firstOrCreate(
            ['name' => AutomationWorkflowService::WORKFLOW_NAME],
            ['is_enabled' => true, 'last_status' => 'idle']
        );

        $latestAttempt = Post::query()
            ->where('workflow_id', $workflow->id)
            ->latest('posted_at')
            ->latest('id')
            ->first();

        $latestSuccessPost = Post::query()
            ->where('workflow_id', $workflow->id)
            ->where('status', 'success')
            ->latest('posted_at')
            ->latest('id')
            ->first();

        $stepStatus = [
            'trigger' => $this->workflowService->isRunning() ? 'running' : 'idle',
            'caption' => $this->resolveStepStatus($latestAttempt, 'caption'),
            'image' => $this->resolveStepStatus($latestAttempt, 'image'),
            'facebook' => $this->resolveStepStatus($latestAttempt, 'facebook'),
            'log' => $latestAttempt?->status ?? 'idle',
        ];

        return view('admin.automation-workflow', [
            'workflow' => $workflow,
            'latestAttempt' => $latestAttempt,
            'latestSuccessPost' => $latestSuccessPost,
            'stepStatus' => $stepStatus,
            'isRunning' => $this->workflowService->isRunning(),
        ]);
    }

    public function runNow(): RedirectResponse
    {
        $result = $this->workflowService->run('manual');

        return back()->with($result['ok'] ? 'success' : 'error', (string) $result['message']);
    }

    public function enable(): RedirectResponse
    {
        $this->workflowService->setEnabled(true);

        return back()->with('success', 'Automation enabled.');
    }

    public function disable(): RedirectResponse
    {
        $this->workflowService->setEnabled(false);

        return back()->with('success', 'Automation disabled.');
    }

    private function resolveStepStatus(?Post $latestAttempt, string $step): string
    {
        if ($this->workflowService->isRunning()) {
            return 'running';
        }

        if (! $latestAttempt) {
            return 'idle';
        }

        if ($latestAttempt->status === 'success') {
            return 'success';
        }

        if ($latestAttempt->status === 'failed') {
            return 'failed';
        }

        if ($latestAttempt->status === 'skipped') {
            return $step === 'facebook' ? 'idle' : 'success';
        }

        return 'idle';
    }
}
