<?php

namespace App\Ansible\Process\Parameter;

class ProcessFlag extends Parameter
{

    /**
     * @param string $name
     */
    public function __construct(
        public string $name,
    )
    {
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return "-" . $this->name;
    }

}
