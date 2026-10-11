<?php

namespace App\Models;

use Database\Factories\ParticipantRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'type', 'details', 'status', 'handled_by', 'resolution', 'resolved_at'])]
class ParticipantRequest extends Model
{
    /** @use HasFactory<ParticipantRequestFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'details' => 'encrypted',
            'resolution' => 'encrypted',
            'resolved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
