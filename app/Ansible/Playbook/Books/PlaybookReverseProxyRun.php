<?php

namespace App\Ansible\Playbook\Books;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Playbook;
use App\Ansible\Process\Process;
use App\Models\Server;

class PlaybookReverseProxyRun extends Playbook
{
    /**
     * @var string
     */
    protected string $directory = "reverse-proxy.run";

    public function __construct(
        protected Server $server
    )
    {
        parent::__construct();
    }

    public function prepare(Ansible $ansible, Process $process): static
    {
        // set acme_default_email
        if ($this->server->reverse_proxy_acme_default_email) {
            $ansible->variable("acme_default_email", $this->server->reverse_proxy_acme_default_email);
        }

        // set acme_ca_provider
        if ($this->server->reverse_proxy_acme_ca_provider) {
            $ansible->variable("acme_ca_uri", match ($this->server->reverse_proxy_acme_ca_provider) {
                "letsencrypt" => "",
                "zero_ssl" => "https://acme.zerossl.com/v2/DV90"
            });
        }

        // set acme_zerossl_api_key
        if ($this->server->reverse_proxy_acme_api_key) {
            $ansible->variable("acme_zerossl_api_key", $this->server->reverse_proxy_acme_api_key);
        }

        // set additional config
        $ansible->variable("nginx_addition_conf", "client_max_body_size 25m;");

        // call parent method
        return parent::prepare($ansible, $process);
    }
}
