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


namespace Nether\network\handlers;

use Nether\logger\ProxyLogger;
use Nether\network\engines\auth\AuthenticationEngine;
use Nether\network\engines\auth\authobjects\JWT;
use Nether\network\types\authentication\AuthToken;
use Nether\network\types\authentication\clientDataJWT;
use Nether\player\sessions\ProxiedSession;
use Nether\ProxyServer;
use pmmp\encoding\ByteBufferWriter;
use pmmp\encoding\BE;
use pmmp\encoding\ByteBufferReader;
use pocketmine\nethernet\session\Session;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\NetworkSettingsPacket;
use pocketmine\network\mcpe\protocol\Packet;
use pocketmine\network\mcpe\protocol\RequestNetworkSettingsPacket;
use pocketmine\network\mcpe\protocol\serializer\PacketBatch;

class ClientNetworkLevelHandler {

    private const COMPRESSION = "\x01";

    public function handleRequestNetworkSettings(Session $session, ProxiedSession $sP) : void {
        $stream = new ByteBufferWriter();
        PacketBatch::encodePackets($stream, [NetworkSettingsPacket::create(NetworkSettingsPacket::COMPRESS_EVERYTHING, 1, false, 4, 0.0)]);
        $batchPayload = $stream->getData();
        $session->send($batchPayload);
        $sP->setCompression(true);
    }

    public function handleLoginPacket(Session $session, LoginPacket $packet, ProxiedSession $sP, ProxyServer $server) : void {


        $authEngine = new AuthenticationEngine($server);
        $authEngine->authenticateUser($packet, $session, $sP);

    }

    public function sendPackets(Session $session, ProxiedSession $sP, Packet $packet) : void {
        $stream = new ByteBufferWriter();
        PacketBatch::encodePackets($stream, [$packet]);
        $batchPayload = $stream->getData();
        
        if($sP->getCompression()) {
            $batchPayload = self::COMPRESSION . snappy_compress($batchPayload);
        }
        $session->send($batchPayload);

    }






}