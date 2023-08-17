<?php

namespace App\Ansible\Playbook\Books;

use App\Ansible\Playbook\Playbook;

class PlaybookServerPing extends Playbook
{
    /**
     * @var string
     */
    protected string $directory = "server.ping";
}
