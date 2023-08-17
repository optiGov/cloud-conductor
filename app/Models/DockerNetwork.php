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
 * @property string $subnet
 * @property Server $server
 */
class DockerNetwork extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = "docker_network";

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

    /**
     * Returns the x.x.x.1 address of the subnet.
     *
     * @return string
     */
    public function getGatewayAddress(): string
    {
        $parts = explode(".", $this->subnet);
        $parts[3] = 1;
        return implode(".", $parts);
    }

    public function getNetworkName(): string
    {
        return Str::replace(
            ["_", ".", " ", "ü", "ä", "ö", "ß", "Ü", "Ä", "Ö"],
            ["-", "-", "-", "ue", "ae", "oe", "ss", "Ue", "Ae", "Oe"],
            Str::lower($this->name)
        );
    }

}
