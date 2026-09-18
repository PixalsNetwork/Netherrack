<?php

namespace Nether;

use Logger;
use LogLevel;
use Nether\logger\ProxyLogger;
use Nether\network\handlers\ClientNetworkLevelHandler;
use Nether\network\ServerNetworkEventListener;
use Nether\player\sessions\SessionManager;
use pocketmine\nethernet\identity\SelfSignedIdentityProvider;
use pocketmine\nethernet\identity\ServerIdentity;
use pocketmine\nethernet\NetherNetServer;
use pocketmine\nethernet\ServerConfiguration;
use pocketmine\nethernet\signaling\http\HttpSignaling;
use pocketmine\nethernet\signaling\http\MutableServerStatusProvider;
use pocketmine\nethernet\signaling\http\ServerStatus;
class ProxyServer {

    private NetherNetServer $server;
    private Logger $proxyLogger;
    public const MAIN_DIR = __DIR__ . "/.." ;
    private array $config = [
        "bindAddress" => "0.0.0.0",
        "port" => 19132
    ];

    public function __construct(){

        $this->proxyLogger = new ProxyLogger;

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

        $this->server = NetherNetServer::create($config, new ServerNetworkEventListener($this, new ClientNetworkLevelHandler, new SessionManager));
        $this->server->addSignaling(
            new HttpSignaling(
                negotiator: $this->server->getNegotiator(),
                bindAddress: $this->config["bindAddress"],
                port: $this->config["port"],
                statusProvider: new MutableServerStatusProvider(new ServerStatus(
                    "Netherrack",
                    2193,
                    "1.26.50",
                    "Nether"
                ))
            )
        );

    }

    public function start() : void {
        $config = $this->config;
        $this->server->start();
        $this->proxyLogger->log(LogLevel::INFO, "[Nether]: Started Proxy...");
        $this->proxyLogger->log(LogLevel::INFO, "[Nether]: NetherNet Signaling Interface Started on : " . $config["bindAddress"] . ":" . $config["port"]);
        while($this->server->isRunning()) {
            $this->server->tick();
            usleep(50000);
        }
    }



    /**
     * @return ProxyLogger
     */

    public function getProxyLogger() : ProxyLogger {
        return $this->proxyLogger;
    }



}