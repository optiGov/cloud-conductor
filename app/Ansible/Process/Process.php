<?php

namespace App\Ansible\Process;


use App\Ansible\Process\Environment\EnvironmentVariable;
use App\Ansible\Process\Parameter\ProcessArgument;
use App\Ansible\Process\Parameter\ProcessFlag;
use App\Ansible\Process\Parameter\ProcessOption;
use Illuminate\Support\Collection;
use JsonException;

class Process
{

    /**
     * @var Collection<string> $inputs
     */
    protected Collection $inputs;

    /**
     * @var Collection<ProcessOption|ProcessFlag|ProcessArgument> $parameters
     */
    protected Collection $parameters;

    /**
     * @var Collection<EnvironmentVariable> $environmentVariables
     */
    protected Collection $environmentVariables;

    /**
     * @var string
     */
    protected string $executable;

    public function __construct()
    {
        $this->inputs = new Collection();
        $this->parameters = new Collection();
        $this->environmentVariables = new Collection();
    }

    /**
     * @param string $executable
     * @return Process
     */
    public function executable(string $executable): Process
    {
        $this->executable = $executable;
        return $this;
    }

    /**
     * Adds an input to the command.
     *
     * @param string $value
     * @return $this
     */
    public function input(string $value): static
    {
        $this->inputs->add($value);
        return $this;
    }

    /**
     * Adds an option to the command.
     *
     * @param string $name
     * @param string|null $value
     * @return $this
     */
    public function option(string $name, string|null $value = null): static
    {
        // check if option already exists
        $optionExists = $this->parameters->first(fn($parameter) => $parameter instanceof ProcessOption && $parameter->name === $name);

        // if option already exists, remove it
        if ($optionExists) {
            $this->parameters = $this->parameters->filter(fn($parameter) => !($parameter instanceof ProcessOption && $parameter->name === $name));
        }

        // add the option
        $this->parameters->add(new ProcessOption($name, $value));

        // return the instance
        return $this;
    }

    /**
     * Adds a flag to the command.
     *
     * @param string $name
     * @return $this
     */
    public function flag(string $name): static
    {
        $this->parameters->add(new ProcessFlag($name));
        return $this;
    }

    /**
     * Adds an argument to the command.
     *
     * @param string $argument
     * @return $this
     */
    public function argument(string $argument): static
    {
        $this->parameters->add(new ProcessArgument($argument));
        return $this;
    }

    /**
     * Adds an environment variable to the command.
     *
     * @param string $name
     * @param string|null $value
     * @return $this
     */
    public function environmentVariable(string $name, string|null $value = null): static
    {
        $this->environmentVariables->add(new EnvironmentVariable($name, $value));
        return $this;
    }

    /**
     * Builds the command to execute.
     *
     * @return string
     */
    public function getCommand(): string
    {
        // build the command
        $command = new Collection();

        // add the executable
        $command->add($this->executable);

        // add the parameters
        $this->parameters->each(fn($parameter) => $command->add($parameter->asString()));

        // build the command string
        return $command->join(" ");
    }

    /**
     * Executes the command.
     *
     * @return ProcessResult
     * @throws JsonException
     */
    public function execute(): ProcessResult
    {
        // build command
        $command = $this->getCommand();

        $environmentString = "";
        $this->environmentVariables->each(function (EnvironmentVariable $var) use (&$environmentString) {
            $environmentString .= "{$var->getName()}={$var->getValue()} ";
        });

        // execute the command
        exec("$environmentString $command", $output, $exitStatus);

        // format output as string
        $output = collect($output)->implode("\n");

        // build output buffer
        $result = new ProcessResult();
        $result->setStatusCode($exitStatus);
        $result->setBuffer($output);

        // return the result
        return $result;
    }
}
