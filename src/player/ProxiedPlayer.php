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

use Nether\network\handlers\ClientNetworkLevelHandler;
use Nether\player\sessions\Connection;
use Nether\player\sessions\ProxiedSession;
use Nether\ProxyServer;
use Ramsey\Uuid\Uuid;
use Override;
use pocketmine\nethernet\session\Session;
use pocketmine\network\mcpe\protocol\DisconnectPacket;
use pocketmine\network\mcpe\protocol\types\DisconnectReason;

class ProxiedPlayer {

    private ProxyServer $server;
    private ProxiedSession $session;
    private Session $network_session;

    public function __construct(ProxyServer $server, ProxiedSession $sP, Session $network_session) {
        $this->server = $server;
        $this->session = $sP;
        $this->network_session = $network_session;
    }


    public function getPlayerSession() : ProxiedSession {
        return $this->session;
    }

    public function disconnect(string $reason = "[Netherrack] Disconnected from server.") {
        $clientHandler = new ClientNetworkLevelHandler;
        $clientHandler->sendPackets($this->network_session, $this->session, DisconnectPacket::create(41, $reason, null));
    }





    


}