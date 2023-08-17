<?php

namespace App\Ansible\Playbook\Books;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Playbook;
use App\Ansible\Process\Process;
use App\Models\DockerContainer;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PlaybookDockerContainerStart extends Playbook
{
    /**
     * @var string
     */
    protected string $directory = "docker-container.start";

    /**
     * @param DockerContainer $container
     */
    public function __construct(
        protected DockerContainer $container
    )
    {
        parent::__construct();
    }

    /**
     * @param Ansible $ansible
     * @param Process $process
     * @return $this
     */
    public function prepare(Ansible $ansible, Process $process): static
    {
        $ansible->variable("uuid", $this->container->uuid);

        // return self
        return $this;
    }
}
