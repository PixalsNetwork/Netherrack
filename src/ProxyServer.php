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


namespace Nether;

use Logger;
use LogLevel;
use Nether\logger\ProxyLogger;
use Nether\network\handlers\ClientNetworkLevelHandler;
use Nether\network\ServerNetworkEventListener;
use Nether\player\ProxiedPlayerManager;
use Nether\player\sessions\SessionManager;
use Nether\railway\engines\config\ConfigEngine;
use pocketmine\nethernet\identity\SelfSignedIdentityProvider;
use pocketmine\nethernet\identity\ServerIdentity;
use pocketmine\nethernet\NetherNetServer;
use pocketmine\nethernet\ServerConfiguration;
use pocketmine\nethernet\signaling\http\HttpSignaling;
use pocketmine\nethernet\signaling\http\MutableServerStatusProvider;
use pocketmine\nethernet\signaling\http\ServerStatus;


class ProxyServer {

    private NetherNetServer $server;
    private MutableServerStatusProvider $status;

    private Logger $proxyLogger;
    private ConfigEngine $config_engine;
    private ProxiedPlayerManager $proxied_player_manager;

    public const MAIN_DIR = __DIR__ . "/.." ;

    public function __construct(){

        $this->proxyLogger = new ProxyLogger;
        $this->config_engine = new ConfigEngine($this);
        $this->proxied_player_manager = new ProxiedPlayerManager($this);

        $this->config_engine->createProxyConfig();

        if(!is_dir(self::MAIN_DIR . "/server_identity")) {
            @mkdir(self::MAIN_DIR . "/server_identity");
        }

        /**  
         * a ' Do you trust this server ? ' will prompt once, then vanish as this 
         * system preserves the server identity, any deletion to this key will result in the same prompt.
         */
        
        if(file_exists(self::MAIN_DIR . "/server_identity/server.key")) {
            $identity = ServerIdentity::fromPrivateKeyPem(file_get_contents(self::MAIN_DIR . "/server_identity/server.key"));
        } else {
            $identity = ServerIdentity::generate(); 
            file_put_contents(self::MAIN_DIR . "/server_identity/server.key", $identity->exportPrivateKeyPem());
        }
        
        $config = new ServerConfiguration(
            identityProvider: new SelfSignedIdentityProvider($identity)
        );

        $this->status = new MutableServerStatusProvider(new ServerStatus(
            $this->config_engine->get("server_settings.server_motd"),
            2193,
            "1.26.50",
            $this->config_engine->get("server_settings.level_name"),
            0,
            $this->config_engine->get("server_settings.max_players")
        ));

        $this->server = NetherNetServer::create($config, new ServerNetworkEventListener($this, new ClientNetworkLevelHandler, new SessionManager));
        $this->server->addSignaling(
            new HttpSignaling(
                negotiator: $this->server->getNegotiator(),
                bindAddress: $this->config_engine->get("server_settings.binding_address"),
                port: $this->config_engine->get("server_settings.port"),
                statusProvider: $this->status
            )
        );

    }

    public function start() : void {
        $this->server->start();
        $this->proxyLogger->log(LogLevel::INFO, "[Netherrack]: Started Proxy...");
        $this->proxyLogger->log(LogLevel::INFO, "[Netherrack]: NetherNet Signaling Interface Started on : " . $this->config_engine->get("server_settings.binding_address") . ":" . $this->config_engine->get("server_settings.port"));
       
        while($this->server->isRunning()) {
            $this->server->tick();
            $this->updatePlayerCount();
            usleep(50_000);
        }

    }



    /**
     * @return ProxyLogger
     */

    public function getProxyLogger() : ProxyLogger {
        return $this->proxyLogger;
    }

    /**
     * @return ProxiedPlayerManager
     */

    public function getPlayerManager() : ProxiedPlayerManager {
        return $this->proxied_player_manager;
    }

    public function getConfig() : ConfigEngine {
        return $this->config_engine;
    }

    private function updatePlayerCount() : void {
        $this->status->setServerStatus(new ServerStatus(
            $this->config_engine->get("server_settings.server_motd"),
            2193,
            "1.26.50",
            $this->config_engine->get("server_settings.level_name"),
            $this->getPlayerManager()->getPlayerCount(),
            $this->config_engine->get("server_settings.max_players")
        ));
    }



}