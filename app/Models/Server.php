<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property-read integer $id
 * @property string $name
 * @property string $host
 * @property string $reverse_proxy_acme_ca_provider
 * @property string $reverse_proxy_acme_default_email
 * @property string $reverse_proxy_acme_api_key
 * @property boolean $unattended_upgrades_enabled
 * @property string|null $unattended_upgrades_time
 * @property JumpHost|null $jumpHost
 * @property Key|null $key
 * @property Collection<DockerImage> $dockerImages
 * @property Collection<DockerNetwork> $dockerNetworks
 * @property Collection<DockerContainer> $dockerContainers
 * @property Collection<IPSecTunnel> $ipsecTunnels
 * @property Collection<CronJob> $cronJobs
 */
class Server extends Host
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = "server";

    /**
     * @var string[]
     */
    protected $guarded = ["id"];

    /**
     * @return HasMany
     */
    public function dockerImages(): HasMany
    {
        return $this->hasMany(DockerImage::class);
    }

    /**
     * @return HasMany
     */
    public function dockerNetworks(): HasMany
    {
        return $this->hasMany(DockerNetwork::class);
    }

    /**
     * @return HasMany
     */
    public function dockerContainers(): HasMany
    {
        return $this->hasMany(DockerContainer::class);
    }

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
    public function cronJobs(): HasMany
    {
        return $this->hasMany(CronJob::class);
    }

    public function jumpHost(): BelongsTo
    {
        return $this->belongsTo(JumpHost::class);
    }

    public function key(): BelongsTo
    {
        return $this->belongsTo(Key::class);
    }
}
