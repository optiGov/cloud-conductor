<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read integer $id
 * @property string $name
 * @property string $host
 * @property string $reverse_proxy_acme_ca_provider
 * @property string $reverse_proxy_acme_default_email
 * @property string $reverse_proxy_acme_api_key
 * @property boolean $unattended_upgrades_enabled
 * @property string|null $unattended_upgrades_time
 * @property DockerImage[] $dockerImages
 * @property DockerNetwork[] $dockerNetworks
 * @property DockerContainer[] $dockerContainers
 * @property IPSecTunnel[] $ipsecTunnels
 */
class Server extends Model
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

}
