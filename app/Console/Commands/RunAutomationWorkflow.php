<?php

namespace App\Console\Commands;

use App\Services\Automation\AutomationWorkflowService;
use Illuminate\Console\Command;

class RunAutomationWorkflow extends Command
{
    protected $signature = 'automation:run-workflow {--trigger=cron : cron|manual}';

    protected $description = 'Run AI automation workflow with one-post-per-day protection.';

    public function handle(AutomationWorkflowService $workflowService): int
    {
        $trigger = (string) $this->option('trigger');
        $result = $workflowService->run($trigger);

        $status = strtoupper((string) ($result['status'] ?? 'unknown'));
        $message = (string) ($result['message'] ?? 'No message');

        $this->line(sprintf('[%s] %s', $status, $message));

        return (($result['status'] ?? '') === 'failed') ? self::FAILURE : self::SUCCESS;
    }
}
