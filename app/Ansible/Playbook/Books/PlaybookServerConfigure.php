<?php

namespace App\Ansible\Playbook\Books;

use App\Ansible\Playbook\Playbook;

class PlaybookServerConfigure extends Playbook
{
    /**
     * @var string
     */
    protected string $directory = "server.configure";
}
