<?php

namespace App\Ansible\Playbook;

use App\Ansible\Ansible;
use App\Ansible\Process\Process;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Playbook
{
    /**
     * @var string
     */
    protected string $directory;

    public function __construct()
    {
    }

    /**
     * Runs preparations for the playbook.
     *
     * @return $this
     */
    public function prepare(Ansible $ansible, Process $process): static
    {
        return $this;
    }

    /**
     * Cleans up after the playbook.
     *
     * @return $this
     */
    public function cleanup(Ansible $ansible, Process $process): static
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return resource_path("playbooks/{$this->directory}/playbook.yaml");
    }

    protected function newTemporaryFile(): string
    {
        return storage_path("app/tmp/" . Str::uuid());
    }

    protected function newTemporaryFolder(): string
    {
        $path = storage_path("app/tmp/" . Str::uuid());
        File::makeDirectory($path);
        return $path;
    }
}
