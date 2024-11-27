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

class PlaybookLocalIPAddressesApply extends Playbook
{
    /**
     * @var string
     */
    protected string $directory = "local-ip-addresses.apply";

    protected string $netplanFilePath;

    /**
     * @param Collection<IPSecTunnel> $localIpAddresses
     */
    public function __construct(
        protected Collection $localIpAddresses
    )
    {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    public function prepare(Ansible $ansible, Process $process): static
    {
        // build netplan
        $this->buildNetplan($ansible, $process);

        // set variables
        $ansible->variable("file_netplan", $this->netplanFilePath);

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
    private function buildNetplan(Ansible $ansible, Process $process)
    {
        $this->netplanFilePath = $this->newTemporyFile();

        // build netplan
        $content = <<<EOF
# this file describes the network interfaces available on your system

# auto generated netplan by cloud conductor

network:
  ethernets:
    lo:
      addresses:

EOF;

        // add tunnels
        foreach ($this->localIpAddresses as $address) {
            $content .= "      - {$address->ip_address}/32\n";
        }

        // write file
        File::put($this->netplanFilePath, $content);
    }


    /**
     * @return void
     */
    private function deleteFiles(): void
    {
        File::delete($this->netplanFilePath);
    }
}
