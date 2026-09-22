<?php


namespace Nether\railway\handlers;

use Nether\ProxyServer;
use Nether\railway\transportObj\DownstreamConnection;
use pmmp\encoding\ByteBufferWriter;
use pocketmine\network\mcpe\protocol\serializer\PacketBatch;
use raklib\protocol\PacketSerializer;
use raklib\protocol\UnconnectedPing;

class ClientSocketHandler {

    private ProxyServer $server;
    private ?DownstreamConnection $conn;

    public function __construct(ProxyServer $server, ?DownstreamConnection $conn)
    {
        $this->server = $server;
        $this->conn = $conn;
        $this->handlePackets();
    }


    public function handlePackets() : void {
        if($this->conn == null) {
            return;
        }
        $this->sendConnectionRequest();

        while($this->server->getNetherNetInstance()->isRunning()) {
            $packet = $this->conn->getClientSocket()->readPacket();
            var_dump($packet);
            usleep(50_000);
        }
    }

    public function sendConnectionRequest() : void {
        if($this->conn !== null) {
            $packet = new UnconnectedPing();

            $packet->sendPingTime = (int) (microtime(true) * 1000);

            $packet->clientId = random_int(0, PHP_INT_MAX);

            $serializer = new PacketSerializer();

            $packet->encode($serializer);

            $this->conn->getClientSocket()->writePacket(

                $serializer->getBuffer()

            );
        }
    }


}