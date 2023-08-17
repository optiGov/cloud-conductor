<?php

namespace App\Ansible\Process\Environment;

class EnvironmentVariable
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return EnvironmentVariable
     */
    public function setName(string $name): EnvironmentVariable
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     * @return EnvironmentVariable
     */
    public function setValue(?string $value): EnvironmentVariable
    {
        $this->value = $value;
        return $this;
    }

}
