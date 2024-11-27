<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * @property-read integer $id
 * @property string $name
 * @property string $psk
 * @property string $local_ip
 * @property ?string $local_id
 * @property string $local_subnet
 * @property string $remote_ip
 * @property ?string $remote_id
 * @property string $remote_subnet
 * @property boolean $separate_connections
 * @property string $ike_version
 * @property string $ike_encryption
 * @property string $ike_hash
 * @property string $ike_dh_group
 * @property string $esp_encryption
 * @property string $esp_hash
 * @property string $esp_dh_group
 * @property integer $ike_lifetime
 * @property integer $key_lifetime
 * @property array $routing
 * @property boolean $health_check_enabled
 * @property ?string $health_check_command
 * @property ?Server $server
 * @property ?JumpHost $jumpHost
 */
class IPSecTunnel extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = "ipsec_tunnel";

    /**
     * @var string[]
     */
    protected $guarded = ["id"];

    /**
     * @var string[]
     */
    protected $casts = [
        "routing" => "array"
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
    public function jumpHost(): BelongsTo
    {
        return $this->belongsTo(JumpHost::class);
    }

    /**
     * @return string
     */
    public function getVTIName(): string
    {
        return "vti" . $this->id;
    }

    /**
     * @return string
     */
    public function getMark(): string
    {
        return "10{$this->id}";
    }

    /**
     * @return string
     */
    public function getIPSecSecretsSection(): string
    {
        // determine local and remote identifiers
        $localIdentifier = $this->local_id ?? $this->local_ip;
        $remoteIdentifier = $this->remote_id ?? $this->remote_ip;

        return <<<EOF
# tunnel: {$this->name}
{$localIdentifier} {$remoteIdentifier} : PSK "{$this->psk}"
EOF;
    }


    /**
     * @return string
     */
    public function getIPSecConfigSection(): string
    {
        $additionalConfigurations = "";
        $remoteSubnet = $this->remote_subnet;

        if($this->separate_connections) {
            $networks = collect(explode(",", $this->remote_subnet));
            $remoteSubnet = $networks->first();
            $networks->shift();

            $networks->each(function($network, $index) use (&$additionalConfigurations) {
                $connectionName = $this->getConnectionNames()->get($index + 1);
                $additionalConfigurations .= <<<EOF

conn $connectionName
        also={$this->name}
        rightsubnet={$network}

EOF;

            });
        }
        $config = <<<EOF
conn {$this->name}
        keyexchange=ike{$this->ike_version}
        ike={$this->ike_encryption}-{$this->ike_hash}-{$this->ike_dh_group}
        esp={$this->esp_encryption}-{$this->esp_hash}-{$this->esp_dh_group}
        left={$this->local_ip}
        leftid={$this->local_id}
        leftsubnet={$this->local_subnet}
        right={$this->remote_ip}
        rightid={$this->remote_id}
        rightsubnet={$remoteSubnet}
        ikelifetime={$this->ike_lifetime}s
        keylife={$this->key_lifetime}s
        authby=secret
        auto=add
        mark={$this->getMark()}
$additionalConfigurations
EOF;



        return $config;
    }

    /**
     * @return string
     */
    public function getNetplanConfigSection(): string
    {
        // build routes
        $routes = "";
        foreach ($this->routing as $route) {
            $routes .= "        - to: {$route['remote_network']}\n";
        }

        return <<<EOF
    # tunnel: {$this->name}
    {$this->getVTIName()}:
      mode: vti
      local: {$this->local_ip}
      remote: {$this->remote_ip}
      key: {$this->getMark()}
      routes:
{$routes}
EOF;
    }

    /**
     * @return Collection
     */
    public function getIPTablesCommands(): Collection
    {
        $commands = new Collection();

        // add commands for forward chain
        foreach ($this->routing as $routing) {
            $network = DockerNetwork::find($routing["local_network"]);
            $destination = $routing["remote_network"];
            $commands->add("iptables -A FORWARD-CLOUD-CONDUCTOR -s {$destination} -d {$network->subnet} -i {$this->getVTIName()} -j RETURN");
        }
        $commands->add("iptables -A FORWARD-CLOUD-CONDUCTOR -i {$this->getVTIName()} -j DROP");

        // add commands for post routing chain
        foreach ($this->routing as $routing) {
            $network = DockerNetwork::find($routing["local_network"]);
            $destination = $routing["remote_network"];
            $commands->add("iptables -t nat -A POSTROUTING-CLOUD-CONDUCTOR -d {$destination} -o {$this->getVTIName()} -j SNAT --to-source {$network->getGatewayAddress()}");
        }
        $commands->add("iptables -t nat -A POSTROUTING-CLOUD-CONDUCTOR -o {$this->getVTIName()} -j RETURN");

        return $commands;
    }

    public function getConnectionNames(): Collection
    {
        $connectionNames = collect([$this->name]);

        if($this->separate_connections) {
            $networks = collect(explode(",", $this->remote_subnet));
            $networks->shift();

            $networks->each(function($_, $index) use (&$connectionNames) {
                $index += 2;
                $connectionNames->add("{$this->name}{$index}");
            });
        }

        return $connectionNames;
    }
}
