<?php

use App\Http\Controllers\Admin\AutomationWorkflowController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/automation-workflow', [AutomationWorkflowController::class, 'index'])->name('automation.index');
    Route::post('/automation-workflow/run-now', [AutomationWorkflowController::class, 'runNow'])->name('automation.run-now');
    Route::post('/automation-workflow/enable', [AutomationWorkflowController::class, 'enable'])->name('automation.enable');
    Route::post('/automation-workflow/disable', [AutomationWorkflowController::class, 'disable'])->name('automation.disable');
});
