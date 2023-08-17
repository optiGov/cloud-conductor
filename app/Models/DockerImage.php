<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property-read integer $id
 * @property string $image
 * @property ?string $registry
 * @property ?string $username
 * @property ?string $password
 * @property boolean $daily_update
 * @property string $daily_update_time
 * @property Server $server
 */
class DockerImage extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = "docker_image";

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
