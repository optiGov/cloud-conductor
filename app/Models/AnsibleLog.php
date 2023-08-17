<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property-read integer $id
 * @property string $command
 * @property array $result
 * @property-read string $result_json
 * @property-read boolean $success
 * @property-read ?string $first_error_message
 * @property-read ?string $first_success_message
 * @property string $created_at
 * @property string $updated_at
 * @property Server $server
 * @property Key $key
 * @property User $user
 */
class AnsibleLog extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = "ansible_log";

    /**
     * @var string[]
     */
    protected $guarded = ["id"];

    /**
     * @var string[]
     */
    protected $casts = [
        "result" => "array"
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
    public function key(): BelongsTo
    {
        return $this->belongsTo(Key::class);
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return bool
     */
    public function getSuccessAttribute(): bool
    {
        $success = true;

        // iterate over all hosts
        foreach ($this->result["stats"] as $key => $value) {
            if ($value["unreachable"] > 0 || $value["failures"] > 0) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * @return string|null
     */
    public function getErrorMessageAttribute(): string|null
    {
        foreach ($this->result["plays"] as $play) {
            foreach ($play["tasks"] as $task) {
                foreach ($task["hosts"] as $host) {
                    $failed = $host["failed"] ?? false;
                    if ($failed) {
                        return $host["stderr"] ?? null;
                    }
                }
            }
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getFirstErrorMessageAttribute(): string|null
    {
        foreach ($this->result["plays"] as $play) {
            foreach ($play["tasks"] as $task) {
                foreach ($task["hosts"] as $host) {
                    $failed = $host["failed"] ?? false;
                    if ($failed) {
                        return $host["stderr"] ?: $host["msg"] ?? null;
                    }
                }
            }
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getFirstSuccessMessageAttribute(): string|null
    {
        foreach ($this->result["plays"] as $play) {
            foreach ($play["tasks"] as $task) {
                foreach ($task["hosts"] as $host) {
                    $failed = $host["failed"] ?? false;
                    if (!$failed) {
                        return $host["stdout"] ?: $host["msg"] ?? null;
                    }
                }
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function getResultJsonAttribute(): string
    {
        return json_encode($this->result);
    }

}
