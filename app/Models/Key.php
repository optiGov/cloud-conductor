<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * @property-read integer $id
 * @property User $user
 * @property string $uuid
 * @property string $name
 * @property string $username
 * @property-read bool $encrypted
 * @property-read Server[] $servers
 */
class Key extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = "key";

    /**
     * @var string[]
     */
    protected $guarded = ["id"];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany
     */
    public function servers(): BelongsToMany
    {
        return $this->belongsToMany(Server::class);
    }

    /**
     * Create a new SSH key in `resources/keys` directory on creation.
     */
    public function createKey(string $password): void
    {
        // get path
        $path = $this->getPath();

        // create key without passphrase
        $this->exec("ssh-keygen -t ed25519 -f {$path} -q -N '' -C 'cloud-conductor-key-{$this->uuid}-user-{$this->user_id}'");

        // encrypt key
        $this->encryptKey($password);
    }

    /**
     * Returns the path to the key.
     *
     * @return string
     */
    public function getPath(): string
    {
        return storage_path("app/keys/{$this->uuid}");
    }

    /**
     * Returns the path to the public key.
     *
     * @return string
     */
    public function getPublicKeyPath(): string
    {
        return $this->getPath() . ".pub";
    }

    /**
     * Encrypts the key with the given password.
     *
     * @param string $password
     * @return void
     * @throws \Exception
     */
    public function encryptKey(string $password){

        // return if key is already encrypted
        if($this->encrypted){
            return;
        }

        // get file content
        $fileContent = File::get($this->getPath());

        // decrypt key with password and aes-256-cbc
        $encrypter = new Encrypter(md5($password), "aes-256-cbc");
        $encryptedKey = $encrypter->encryptString($fileContent);

        // store decrypted key
        File::put($this->getPath(), $encryptedKey);
    }

    /**
     * Encrypts the key with the given password.
     *
     * @param string $password
     * @return void
     * @throws \Exception
     */
    public function decryptKey(string $password){
        // return if key is not encrypted
        if(!$this->encrypted){
            return;
        }

        // get file content
        $fileContent = File::get($this->getPath());

        // decrypt key with password and aes-256-cbc
        $encrypter = new Encrypter(md5($password), "aes-256-cbc");
        $decryptedKey = $encrypter->decryptString($fileContent);

        // store decrypted key
        File::put($this->getPath(), $decryptedKey);
    }

    /**
     * @return bool
     */
    protected function getEncryptedAttribute(): bool
    {
        // return whether key file contains `BEGIN OPENSSH PRIVATE KEY`
        return !Str::contains(
            File::get($this->getPath()),
            "BEGIN OPENSSH PRIVATE KEY"
        );
    }

    public function deleteKey()
    {
        // delete key
        File::delete($this->getPath());
        File::delete($this->getPublicKeyPath());
    }

    /**
     * Executes a command.
     *
     * @param string $command
     * @return void
     */
    protected function exec(string $command): void
    {
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \RuntimeException("Command [{$command}] failed with return code {$returnCode}.");
        }
    }
}
