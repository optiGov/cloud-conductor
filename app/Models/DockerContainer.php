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
 * @property string $uuid
 * @property string $restart_policy
 * @property string $hostname
 * @property array $volumes
 * @property array $networks
 * @property array $ports
 * @property array $environment
 * @property array $extra_hosts
 * @property float $deploy_resources_limits_cpu
 * @property integer $deploy_resources_limits_memory
 * @property float $deploy_resources_reservations_cpu
 * @property integer $deploy_resources_reservations_memory
 * @property boolean $daily_update
 * @property string $daily_update_time
 * @property Server $server
 * @property DockerImage $dockerImage
 */
class DockerContainer extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = "docker_container";

    /**
     * @var string[]
     */
    protected $guarded = ["id"];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "volumes" => "array",
        "networks" => "array",
        "ports" => "array",
        "environment" => "array",
        "extra_hosts" => "array",
    ];

    /**
     * @return BelongsTo
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * @return BelongsTo
     */
    public function dockerImage(): BelongsTo
    {
        return $this->belongsTo(DockerImage::class);
    }

    /**
     * @return string
     */
    public function getContainerName(): string
    {
        return Str::replace(
            ["_", ".", " ", "ü", "ä", "ö", "ß", "Ü", "Ä", "Ö"],
            ["-", "-", "-", "ue", "ae", "oe", "ss", "Ue", "Ae", "Oe"],
            $this->name);
    }

    public function getDockerComposeContent(): string
    {
        // build volumes
        $appVolumes = empty($this->volumes) ? "" : "    volumes:\n";
        foreach ($this->volumes as $host => $container) {
            $appVolumes .= "      - $host:$container\n";
        }

        // build appNetworks
        $appNetworks = (empty($this->networks) && !$this->hostname) ? "" : "    networks:\n";
        $appNetworks .= !$this->hostname ? "" : "      # reverse proxy network\n      reverse-proxy_container-network:\n";
        foreach ($this->networks as $data) {
            $network = DockerNetwork::find($data["network"]);
            $ipAddress = $data["ip_address"] ?? null;

            if ($network) {
                $appNetworks .= "      {$network->getNetworkName()}:\n";
                if ($ipAddress) {
                    $appNetworks .= "        ipv4_address: $ipAddress\n";
                }
            }
        }

        // build environment
        $environment = "";
        foreach ($this->environment as $key => $value) {
            $environment .= "      - $key=$value\n";
        }

        // build ports
        $ports = empty($this->ports) ? "" : "    expose:\n";
        foreach ($this->ports as $port) {
            $ports .= "      - {$port["port"]}\n";
        }

        // build extra hosts
        $extraHosts = empty($this->extra_hosts) ? "" : "    extra_hosts:\n";
        foreach ($this->extra_hosts as $host => $ip) {
            $extraHosts .= "      - $host:$ip\n";
        }

        // build resources
        $resources = ($this->deploy_resources_limits_cpu
            || $this->deploy_resources_limits_memory
            || $this->deploy_resources_reservations_cpu
            || $this->deploy_resources_reservations_memory)
            ? "    deploy:\n      resources:\n"
            : "";
        if ($this->deploy_resources_limits_cpu || $this->deploy_resources_limits_memory) {
            $resources .= "        limits:\n";
            if ($this->deploy_resources_limits_cpu) {
                $resources .= "          cpus: '{$this->deploy_resources_limits_cpu}'\n";
            }
            if ($this->deploy_resources_limits_memory) {
                $resources .= "          memory: {$this->deploy_resources_limits_memory}M\n";
            }
        }
        if ($this->deploy_resources_reservations_cpu || $this->deploy_resources_reservations_memory) {
            $resources .= "        reservations:\n";
            if ($this->deploy_resources_reservations_cpu) {
                $resources .= "          cpus: '{$this->deploy_resources_reservations_cpu}'\n";
            }
            if ($this->deploy_resources_reservations_memory) {
                $resources .= "          memory: {$this->deploy_resources_reservations_memory}M\n";
            }
        }

        // build networks
        $networks = "";
        foreach ($this->networks as $data) {
            $network = DockerNetwork::find($data["network"]);
            if ($network) {
                $networks .= "  {$network->getNetworkName()}:\n    external: true\n";
            }
        }

        // build volumes
        $volumes = empty($this->volumes) ? "" : "volumes:\n";
        foreach ($this->volumes as $host => $container) {
            $volumes .= "  $host:\n";

            // check if $host contains a uuid and references another DockerContainer's volume
            if (Str::contains($host, "_")) {
                $uuid = Str::before($host, "_");
                $container = DockerContainer::where("uuid", $uuid)->first();
                if ($container) {
                    $volumes .= "    external: true\n";
                    $volumes .= "    name: $host\n";
                }
            }
        }

        // return docker-compose content
        return <<<EOF
version: "3.8"
services:
  # application
  app:
    # image of the container
    image: {$this->dockerImage->image}
    # always restart the container to prevent downtimes
    restart: {$this->restart_policy}
    # unique container name
    container_name: {$this->getContainerName()}
    # mount volumes
$appVolumes
    # connect to networks
$appNetworks
    # configure reverse proxy
    environment:
      - VIRTUAL_HOST={$this->hostname}
      - LETSENCRYPT_HOST={$this->hostname}
      - LETSENCRYPT_EMAIL={$this->server->reverse_proxy_acme_default_email}
$environment
    # expose ports
$ports
    # extra hosts
$extraHosts
    # resource limits
$resources

# network configuration
networks:
  # external nginx reverse proxy network
  reverse-proxy_container-network:
    external: true
$networks

# volume configuration
$volumes
EOF;
    }

}
