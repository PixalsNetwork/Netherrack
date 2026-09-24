<?php


/**
 *   _   _      _   _                    _    
 *  | \ | | ___| |_| |__   ___ _ __ _ __ __ _  ___| | __
 *  |  \| |/ _ \ __| '_ \ / _ \ '__| '__/ _` |/ __| |/ /
 *  | |\  |  __/ |_| | | |  __/ |  | | | (_| | (__|   <
 *  |_| \_|\___|\__|_| |_|\___|_|  |_|  \__,_|\___|_|\_\
 *
 *  Netherrack
 *
 *  A Minecraft: Bedrock Edition networking & proxy project
 *
 *  Developed by:
 *  Pixals' Department of Innovation & Technology
 *
 *  © Pixals Technologies
 */

namespace Nether\railway\transportObj;

use Nether\player\ProxiedPlayer;
use Nether\ProxyServer;
use Nether\railway\downstream\DownstreamClient;
use Nether\railway\engines\transport\RailwayEngine;
use raklib\client\ClientSocket;
use raklib\utils\InternetAddress;

class DownstreamConnection {

    private DownstreamServer $server;
    private ProxyServer $proxy_server;
    private ProxiedPlayer $player;
    private DownstreamClient $downstream_session;


    public function __construct(ProxyServer $proxy_server, DownstreamServer $server, ProxiedPlayer $player, RailwayEngine $railwayEngine) {
        $this->server = $server;
        $this->proxy_server = $proxy_server;
        $this->player = $player;

        $this->downstream_session = new DownstreamClient(
            new InternetAddress(
                $server->getAddress(),
                $server->getPort(),
                4
            ),
            $player,
            $proxy_server,
            $railwayEngine, $server
        );
    }

    public function getDownstreamServer() : DownstreamServer {
        return $this->server;
    }


    public function getPlayer() : ProxiedPlayer {
        return $this->player;
    }

    public function getDownstreamClient() : DownstreamClient {
        return $this->downstream_session;
    }



}