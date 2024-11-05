<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * @property-read integer $id
 * @property string $name
 * @property string $command
 * @property string $status
 * @property ?string $minute
 * @property ?string $hour
 * @property ?string $day
 */
class CronJob extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = "cron_job";

    /**
     * @var string[]
     */
    protected $guarded = ["id"];

    /**
     * @return BelongsTo
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

}
