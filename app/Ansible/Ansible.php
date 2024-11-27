<?php

namespace App\Ansible;

use App\Ansible\Playbook\Playbook;
use App\Ansible\Process\Process;
use App\Ansible\Process\ProcessResult;
use App\Models\AnsibleLog;
use App\Models\Host;
use App\Models\Key;
use App\Models\Server;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JsonException;

class Ansible
{

    /**
     * @var Playbook
     */
    protected Playbook $playbook;

    /**
     * @var Host
     */
    protected Host $host;

    /**
     * @var Key
     */
    protected Key $key;

    /**
     * @var string
     */
    protected string $password;

    /**
     * @var array
     */
    protected array $variables = [];

    /**
     * @var string
     */
    protected string $hostFilePath;

    /**
     * @param string $executable
     * @param string $executablePlaybook
     */
    public function __construct(
        public string $executable = "ansible",
        public string $executablePlaybook = "ansible-playbook",
    )
    {
    }

    /**
     * @param Playbook $playbook
     * @return Ansible
     */
    public function play(Playbook $playbook): static
    {
        $this->playbook = $playbook;
        return $this;
    }

    /**
     * @param Host $host
     * @return Ansible
     */
    public function on(Host $host): static
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function variable(string $name, mixed $value): static
    {
        $this->variables[$name] = $value;
        return $this;
    }

    /**
     * @param Key $key
     * @param string $password
     * @return Ansible
     */
    public function with(Key $key, string &$password): static
    {
        $this->key = $key;
        $this->password = &$password;
        return $this;
    }

    /**
     * @return Ansible
     */
    protected function generateHostFile(): Ansible
    {
        // generate path
        $this->hostFilePath = storage_path("app/tmp/" . Str::uuid());

        // create file
        File::put($this->hostFilePath, "[server]\n" . $this->host->host . " ansible_ssh_private_key_file=" . $this->key->getPath() . " ansible_user=" . $this->key->username);

        // return self
        return $this;
    }

    /**
     * @return $this
     */
    protected function removeHostFile(): Ansible
    {
        // remove file
        File::delete($this->hostFilePath);

        // return self
        return $this;
    }

    /**
     * @return ProcessResult
     * @throws JsonException
     */
    public function execute(): ProcessResult
    {
        // generate host file
        $this->generateHostFile();

        // add to variables
        $this->variable("host", $this->host->host);

        // create process
        $process = new Process();

        // set executable
        $process->executable($this->executablePlaybook);

        // set environment variables
        $process
            ->environmentVariable("ANSIBLE_HOST_KEY_CHECKING", "False")
            ->environmentVariable("ANSIBLE_STDOUT_CALLBACK", "json");

        // call playbook preparation
        $this->playbook->prepare($this, $process);

        // set options
        $process
            ->option("inventory", $this->hostFilePath)
            ->option("extra-vars", escapeshellarg(json_encode($this->variables)));

        // set arguments
        $process->argument($this->playbook->getPath());

        // decrypt key
        $this->key->decryptKey($this->password);

        // execute process
        $result = $process->execute();

        // encrypt key
        $this->key->encryptKey($this->password);

        // remove password
        $this->password = "";
        unset($this->password);

        // call playbook cleanup
        $this->playbook->cleanup($this, $process);

        // remove host file after process is done
        $this->removeHostFile();

        // log the result
        $log = $this->log(
            $this->host,
            $this->key,
            auth()->user(),
            $process->getCommand(),
            $result->asArray()
        );

        // set the log
        $result->setLog($log);

        // return result
        return $result;
    }

    /**
     * @param Host $host
     * @param Key $key
     * @param User|null $user
     * @param string $command
     * @param array $result
     * @return AnsibleLog
     */
    protected function log(Host $host, Key $key, ?User $user, string $command, array $result): AnsibleLog
    {
        // create new log
        $log = new AnsibleLog();

        // associate models
        $log->key()->associate($key);
        $log->user()->associate($user);

        // set values
        $log->host = $host->name;
        $log->command = $command;
        $log->result = $result;
        $log->save();

        // return log
        return $log;
    }

}
