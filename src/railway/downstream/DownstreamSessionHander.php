<?php


namespace Nether\railway\downstream;

use LogLevel;
use Nether\network\engines\auth\AuthenticationEngine;
use pocketmine\network\mcpe\protocol\ClientToServerHandshakePacket;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\ServerToClientHandshakePacket;

final class DownstreamSessionHander {

    private DownstreamSession $session;
    private DownstreamClient $client;
    private AuthenticationEngine $authEngine;

    public function __construct(DownstreamClient $client, AuthenticationEngine $authEngine)
    {
        $this->session = $client->getClientSession();
        $this->authEngine = $authEngine;
        $this->client = $client;
    }

    public function respondWithLoginPacket() : void {
        $auth = $this->authEngine->getAuthJWT($this->client->player->getPlayerSession()->getUUID());
        $client = $this->authEngine->getClientJWT($this->client->player->getPlayerSession()->getUUID());
        $this->session->compression = true;
        $this->session->createBedrockDataPackets(LoginPacket::create(2193, $auth, $client), true);
        $this->client->pr_server->getProxyLogger()->log(LogLevel::INFO, "[RailwayEngine]: Sent LoginPacket to Downstream");
    }   

    public function respondWithClientHandshake(ServerToClientHandshakePacket $p) : void {
        $this->session->server_jwt = $p->jwt;
        $this->session->createBedrockDataPackets(ClientToServerHandshakePacket::create(), true);
        // TODO: Implement Encryption.
    }


}