<?php

namespace App\Ansible\Playbook\Books;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Playbook;
use App\Ansible\Process\Process;
use App\Models\DockerContainer;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PlaybookIPSecTunnelsPublicKey extends Playbook
{
    /**
     * @var string
     */
    protected string $directory = "ipsec-tunnels.public-key";

    /**
     * @param DockerContainer $container
     */
    public function __construct(
    )
    {
        parent::__construct();
    }
}
