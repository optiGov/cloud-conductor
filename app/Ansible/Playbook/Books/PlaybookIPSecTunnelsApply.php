<?php

namespace App\Ansible\Playbook\Books;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Playbook;
use App\Ansible\Process\Process;
use App\Models\DockerImage;
use App\Models\DockerNetwork;
use App\Models\IPSecTunnel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class PlaybookIPSecTunnelsApply extends Playbook
{
    /**
     * @var string
     */
    protected string $directory = "ipsec-tunnels.apply";

    protected string $ipsecConfFilePath;
    protected string $ipsecSecretsFilePath;
    protected string $netplanFilePath;
    protected string $healthCheckScriptFilePath;
    protected string $iptablesPersistentScriptFilePath;

    /**
     * @param Collection<IPSecTunnel> $tunnels
     */
    public function __construct(
        protected Collection $tunnels,
    )
    {
        parent::__construct();
    }

    private function isOnServer(): bool
    {
        return $this->tunnels->first()->server_id !== null;
    }

    /**
     * @inheritDoc
     */
    public function prepare(Ansible $ansible, Process $process): static
    {
        // set variables
        $ansible->variable("interfaces", $this->tunnels->map(fn(IPSecTunnel $tunnel) => $tunnel->getVTIName())->toArray());

        // only set iptables_command if we are on the server, to enable routing into the docker networks
        if($this->isOnServer()){
            $ansible->variable("iptables_command", $this->getIpTablesCommands());
        }

        // build ipsec.conf
        $this->buildIPSecConf($ansible, $process);
        $ansible->variable("file_ipsec_conf", $this->ipsecConfFilePath);

        // build ipsec.secrets
        $this->buildIPSecSecrets($ansible, $process);
        $ansible->variable("file_ipsec_secrets", $this->ipsecSecretsFilePath);

        // build netplan
        $this->buildNetplan($ansible, $process);
        $ansible->variable("file_netplan", $this->netplanFilePath);

        // build health check script
        $this->buildHealthCheckScript($ansible, $process);
        $ansible->variable("file_health_check", $this->healthCheckScriptFilePath);

        // build health check script if we are on the server
        if($this->isOnServer()){
            $this->buildIptablesPersistentScript($ansible, $process);
            $ansible->variable("file_iptables_persistent", $this->iptablesPersistentScriptFilePath);
        }

        // call parent method
        return parent::prepare($ansible, $process);
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Ansible $ansible, Process $process): static
    {
        // delete files
        $this->deleteFiles();

        // call parent method
        return parent::cleanup($ansible, $process);
    }

    /**
     * @param Ansible $ansible
     * @param Process $process
     * @return void
     */
    private function buildIPSecConf(Ansible $ansible, Process $process)
    {
        $this->ipsecConfFilePath = $this->newTemporyFile();

        // build ipsec.conf
        $content = <<<EOF
# ipsec.conf - strongSwan IPsec configuration file

# basic configuration

config setup
        charondebug = "ike 2, net 1, knl 2, mgr 2, cfg 0, chd 2"

# auto generated config by cloud conductor


EOF;

        // add tunnels
        foreach ($this->tunnels as $tunnel) {
            $content .= $tunnel->getIPSecConfigSection() . "\n";
        }

        // write file
        File::put($this->ipsecConfFilePath, $content);
    }

    /**
     * @param Ansible $ansible
     * @param Process $process
     * @return void
     */
    private function buildIPSecSecrets(Ansible $ansible, Process $process)
    {
        $this->ipsecSecretsFilePath = $this->newTemporyFile();

        // build ipsec.secrets
        $content = <<<EOF
# ipsec.secrets - strongSwan IPsec secrets file

# This file holds shared secrets or RSA private keys for authentication.

# RSA private key for this host, authenticating it to any other host
# which knows the public part.

# auto generated secrets by cloud conductor


EOF;

        // add tunnels
        foreach ($this->tunnels as $tunnel) {
            $content .= $tunnel->getIPSecSecretsSection() . "\n";
        }

        // write file
        File::put($this->ipsecSecretsFilePath, $content);
    }

    /**
     * @param Ansible $ansible
     * @param Process $process
     * @return void
     */
    private function buildNetplan(Ansible $ansible, Process $process)
    {
        $this->netplanFilePath = $this->newTemporyFile();

        // build netplan
        $content = <<<EOF
# this file describes the network interfaces available on your system

# auto generated netplan by cloud conductor

network:
  version: 2
  tunnels:

EOF;

        // add tunnels
        foreach ($this->tunnels as $tunnel) {
            $content .= $tunnel->getNetplanConfigSection() . "\n";
        }

        // write file
        File::put($this->netplanFilePath, $content);

    }

    /**
     * @param Ansible $ansible
     * @param Process $process
     * @return void
     */
    private function buildHealthCheckScript(Ansible $ansible, Process $process)
    {
        $this->healthCheckScriptFilePath = $this->newTemporyFile();

        // build script
        $content = "#!/bin/bash\n";

        // add tunnels
        foreach ($this->tunnels as $tunnel) {
            // skip if health check is disabled
            if (!$tunnel->health_check_enabled) {
                continue;
            }

            $commands = '';

            $tunnel->getConnectionNames()->each(function ($connectionName) use (&$commands) {
                $commands .= <<<EOF
    ipsec down {$connectionName}

EOF;
            });

            $tunnel->getConnectionNames()->each(function ($connectionName) use (&$commands) {
                $commands .= <<<EOF
    ipsec up {$connectionName}

EOF;
            });

            $content .= <<<EOF
# tunnel: {$tunnel->name}
if ! ({$tunnel->health_check_command}); then
{$commands}
fi
EOF;

            // append newline
            $content .= "\n";

        }

        // write file
        File::put($this->healthCheckScriptFilePath, $content);

    }

    /**
     * @param Ansible $ansible
     * @param Process $process
     * @return void
     */
    private function buildIptablesPersistentScript(Ansible $ansible, Process $process)
    {
        $this->iptablesPersistentScriptFilePath = $this->newTemporyFile();

        // build script
        $content = <<<EOF
#!/bin/bash

# wait for network to be ready
sleep 30

# run iptables commands

EOF;;

        // add commands
        $commands = $this->getIpTablesCommands(false);

        foreach ($commands as $command) {
            $content .= $command . "\n";
        }

        // write file
        File::put($this->iptablesPersistentScriptFilePath, $content);
    }

    /**
     * @return void
     */
    private function deleteFiles(): void
    {
        File::delete($this->ipsecConfFilePath);
        File::delete($this->ipsecSecretsFilePath);
        File::delete($this->netplanFilePath);
        File::delete($this->healthCheckScriptFilePath);

        if($this->isOnServer()){
            File::delete($this->iptablesPersistentScriptFilePath);
        }
    }

    /**
     * @param bool $includeDroppingChains
     * @return array
     */
    protected function getIpTablesCommands(bool $includeDroppingChains = true): array
    {
        $commands = new Collection();

        if ($includeDroppingChains) {
            // remove POSTROUTING-CLOUD-CONDUCTOR chain
            $commands->add("iptables -t nat -D POSTROUTING -j POSTROUTING-CLOUD-CONDUCTOR");
            $commands->add("iptables -t nat -F POSTROUTING-CLOUD-CONDUCTOR");
            $commands->add("iptables -t nat -X POSTROUTING-CLOUD-CONDUCTOR");

            // remove FORWARD-CLOUD-CONDUCTOR chain
            $commands->add("iptables -D FORWARD -j FORWARD-CLOUD-CONDUCTOR");
            $commands->add("iptables -F FORWARD-CLOUD-CONDUCTOR");
            $commands->add("iptables -X FORWARD-CLOUD-CONDUCTOR");
        }

        // create POSTROUTING-CLOUD-CONDUCTOR chain
        $commands->add("iptables -t nat -N POSTROUTING-CLOUD-CONDUCTOR");
        $commands->add("iptables -t nat -I POSTROUTING -j POSTROUTING-CLOUD-CONDUCTOR");

        // create FORWARD-CLOUD-CONDUCTOR chain
        $commands->add("iptables -N FORWARD-CLOUD-CONDUCTOR");
        $commands->add("iptables -I FORWARD -j FORWARD-CLOUD-CONDUCTOR");

        // add return rule to FORWARD chain
        $commands->add("iptables -A FORWARD-CLOUD-CONDUCTOR -m state --state ESTABLISHED,RELATED -j RETURN");

        // add rules to POSTROUTING-CLOUD-CONDUCTOR and FORWARD-CLOUD-CONDUCTOR chain
        foreach ($this->tunnels as $tunnel) {
            $tunnel->getIPTablesCommands()->each(fn(string $command) => $commands->add($command));
        }

        // return string
        return $commands->toArray();
    }
}
