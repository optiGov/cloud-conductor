<?php

namespace App\Ansible\Playbook\Books;

use App\Ansible\Playbook\Playbook;

class PlaybookHostCommand extends Playbook
{
    /**
     * @var string
     */
    protected string $directory = "host.command";
}
