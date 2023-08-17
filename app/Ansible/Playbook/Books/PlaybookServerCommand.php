<?php

namespace App\Ansible\Playbook\Books;

use App\Ansible\Playbook\Playbook;

class PlaybookServerCommand extends Playbook
{
    /**
     * @var string
     */
    protected string $directory = "server.command";
}
