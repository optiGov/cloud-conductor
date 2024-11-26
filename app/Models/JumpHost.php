<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property-read integer $id
 * @property string $name
 * @property string $host
 * @property boolean $unattended_upgrades_enabled
 * @property string|null $unattended_upgrades_time
 * @property Collection<IPSecTunnel> $ipsecTunnels
 * @property Collection<Server> $servers
 */
class JumpHost extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = "jump_host";

    /**
     * @var string[]
     */
    protected $guarded = ["id"];

    /**
     * @return HasMany
     */
    public function ipsecTunnels(): HasMany
    {
        return $this->hasMany(IPSecTunnel::class);
    }

    /**
     * @return HasMany
     */
    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }
}
