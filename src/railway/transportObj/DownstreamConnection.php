<?php


namespace Nether\railway\transportObj;

use Nether\player\ProxiedPlayer;
use Nether\ProxyServer;
use raklib\client\ClientSocket;
use raklib\utils\InternetAddress;

class DownstreamConnection {

    private DownstreamServer $server;
    private ProxyServer $proxy_server;
    private ProxiedPlayer $player;
    private ClientSocket $socket;


    public function __construct(ProxyServer $proxy_server, DownstreamServer $server, ProxiedPlayer $player) {
        $this->server = $server;
        $this->proxy_server = $proxy_server;
        $this->player = $player;

        $this->socket = new ClientSocket(new InternetAddress($server->getAddress(), $server->getPort(), 4));
    }

    public function getDownstreamServer() : DownstreamServer {
        return $this->server;
    }


    public function getPlayer() : ProxiedPlayer {
        return $this->player;
    }

    public function getClientSocket() : ClientSocket {
        return $this->socket;
    }



}