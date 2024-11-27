<?php

namespace App\Ansible\Playbook\Books;

use App\Ansible\Playbook\Playbook;

class PlaybookJumpHostConfigure extends Playbook
{
    /**
     * @var string
     */
    protected string $directory = "jump-host.configure";
}
