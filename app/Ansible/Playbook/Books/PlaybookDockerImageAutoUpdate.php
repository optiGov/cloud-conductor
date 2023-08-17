<?php

namespace App\Ansible\Playbook\Books;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Playbook;
use App\Ansible\Process\Process;
use App\Models\DockerImage;

class PlaybookDockerImageAutoUpdate extends Playbook
{
    /**
     * @var string
     */
    protected string $directory = "docker-image.auto-update";

    /**
     * @param DockerImage $image
     */
    public function __construct(
        protected DockerImage $image
    )
    {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    public function prepare(Ansible $ansible, Process $process): static
    {
        // set image
        $ansible->variable("image", $this->image->image);

        // set registry if set
        if($this->image->registry) {
            $ansible->variable("registry_url", $this->image->registry);
        }

        // set username and password if set
        if($this->image->username && $this->image->password) {
            $ansible->variable("registry_username", $this->image->username);
            $ansible->variable("registry_password", $this->image->password);
        }

        // set daily update
        $ansible->variable("daily_update", $this->image->daily_update);
        $ansible->variable("daily_update_time", $this->image->daily_update_time);

        // call parent method
        return parent::prepare($ansible, $process);
    }
}
