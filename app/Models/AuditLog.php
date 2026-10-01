<?php

namespace App\Models;

use Database\Factories\AuditLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;

#[Fillable(['actor_id', 'action', 'subject_type', 'subject_id', 'changes', 'reason', 'ip_address'])]
class AuditLog extends Model
{
    /** @use HasFactory<AuditLogFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['changes' => 'array'];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public static function record(Request $request, string $action, Model $subject, array $changes = [], ?string $reason = null): void
    {
        self::create(['actor_id' => $request->user()?->id, 'action' => $action, 'subject_type' => $subject->getMorphClass(), 'subject_id' => $subject->getKey(), 'changes' => $changes, 'reason' => $reason, 'ip_address' => $request->ip()]);
    }
}
