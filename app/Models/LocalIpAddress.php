<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property-read integer $id
 * @property string $name
 * @property string $ip_address
 * @property JumpHost $jumpHost
 */
class LocalIpAddress extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = "local_ip_address";

    /**
     * @var string[]
     */
    protected $guarded = ["id"];

    /**
     * @var string[]
     */
    protected $casts = [
    ];

    /**
     * @return BelongsTo
     */
    public function jumpHost(): BelongsTo
    {
        return $this->belongsTo(JumpHost::class);
    }

}
