<?php


namespace Nether\railway\engines\transport;

use Nether\ProxyServer;

final class RailwayEngine {

    private ProxyServer $server;

    public function __construct(ProxyServer $server) {
        $this->server = $server;
    }


    

}