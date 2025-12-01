<?php

namespace Infra\Report\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Infra\Report\Models\Evidence\Evidence;

class ScanEvidenceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $evidenceId) {}

    public function handle(): void
    {
        $evidence = Evidence::findOrFail($this->evidenceId);
        // TODO: integrate with clamd via TCP. For now mark as clean.
        $evidence->is_scanned = true;
        $evidence->scan_status = 'clean';
        $evidence->save();
    }
}

