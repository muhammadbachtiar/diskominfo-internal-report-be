<?php

namespace Infra\Asset\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Infra\Shared\Concerns\HasUuidV7;
use Infra\Shared\Models\BaseModel;
use Infra\User\Models\User;

class AssetMaintenance extends BaseModel
{
    use HasUuidV7;

    protected $fillable = [
        'description',
        'completion_note',
        'performed_by',
        'completed_by',
        'return_to_active_location',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'return_to_active_location' => 'boolean',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function markAsCompleted(?string $note = null, ?int $userId = null): self
    {
        $this->finished_at = now();
        $this->completion_note = $note;
        $this->completed_by = $userId ?? auth()->id();
        $this->save();

        // Update asset status based on return_to_active_location
        // If return_to_active_location is false, asset stays with borrower (borrowed status if there's an active loan)
        // If return_to_active_location is true, asset physically returns (available status)
        if ($this->return_to_active_location) {
            $newStatus = 'available';
        } else {
            // Check if asset has an active loan
            $newStatus = $this->asset->currentLoan ? 'borrowed' : 'available';
        }

        $this->asset->update(['status' => $newStatus]);

        return $this;
    }
}
