<?php

namespace App\Ansible\Process\Parameter;

class ProcessOption extends Parameter
{

    /**
     * @param string $name
     * @param string|null $value
     */
    public function __construct(
        public string      $name,
        public string|null $value,
    )
    {
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if ($this->value === null) {
            return "--" . $this->name;
        }

        return "--" . $this->name . "=" . $this->value;
    }

}
