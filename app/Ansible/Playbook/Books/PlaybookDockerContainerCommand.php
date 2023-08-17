<?php

namespace App\Ansible\Playbook\Books;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Playbook;
use App\Ansible\Process\Process;
use App\Models\DockerContainer;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PlaybookDockerContainerCommand extends Playbook
{
    /**
     * @var string
     */
    protected string $directory = "docker-container.command";

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
        $ansible->variable("container_name", $this->container->getContainerName());

        // return self
        return $this;
    }
}
