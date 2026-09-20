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


namespace Nether\network;

require_once __DIR__ . "/../../vendor/flow-php/snappy/polyfill.php";

use LogLevel;
use Nether\network\handlers\ClientNetworkLevelHandler;
use Nether\player\sessions\Connection;
use Nether\player\sessions\ProxiedSession;
use Nether\player\sessions\SessionManager;
use Nether\ProxyServer;
use Override;
use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pmmp\encoding\VarInt;
use pocketmine\nethernet\ServerEventListener;
use pocketmine\nethernet\session\DisconnectReason;
use pocketmine\nethernet\session\Reliability;
use pocketmine\nethernet\session\Session;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\network\mcpe\protocol\RequestNetworkSettingsPacket;
use pocketmine\network\mcpe\protocol\serializer\PacketBatch;

class ServerNetworkEventListener implements ServerEventListener {

    private ProxyServer $server;
    private ClientNetworkLevelHandler $handler;
    private SessionManager $session_manager;

    public function __construct(ProxyServer $server, ClientNetworkLevelHandler $handler, SessionManager $session_manager)
    {
        $this->server = $server;
        $this->handler = $handler;
        $this->session_manager = $session_manager;
    }


    public function onSessionOpen(Session $session): void {
        $this->server->getProxyLogger()->log(LogLevel::DEBUG, "[Netherrack]: Session has been opened for NetworkDevice: " . $session->getRemoteAddress());
        $this->session_manager->createSession(new ProxiedSession(new Connection($session->getRemoteAddress(), $session->getNetworkId()), false, null, null));

    }


    public function onSessionClose(Session $session, DisconnectReason $reason): void {
        $this->server->getProxyLogger()->log(LogLevel::DEBUG, "[Netherrack]: Session has been Destroyed for NetworkDevice: " . $this->session_manager->getSession($session->getNetworkId())->getConnection()->getRemoteAddress());
        $this->server->getPlayerManager()->destroyPlayer($this->server->getPlayerManager()->getPlayerByUUID($this->session_manager->getSession($session->getNetworkId())->getUUID()->toString()));
        $this->session_manager->destroySession($session->getNetworkId());
    }

    public function onPacketReceive(Session $session, string $payload, Reliability $reliability): void {

        $sessionPlayer = $this->session_manager->getSession($session->getNetworkId());
        if($sessionPlayer->getCompression()) {
            $compressed = substr($payload, 1);
            $decompressed = snappy_uncompress($compressed);
            $payload = $decompressed;
        }

        foreach(PacketBatch::decodePackets(new ByteBufferReader($payload), PacketPool::getInstance()) as $packetObject) {
            match(true) {
                $packetObject instanceof RequestNetworkSettingsPacket => $this->handler->handleRequestNetworkSettings($session, $sessionPlayer),
                $packetObject instanceof LoginPacket => $this->handler->handleLoginPacket($session, $packetObject, $sessionPlayer, $this->server)
            };
        }

    }

    public function canAcceptPackets(): bool {
        return true;
    }


}