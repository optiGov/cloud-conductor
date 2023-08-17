<?php

namespace App\Ansible\Playbook\Books;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Playbook;
use App\Ansible\Process\Process;
use App\Models\DockerImage;
use App\Models\DockerNetwork;

class PlaybookDockerNetworkApply extends Playbook
{
    /**
     * @var string
     */
    protected string $directory = "docker-network.apply";

    /**
     * @param DockerNetwork $network
     */
    public function __construct(
        protected DockerNetwork $network
    )
    {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    public function prepare(Ansible $ansible, Process $process): static
    {
        // set variables
        $ansible->variable("docker_network_name", $this->network->getNetworkName());
        $ansible->variable("docker_network_subnet", $this->network->subnet);

        // call parent method
        return parent::prepare($ansible, $process);
    }
}
