<?php


namespace Nether\railway\downstream;

use LogLevel;
use Nether\player\ProxiedPlayer;
use Nether\ProxyServer;
use Nether\railway\engines\transport\RailwayEngine;
use Nether\railway\transportObj\DownstreamServer;
use Override;
use pmmp\webrtc\ConnectionState;
use raklib\client\ClientSocket;
use raklib\protocol\ConnectionRequest;
use raklib\protocol\ConnectionRequestAccepted;
use raklib\protocol\NewIncomingConnection;
use raklib\protocol\OpenConnectionReply1;
use raklib\protocol\OpenConnectionReply2;
use raklib\protocol\OpenConnectionRequest1;
use raklib\protocol\OpenConnectionRequest2;
use raklib\protocol\PacketSerializer;
use raklib\utils\InternetAddress;

class DownstreamClient extends ClientSocket {

    private ProxiedPlayer $player;
    private ProxyServer $pr_server;
    private RailwayEngine $engine;
    private DownstreamServer $server;

    private ConnectionState $state;

    private int $serverID;
    private bool $server_sec;
    private InternetAddress $server_add;
    private int $clientID;
    private InternetAddress $clientAdd;
    private int $mtuSize;


    public function __construct(InternetAddress $connectAddress, ProxiedPlayer $player, ProxyServer $server, RailwayEngine $engine, DownstreamServer $dserver){
        
        $this->player = $player;
        $this->pr_server = $server;
        $this->engine = $engine;
        $this->server = $dserver;
        parent::__construct($connectAddress);
        $this->createConnectionHandshake();
    }

    public function recieve() : void {
        
        while ($this->pr_server->getNetherNetInstance()->isRunning()) {
            $buffer = $this->readPacket();
            if ($buffer === null || $buffer === '') {
                continue;
            }
            $this->onPacketRecieve($buffer);
        }
    }

    private function createConnectionHandshake() : void {
        $serializer = new PacketSerializer();
        // OpenConnReq1
        $r1 = new OpenConnectionRequest1;
        $r1->mtuSize = 1492;
        $r1->protocol = 11;
        $r1->encode($serializer);
        $this->writePacket($serializer->getBuffer());
    }



    private function onPacketRecieve(String $buffer) : void {
        $id = ord($buffer[0]);
        $global_serializer = new PacketSerializer();
        echo "GOT SOME" . PHP_EOL;

        if($id == OpenConnectionReply1::$ID){
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
            echo "GOT OPR1 SENT OPRE2" . PHP_EOL;
        }

        if($id == OpenConnectionReply2::$ID) {

            $serializer = new PacketSerializer($buffer);
            $re2 = new OpenConnectionReply2;
            $re2->decode($serializer);
            $this->clientAdd = $re2->clientAddress;

            $cr_1 = new ConnectionRequest;
            $cr_1->clientID = $this->clientID;
            $cr_1->sendPingTime = $this->getRakNetTime();
            $cr_1->useSecurity = $this->server_sec;
            $cr_1->encode($global_serializer);
            $this->writePacket($global_serializer->getBuffer());
            echo "GOT OPR2 SENT CR" . PHP_EOL;

        }
        // TODO: Connected Session - Send ACK and recieve Datagrams.
        echo $id . PHP_EOL;
        echo $buffer . PHP_EOL;
        if($id == ConnectionRequestAccepted::$ID) {

            $serializer = new PacketSerializer($buffer);
            $cr_re = new ConnectionRequestAccepted;
            $cr_re->decode($serializer);


            $new_in = new NewIncomingConnection;
            $new_in->address = $cr_re->address;
            $new_in->systemAddresses = $cr_re->systemAddresses;
            $new_in->sendPingTime = $cr_re->sendPongTime;
            $new_in->sendPongTime = $this->getRakNetTime();

            $new_in->encode($global_serializer);
            $this->writePacket($global_serializer->getBuffer());
            $this->state = ConnectionState::CONNECTED;
            $this->pr_server->getProxyLogger()->log(LogLevel::INFO, "Finished First Handshake With a Downstream");
        }
    }

    private function getRakNetTime(): int {
        return (int) (hrtime(true) / 1_000_000);
    }



}