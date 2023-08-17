<?php

namespace App\Ansible\Process\Parameter;

class ProcessArgument extends Parameter
{

    /**
     * @param string $argument
     */
    public function __construct(
        public string $argument,
    )
    {
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->argument;
    }

}
