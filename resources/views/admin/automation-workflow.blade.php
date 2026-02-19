@extends('layouts.admin')

@section('content')
<style>
    :root {
        --aw-bg: #f3f7fc;
        --aw-card: #ffffff;
        --aw-ink: #0f172a;
        --aw-muted: #475569;
        --aw-border: #dbe5f3;
        --aw-primary: #0b6bcb;
        --aw-success: #0f9d58;
        --aw-danger: #d93025;
        --aw-warning: #f59e0b;
    }

    .aw-shell {
        background: linear-gradient(135deg, #e9f2ff 0%, #f8fbff 50%, #edf7ff 100%);
        border: 1px solid var(--aw-border);
        border-radius: 20px;
        padding: 24px;
    }

    .aw-title {
        color: var(--aw-ink);
        font-weight: 800;
        margin-bottom: 4px;
    }

    .aw-subtitle {
        color: var(--aw-muted);
        margin-bottom: 18px;
    }

    .aw-top-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    .aw-badge {
        border-radius: 999px;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        border: 1px solid transparent;
    }

    .aw-badge.idle { background: #e8eef7; color: #334155; border-color: #cfd8e5; }
    .aw-badge.running { background: #fff7e0; color: #8a5a00; border-color: #f4d38a; }
    .aw-badge.success { background: #e7f8ef; color: #0a6f3f; border-color: #b7e6cd; }
    .aw-badge.failed { background: #fdeceb; color: #b42318; border-color: #f8b4b0; }
    .aw-badge.disabled { background: #f1f5f9; color: #64748b; border-color: #d6dee8; }
    .aw-badge.skipped { background: #fef3c7; color: #92400e; border-color: #fcd34d; }

    .aw-controls {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .aw-flow {
        display: grid;
        grid-template-columns: repeat(9, minmax(120px, 1fr));
        gap: 8px;
        align-items: center;
        margin: 14px 0 22px;
        overflow-x: auto;
        padding-bottom: 10px;
    }

    .aw-step {
        min-width: 180px;
        background: var(--aw-card);
        border: 1px solid var(--aw-border);
        border-radius: 14px;
        padding: 14px;
        box-shadow: 0 8px 24px rgba(11, 54, 95, 0.08);
    }

    .aw-step h6 {
        margin: 0 0 6px;
        color: var(--aw-ink);
        font-weight: 700;
    }

    .aw-step p {
        margin: 0 0 10px;
        color: var(--aw-muted);
        font-size: 13px;
        line-height: 1.4;
    }

    .aw-arrow {
        text-align: center;
        color: #7aa7d8;
        font-size: 20px;
        font-weight: 900;
    }

    .aw-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 14px;
    }

    .aw-panel {
        background: #fff;
        border: 1px solid var(--aw-border);
        border-radius: 14px;
        padding: 14px;
    }

    .aw-panel h6 {
        margin-bottom: 10px;
        color: #0f172a;
        font-weight: 700;
    }

    .aw-preview img {
        width: 100%;
        max-height: 180px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid var(--aw-border);
    }

    .aw-meta {
        color: #334155;
        font-size: 13px;
        margin: 4px 0;
    }

    @media (max-width: 992px) {
        .aw-flow {
            grid-template-columns: 1fr;
        }

        .aw-arrow {
            transform: rotate(90deg);
            margin: 4px 0;
        }

        .aw-step {
            min-width: 100%;
        }
    }
</style>

<div class="aw-shell">
    <div class="aw-top-row">
        <div>
            <h4 class="aw-title">AI Automation Workflow</h4>
            <p class="aw-subtitle">Daily automated Facebook content pipeline with AI caption, AI image, publish, and run logging.</p>
        </div>
        <span class="aw-badge {{ $workflow->is_enabled ? 'success' : 'disabled' }}">
            {{ $workflow->is_enabled ? 'Enabled' : 'Disabled' }}
        </span>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="aw-controls mb-3">
        <form action="{{ route('admin.automation.run-now') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary" {{ $isRunning ? 'disabled' : '' }}>
                <i class="fa-solid fa-play"></i> Run Workflow Now
            </button>
        </form>

        <form action="{{ route('admin.automation.disable') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-danger" {{ ! $workflow->is_enabled ? 'disabled' : '' }}>
                <i class="fa-solid fa-pause"></i> Disable Automation
            </button>
        </form>

        <form action="{{ route('admin.automation.enable') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-success" {{ $workflow->is_enabled ? 'disabled' : '' }}>
                <i class="fa-solid fa-power-off"></i> Enable Automation
            </button>
        </form>
    </div>

    <div class="aw-flow">
        <div class="aw-step">
            <h6><i class="fa-regular fa-clock"></i> Trigger</h6>
            <p>Starts daily via cron or manually by admin.</p>
            <span class="aw-badge {{ $stepStatus['trigger'] }}">{{ ucfirst($stepStatus['trigger']) }}</span>
        </div>
        <div class="aw-arrow"><i class="fa-solid fa-arrow-right"></i></div>
        <div class="aw-step">
            <h6><i class="fa-solid fa-pen-nib"></i> AI Caption Generator</h6>
            <p>Creates one caption for today’s Facebook post.</p>
            <span class="aw-badge {{ $stepStatus['caption'] }}">{{ ucfirst($stepStatus['caption']) }}</span>
        </div>
        <div class="aw-arrow"><i class="fa-solid fa-arrow-right"></i></div>
        <div class="aw-step">
            <h6><i class="fa-regular fa-image"></i> AI Image Generator</h6>
            <p>Creates one image and stores it locally.</p>
            <span class="aw-badge {{ $stepStatus['image'] }}">{{ ucfirst($stepStatus['image']) }}</span>
        </div>
        <div class="aw-arrow"><i class="fa-solid fa-arrow-right"></i></div>
        <div class="aw-step">
            <h6><i class="fa-brands fa-facebook"></i> Facebook Page Post</h6>
            <p>Publishes caption and image via Graph API.</p>
            <span class="aw-badge {{ $stepStatus['facebook'] }}">{{ ucfirst($stepStatus['facebook']) }}</span>
        </div>
        <div class="aw-arrow"><i class="fa-solid fa-arrow-right"></i></div>
        <div class="aw-step">
            <h6><i class="fa-solid fa-list-check"></i> Success / Error Log</h6>
            <p>Stores each attempt status and error details.</p>
            <span class="aw-badge {{ $stepStatus['log'] }}">{{ ucfirst($stepStatus['log']) }}</span>
        </div>
    </div>

    <div class="aw-info-grid">
        <div class="aw-panel">
            <h6>Workflow Status</h6>
            <p class="aw-meta"><strong>Last run:</strong> {{ optional($workflow->last_run_at)->toDayDateTimeString() ?? 'Never' }}</p>
            <p class="aw-meta"><strong>Last status:</strong>
                <span class="aw-badge {{ $workflow->last_status }}">{{ ucfirst($workflow->last_status) }}</span>
            </p>
            <p class="aw-meta"><strong>Current mode:</strong> {{ $workflow->is_enabled ? 'Automation Active' : 'Automation Disabled' }}</p>
        </div>

        <div class="aw-panel aw-preview">
            <h6>Last Post Preview</h6>
            @if($latestSuccessPost)
                @if($latestSuccessPost->image_path)
                    <img src="{{ asset('storage/' . $latestSuccessPost->image_path) }}" alt="Last posted image">
                @endif
                <p class="aw-meta mt-2"><strong>Caption:</strong> {{ $latestSuccessPost->caption }}</p>
                <p class="aw-meta"><strong>Posted at:</strong> {{ optional($latestSuccessPost->posted_at)->toDayDateTimeString() }}</p>
            @else
                <p class="aw-meta">No successful post yet.</p>
            @endif
        </div>

        <div class="aw-panel">
            <h6>Last Attempt</h6>
            @if($latestAttempt)
                <p class="aw-meta"><strong>Status:</strong>
                    <span class="aw-badge {{ $latestAttempt->status }}">{{ ucfirst($latestAttempt->status) }}</span>
                </p>
                <p class="aw-meta"><strong>Time:</strong> {{ optional($latestAttempt->posted_at)->toDayDateTimeString() ?? $latestAttempt->created_at->toDayDateTimeString() }}</p>
                <p class="aw-meta"><strong>Error:</strong> {{ $latestAttempt->error_message ?: 'None' }}</p>
            @else
                <p class="aw-meta">No attempts recorded.</p>
            @endif
        </div>
    </div>
</div>
@endsection
