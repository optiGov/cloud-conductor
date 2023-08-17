<?php

namespace App\Ansible\Playbook\Books;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Playbook;
use App\Ansible\Process\Process;
use App\Models\DockerContainer;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PlaybookDockerContainerCreate extends Playbook
{
    /**
     * @var string
     */
    protected string $directory = "docker-container.create";

    /**
     * @var string
     */
    protected string $dockerComposeFilePath;

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
        // get path
        $this->dockerComposeFilePath = $this->newTemporyFile();

        // create docker-compose file
        File::put($this->dockerComposeFilePath, $this->container->getDockerComposeContent());

        // set variables
        $ansible->variable("docker_compose_file", $this->dockerComposeFilePath);
        $ansible->variable("uuid", $this->container->uuid);
        $ansible->variable("daily_update", $this->container->daily_update);
        $ansible->variable("daily_update_time", $this->container->daily_update_time);

        // return self
        return $this;
    }

    /**
     * @param Ansible $ansible
     * @param Process $process
     * @return $this
     */
    public function cleanup(Ansible $ansible, Process $process): static
    {
        // remove docker-compose file
        File::delete($this->dockerComposeFilePath);

        // return self
        return $this;
    }
}
