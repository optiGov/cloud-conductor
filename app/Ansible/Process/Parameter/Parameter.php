<?php

namespace App\Ansible\Process\Parameter;

abstract class Parameter
{

    /**
     * @return string
     */
    abstract public function __toString(): string;

    /**
     * @return string
     */
    public function asString(): string
    {
        return $this->__toString();
    }

}
