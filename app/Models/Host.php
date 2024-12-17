<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

abstract class Host extends Model
{
    public function jumpHost(): BelongsTo
    {
        return $this->belongsTo(JumpHost::class);
    }

    public function key(): BelongsTo
    {
        return $this->belongsTo(Key::class);
    }
}
