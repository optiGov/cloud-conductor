<?php

namespace App\Ansible\Process;

use App\Models\AnsibleLog;
use Illuminate\Support\Str;
use JsonException;

class ProcessResult
{
    /**
     * @var int|null
     */
    protected int|null $statusCode = null;
    /**
     * @var string
     */
    protected string $buffer = "";

    /**
     * @var AnsibleLog
     */
    protected AnsibleLog $log;

    /**
     * @param string $buffer
     * @return void
     */
    public function setBuffer(string $buffer): void
    {
        $this->buffer = $buffer;
    }

    /**
     * @param int $statusCode
     * @return void
     */
    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @param AnsibleLog $log
     */
    public function setLog(AnsibleLog $log): void
    {
        $this->log = $log;
    }

    /**
     * @return int|null
     */
    public function getStatusCode(): int|null
    {
        return $this->statusCode;
    }

    /**
     * @return string
     */
    public function getBuffer(): string
    {
        return $this->buffer;
    }

    /**
     * @return AnsibleLog
     */
    public function getLog(): AnsibleLog
    {
        return $this->log;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->statusCode !== 0;
    }

    /**
     * @return bool
     */
    public function hasSuccess(): bool
    {
        return $this->statusCode === 0;
    }

    /**
     * @return bool
     */
    public function isJson(): bool
    {
        return Str::isJson($this->buffer);
    }

    /**
     * @return array
     * @throws JsonException
     */
    public function asArray(): array
    {
        if (!$this->isJson()) {
            throw new JsonException("Buffer is not JSON.");
        }

        return json_decode($this->buffer, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return bool
     * @throws JsonException
     */
    public function noAnsibleErrors(): bool
    {
        // check for error
        if ($this->hasError()) {
            return false;
        }

        // check for ansible error
        $response = $this->asArray();
        $success = true;

        // iterate over all hosts
        foreach($response["stats"] as $key => $value) {
            if($value["unreachable"] > 0 || $value["failures"] > 0) {
                $success = false;
            }
        }

        return $success;
    }

}
