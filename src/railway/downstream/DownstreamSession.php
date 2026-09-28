<?php


namespace Nether\railway\downstream;

use Logger;
use LogLevel;
use Override;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\RequestNetworkSettingsPacket;
use pocketmine\network\mcpe\protocol\serializer\PacketBatch;
use pmmp\encoding\ByteBufferReader;
use raklib\generic\Session;
use raklib\protocol\ConnectedPing;
use raklib\protocol\ConnectedPong;
use raklib\protocol\ConnectionRequest;
use raklib\protocol\ConnectionRequestAccepted;
use raklib\protocol\Datagram;
use raklib\protocol\EncapsulatedPacket;
use raklib\protocol\NewIncomingConnection;
use raklib\protocol\Packet;
use raklib\protocol\PacketReliability;
use raklib\protocol\PacketSerializer;
use raklib\utils\InternetAddress;
use pmmp\encoding\ByteBufferWriter;
use pocketmine\network\mcpe\protocol\NetworkSettingsPacket;
use pocketmine\network\mcpe\protocol\PacketPool;

class DownstreamSession extends Session
{
    protected DownstreamClient $client;
    public bool $pendingNetworkSettings = false;
    public bool $session_is_compressed = false;


    #[Override]
    public function __construct(Logger $logger, InternetAddress $address, int $clientId, int $mtuSize, DownstreamClient $client, int $recvMaxSplitParts = PHP_INT_MAX, int $recvMaxConcurrentSplits = PHP_INT_MAX)
    {
        $this->client = $client;
        return parent::__construct($logger, $address, $clientId, $mtuSize, $recvMaxSplitParts, $recvMaxConcurrentSplits);
    }

    #[Override]
    public function onPacketAck(int $identifierACK): void {}

    #[Override]
    public function onDisconnect(int $reason): void {}

    #[Override]
    public function onPacketReceive(string $packet): void
    {
        $packet = substr($packet, 1);
        foreach(PacketBatch::decodePackets(new ByteBufferReader($packet), PacketPool::getInstance()) as $packetObject) {
            match(true) {
                $packetObject instanceof NetworkSettingsPacket
            }
        }
    }

    #[Override]
    public function onPingMeasure(int $pingMS): void {}

    #[Override]
    public function sendPacket(Packet $packet): void
    {
        // Start online handshake
        if ($packet instanceof ConnectionRequest) {
            // $serializer = new PacketSerializer();
            // $packet->encode($serializer);
            $this->queueConnectedPacket($packet, PacketReliability::RELIABLE, 0, true);
        }
        if ($packet instanceof Datagram) {

            foreach ($packet->packets as $encap) {
                $pid = ord($encap->buffer[0]);
                if ($pid === NewIncomingConnection::$ID && $this->state !== self::STATE_CONNECTED) {
                    $this->state = self::STATE_CONNECTED;
                    $this->pendingNetworkSettings = true;
                }
            }
            $serializer = new PacketSerializer();
            $packet->encode($serializer);
            $buffer = $serializer->getBuffer();

            $this->client->writePacket($buffer);
        }
    }

    #[Override]
    public function handleRakNetConnectionPacket(string $packet): void
    {
        $id = ord($packet[0]);
        $global_serializer = new PacketSerializer();
        if ($id == ConnectionRequestAccepted::$ID) {
            $serializer = new PacketSerializer($packet);
            $cr_re = new ConnectionRequestAccepted;
            $cr_re->decode($serializer);

            $new_in = new NewIncomingConnection;
            $new_in->address = $cr_re->address;
            $new_in->systemAddresses = $cr_re->systemAddresses;
            $new_in->sendPingTime = $cr_re->sendPongTime;
            $new_in->sendPongTime = $this->getRakNetTimeMS();
            $this->queueConnectedPacket($new_in, PacketReliability::RELIABLE_ORDERED, 0, true);
        }

        if ($id == ConnectedPing::$ID) {
            $serializer = new PacketSerializer($packet);
            $cp = new ConnectedPing;
            $cp->decode($serializer);
            $ping = $cp->sendPingTime;

            $this->queueConnectedPacket(ConnectedPong::create($ping, $this->getRakNetTimeMS()), PacketReliability::UNRELIABLE, 0, true);
        }
    }

    public function createBedrockDataPackets(DataPacket $packet): void
    {
        $serializer = new ByteBufferWriter();
        PacketBatch::encodePackets($serializer, [$packet]);
        $buffer = $serializer->getData();
        $this->createEncapsulatedPacket("\xfe" . $buffer);
    }

    private function createEncapsulatedPacket(String $buffer): void
    {
        $encap = new EncapsulatedPacket;
        $encap->reliability = PacketReliability::RELIABLE_ORDERED;
        $encap->orderChannel = 0;
        $encap->buffer = $buffer;
        $this->addEncapsulatedToQueue($encap, true);
    }
}
