<?php

namespace App\Ansible\Playbook\Books;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Playbook;
use App\Ansible\Process\Process;
use App\Models\CronJob;
use App\Models\DockerImage;
use App\Models\DockerNetwork;

class PlaybookCronJobsApply extends Playbook
{
    /**
     * @var string
     */
    protected string $directory = "cron-job.apply";

    /**
     * @param CronJob $cronJob
     */
    public function __construct(
        protected CronJob $cronJob
    )
    {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    public function prepare(Ansible $ansible, Process $process): static
    {
        // set variables
        $ansible->variable("cron_job_identifier", $this->cronJob->identifier);
        $ansible->variable("cron_job_command", $this->cronJob->command);
        $ansible->variable("cron_job_disabled", $this->cronJob->status === "active" ? "false" : "true");
        $ansible->variable("cron_job_minute", $this->cronJob->minute);
        $ansible->variable("cron_job_hour", $this->cronJob->hour);
        $ansible->variable("cron_job_day", $this->cronJob->day);
        $ansible->variable("cron_job_state", $this->cronJob->trashed() ? "absent" : "present");

        // call parent method
        return parent::prepare($ansible, $process);
    }
}
