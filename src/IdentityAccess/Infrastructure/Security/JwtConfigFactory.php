<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;

class JwtConfigFactory
{
    public static function create(string $privateKey, string $publicKey, string $passphrase): Configuration
    {
        return Configuration::forAsymmetricSigner(
            signer: new Sha256(),
            signingKey: InMemory::plainText($privateKey, $passphrase),
            verificationKey: InMemory::plainText($publicKey)
        );
    }
}
