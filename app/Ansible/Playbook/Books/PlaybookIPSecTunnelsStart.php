<?php

namespace App\Ansible\Playbook\Books;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Playbook;
use App\Ansible\Process\Process;
use App\Models\DockerImage;
use App\Models\DockerNetwork;
use App\Models\IPSecTunnel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class PlaybookIPSecTunnelsStart extends Playbook
{
    /**
     * @var string
     */
    protected string $directory = "ipsec-tunnels.start";

    /**
     * @param Collection<IPSecTunnel> $tunnels
     */
    public function __construct(
        protected Collection $tunnels
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
        $ansible->variable("tunnels", $this->tunnels->map(fn(IPSecTunnel $tunnel) => $tunnel->getConnectionNames())->flatten()->toArray());

        // call parent method
        return parent::prepare($ansible, $process);
    }
}
