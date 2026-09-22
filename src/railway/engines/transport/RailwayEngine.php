<?php


namespace Nether\railway\engines\transport;

use LogLevel;
use Nether\player\ProxiedPlayer;
use Nether\ProxyServer;
use Nether\railway\handlers\ClientSocketHandler;
use Nether\railway\transportObj\DownstreamConnection;
use Nether\railway\transportObj\DownstreamServer;
use RuntimeException;

final class RailwayEngine {

    private ProxyServer $server;
    private static array $downstreams = [];
    private static array $connections = [];


    public function __construct(ProxyServer $server) {
        $this->server = $server;
    }

    /**
     * @throws RuntimeException
     */

    public function loadDownstreams() : void {
        $config_engine = $this->server->getConfig();


        foreach($config_engine->get("downstreams") as $name => $object) {
            if(self::$downstreams !== []) {
                foreach(self::$downstreams as $server) {
                    if($server->getDownstreamName() == $name || $server->getAddress() == $object["server_address"] || $server->getPort() == $object["port"]) {
                        throw new RuntimeException("Can't Have Duplicates of the same downstream.");
                    }
                }
            }   

            self::$downstreams[$name] = new DownstreamServer([
                "name" => $name,
                "server_address" => $object["server_address"],
                "port" => $object["port"]
            ]);
        }

        $this->server->getProxyLogger()->log(LogLevel::INFO, "[Netherrack]: Loaded All Downstreams with a total of: " . count(self::$downstreams));
    }  

    public function getDownstreamServer(String $server_name) : ?DownstreamServer {
        return self::$downstreams[$server_name];
    }

    public function getDownstreamConnection(String $player_name) : ?DownstreamConnection {
        return self::$connections[$player_name];
    }
    
    public function createDownstreamConnection(ProxiedPlayer $player, DownstreamServer $server) : void {
        self::$connections[$player->getPlayerSession()->getUUID()->toString()] = new DownstreamConnection($this->server, $server, $player);
    }
    
    public function clientPacketReciever() : void {
        foreach(self::$connections as $conn) {
            new ClientSocketHandler($this->server, $conn);
        }
    }


}