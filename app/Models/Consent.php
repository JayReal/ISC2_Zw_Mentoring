<?php

namespace App\Models;

use Database\Factories\ConsentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'type', 'granted', 'policy_version', 'recorded_at', 'withdrawn_at'])]
class Consent extends Model
{
    /** @use HasFactory<ConsentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['granted' => 'boolean', 'recorded_at' => 'datetime', 'withdrawn_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
