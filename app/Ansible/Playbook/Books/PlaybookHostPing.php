<?php

namespace App\Ansible\Playbook\Books;

use App\Ansible\Playbook\Playbook;

class PlaybookHostPing extends Playbook
{
    /**
     * @var string
     */
    protected string $directory = "host.ping";
}
