<?php


namespace Nether\network\engines\auth;

use LogLevel;
use Nether\logger\ProxyLogger;
use Nether\network\engines\auth\authobjects\JWT;
use Nether\player\sessions\ProxiedSession;
use phpseclib4\Crypt\PublicKeyLoader;
use phpseclib4\Crypt\RSA;
use phpseclib4\Crypt\RSA\PublicKey;
use phpseclib4\Math\BigInteger;
use pocketmine\nethernet\crypto\Base64Url;
use pocketmine\nethernet\crypto\CryptoException;
use pocketmine\nethernet\crypto\EcdsaSignature;
use pocketmine\nethernet\identity\JsonWebToken;
use pocketmine\nethernet\session\Session;
use pocketmine\network\mcpe\protocol\LoginPacket;

final class AuthenticationEngine {

    private const BASIC_TOKEN_AUDIENCE = "api://auth-minecraft-services/multiplayer";
    private ProxyLogger $logger;

    public function __construct(ProxyLogger $logger){
        $this->logger = $logger;
    }

    public function authenticateUser(LoginPacket $lp, Session $session, ProxiedSession $sP) : void {
        $authInfo = json_decode($lp->authInfoJson, true);
        $jwt = new JWT($lp->clientDataJwt);
        $token = new JWT($authInfo["Token"]);
        $jwt_sys = JsonWebToken::parse($authInfo["Token"]);
 
        try {  
            
            $this->verifyJWTSignature($jwt);
            $jwt_sys->checkTimestamps();
            $this->verifyMinecraftSignature($token);
            $this->verifyTokenAudience($token);
            $this->verifyTokenIssuer($token);
            $this->logger->log(LogLevel::DEBUG, "[AuthenticationEngine]: Successfully Finished Authentication for: " . $sP->getConnection()->getRemoteAddress());

        } catch (CryptoException $e) {
            // TODO : SEND SESSION A DisconnectPacket.
            echo $e->getMessage() . PHP_EOL;
        }

    }

    /**
     * @throws CryptoException
     */

    private function verifyJWTSignature(JWT $jwt_token) : void {
        $signing_in = $jwt_token->header . "." . $jwt_token->payload;
        $signature = EcdsaSignature::joseToDer(Base64Url::decode($jwt_token->signature));
        $key = $jwt_token->decryptedHeader["x5u"];

        /** @var PublicKey $public_key */
        $public_key = PublicKeyLoader::load($key)->withHash("sha384"); 
        

        if(!$public_key->verify($signing_in, $signature)) {
            throw new CryptoException("Failed JWT Verification.");
        }
    }


    /**
     * @throws CryptoException
     */

    private function verifyMinecraftSignature(JWT $token) : void {
        $api = json_decode(file_get_contents("https://authorization.franchise.minecraft-services.net/.well-known/openid-configuration"), true);
        $keys = json_decode(file_get_contents($api["jwks_uri"]), true);

        $key_captured = false;
        foreach($keys["keys"] as $key) {
            if($key["kid"] == $token->decryptedHeader["kid"]) {
                $key_captured = true;
                $n = $key["n"];
                $m = $key["e"];

                $public_key = $this->gainPublicKey($n, $m);

                $signing_in = $token->header . "." . $token->payload;
                $signature = Base64Url::decode($token->signature);

                $public_key = $public_key->withHash("sha256")->withPadding(RSA::SIGNATURE_PKCS1);
                if(!$public_key->verify($signing_in, $signature)) {
                    throw new CryptoException("Minecraft Token Signature is either faked or failed the process.");
                }
                
                break;
            }
        }

        if(!$key_captured) { 
            throw new CryptoException("Failed to fetch a similar key.");
        }
    }

    /**
     * @throws CryptoException
     */

    private function verifyTokenAudience(JWT $token) : void {
        if($token->decryptedPayload["aud"] !== self::BASIC_TOKEN_AUDIENCE){
            throw new CryptoException("Failed Audience Verification");
        }
    }

    /**
     * @throws CryptoException
     */

    private function verifyTokenIssuer(JWT $token) : void {
        $api = json_decode(file_get_contents("https://authorization.franchise.minecraft-services.net/.well-known/openid-configuration"), true);
        $value = $api["issuer"];
        if($token->decryptedPayload["iss"] !== $value){
            throw new CryptoException("Failed Issuer Verification");
        }
    }


    private function gainPublicKey(String $modulus, String $exponent) : PublicKey {
        $key = PublicKeyLoader::load([
            "n" => new BigInteger(Base64Url::decode($modulus), 256),
            "e" => new BigInteger(Base64Url::decode($exponent), 256)
        ]);

        return $key;
    }





    


}