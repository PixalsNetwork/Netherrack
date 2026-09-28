<?php


namespace Nether\railway\downstream;

use LogLevel;
use Nether\network\engines\auth\AuthenticationEngine;
use Nether\player\ProxiedPlayer;
use Nether\ProxyServer;
use Nether\railway\engines\transport\RailwayEngine;
use Nether\railway\transportObj\DownstreamServer;
use Override;
use pmmp\webrtc\ConnectionState;
use pocketmine\network\mcpe\protocol\RequestNetworkSettingsPacket;
use raklib\client\ClientSocket;
use raklib\protocol\ACK;
use raklib\protocol\ConnectionRequest;
use raklib\protocol\ConnectionRequestAccepted;
use raklib\protocol\Datagram;
use raklib\protocol\NACK;
use raklib\protocol\NewIncomingConnection;
use raklib\protocol\OpenConnectionReply1;
use raklib\protocol\OpenConnectionReply2;
use raklib\protocol\OpenConnectionRequest1;
use raklib\protocol\OpenConnectionRequest2;
use raklib\protocol\PacketSerializer;
use raklib\utils\InternetAddress;

class DownstreamClient extends ClientSocket
{

    public ProxiedPlayer $player;
    public ProxyServer $pr_server;
    private RailwayEngine $engine;
    private DownstreamServer $server;
    private ?DownstreamSession $session = null;

    private ConnectionState $state;

    private int $serverID;
    private bool $server_sec;
    private InternetAddress $server_add;
    private int $clientID;
    private InternetAddress $clientAdd;
    private int $mtuSize;


    public function __construct(InternetAddress $connectAddress, ProxiedPlayer $player, ProxyServer $server, RailwayEngine $engine, DownstreamServer $dserver)
    {

        $this->player = $player;
        $this->pr_server = $server;
        $this->engine = $engine;
        $this->server = $dserver;
        parent::__construct($connectAddress);
        $this->createConnectionHandshake();
    }

    public function recieve(): void
    {

        if ($this->session !== null) {
            $this->session->update(microtime(true));
        }

        while ($this->pr_server->getNetherNetInstance()->isRunning()) {
            $buffer = $this->readPacket();
            if ($buffer === null || $buffer === '') {
                continue;
            }
            if ($this->session !== null) {
                $this->handleConnectedPacket($buffer);
            }
            if($this->session !== null && $this->session->pendingNetworkSettings) {
                $this->session->pendingNetworkSettings = false;
                $this->session->createBedrockDataPackets(RequestNetworkSettingsPacket::create(2193), false);

            }
            $this->onPacketRecieve($buffer);
        }
    }

    private function createConnectionHandshake(): void
    {
        $serializer = new PacketSerializer();
        // OpenConnReq1
        $r1 = new OpenConnectionRequest1;
        $r1->mtuSize = 1492;
        $r1->protocol = 11;
        $r1->encode($serializer);
        $this->writePacket($serializer->getBuffer());
    }



    private function onPacketRecieve(String $buffer): void
    {
        $id = ord($buffer[0]);
        $global_serializer = new PacketSerializer();

        if ($id == OpenConnectionReply1::$ID) {
            $serializer = new PacketSerializer($buffer);
            $re1 = new OpenConnectionReply1;
            $re1->decode($serializer);
            $this->mtuSize = $re1->mtuSize;
            $this->serverID = $re1->serverID;
            $this->server_sec = $re1->serverSecurity;

            $r2 = new OpenConnectionRequest2;

            $r2->clientID = rand(0, PHP_INT_MAX);
            $r2->serverAddress = new InternetAddress($this->server->getAddress(), $this->server->getPort(), 4);
            $r2->mtuSize = $this->mtuSize;
            $this->clientID = $r2->clientID;
            $this->server_add = $r2->serverAddress;

            $r2->encode($global_serializer);

            $this->writePacket($global_serializer->getBuffer());
        } elseif ($id == OpenConnectionReply2::$ID) {

            $serializer = new PacketSerializer($buffer);
            $re2 = new OpenConnectionReply2;
            $re2->decode($serializer);
            $this->clientAdd = $re2->clientAddress;
            $this->pr_server->getProxyLogger()->log(LogLevel::INFO, "Finished First Handshake With a Downstream");
            $cr_1 = new ConnectionRequest;
            $cr_1->clientID = $this->clientID;
            $cr_1->sendPingTime = $this->getRakNetTime();
            $cr_1->useSecurity = $this->server_sec;
            $this->session = new DownstreamSession($this->pr_server->getProxyLogger(), new InternetAddress($this->server->getAddress(), $this->server->getPort(), 4), $this->clientID, $this->mtuSize, $this);
            $this->session->sendPacket($cr_1);
        }





        // Finished Offline Handshake, Online Handshake is at DownstreamSession.php
    }

    private function getRakNetTime(): int
    {
        return (int) (hrtime(true) / 1_000_000);
    }

    private function handleConnectedPacket(string $buffer): void
    {

        $id = ord($buffer[0]);
        $ser = new PacketSerializer($buffer);

        if ($id == ACK::$ID) {
            $p = new ACK();
            $p->decode($ser);
        } elseif ($id == NACK::$ID) {
            $p = new NACK();
            $p->decode($ser);
        } elseif ($id == 0x80) {
            $p = new Datagram();
            $p->decode($ser);
        } else {
            throw new \RuntimeException(
                "Unknown connected RakNet packet ID: 0x" . strtoupper(dechex($id))
            );
        }

        $this->session->handlePacket($p);
    }

    public function getClientSession() : DownstreamSession {
        return $this->session;
    }
}
