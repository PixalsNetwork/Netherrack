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

namespace Nether\player;

use LogLevel;
use Nether\player\sessions\ProxiedSession;
use Nether\ProxyServer;

final class ProxiedPlayerManager {

    private static array $players = [];
    private static array $uuids = [];
    private static array $xuids = [];
    private ProxyServer $server;

    public function __construct(ProxyServer $server)
    {
        $this->server = $server;
    }


    public function createPlayerObj(ProxiedPlayer $player) : void {
        if(isset(self::$uuids[$player->getPlayerSession()->getName()]) && isset(self::$players[self::$uuids[$player->getPlayerSession()->getName()]])) {
            $player->disconnect("[Netherrack]: A Player with the same account has already joined.");
        } else {
            self::$players[$player->getPlayerSession()->getUUID()->toString()] = $player;
            self::$uuids[$player->getPlayerSession()->getName()] = $player->getPlayerSession()->getUUID()->toString();
            self::$xuids[$player->getPlayerSession()->getXUID()] = $player->getPlayerSession()->getUUID()->toString();
            $this->server->getProxyLogger()->log(LogLevel::INFO, "[Netherrack]: Created ProxiedPlayer " . $player->getPlayerSession()->getName());
        }
    }

    public function getPlayerByUUID(String $uuid) : ?ProxiedPlayer {
        return self::$players[$uuid];
    }

    public function getPlayerByName(String $n) : ?ProxiedPlayer {
        return self::$players[self::$uuids[$n]];
    }

    public function getPlayerByXUID(String $xuid) : ?ProxiedPlayer {
        return self::$players[self::$xuids[$xuid]];
    }


    public function destroyPlayer(ProxiedPlayer $player) : void {
        unset(self::$players[$player->getPlayerSession()->getUUID()->toString()]);
        unset(self::$uuids[$player->getPlayerSession()->getName()]);
        unset(self::$xuids[$player->getPlayerSession()->getXUID()]);
    }   

    public function getPlayerCount() : int {
        return count(self::$players);
    }




}