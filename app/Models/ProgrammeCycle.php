<?php

namespace App\Models;

use Database\Factories\ProgrammeCycleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'starts_on', 'ends_on', 'status', 'target_participants', 'settings'])]
class ProgrammeCycle extends Model
{
    /** @use HasFactory<ProgrammeCycleFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date', 'settings' => 'array'];
    }
}
